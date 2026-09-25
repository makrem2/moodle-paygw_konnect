<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Initiates a Konnect payment and redirects the user to the Konnect pay page.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
require_sesskey();

$component   = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid      = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

global $USER, $DB, $CFG;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/payment/gateway/konnect/pay.php'));

// Load the gateway configuration for this payable item.
$config = (object) \core_payment\helper::get_gateway_configuration($component, $paymentarea, $itemid, 'konnect');
if (empty($config->apikey) || empty($config->receiverwalletid)) {
    throw new \moodle_exception('errornoconfig', 'paygw_konnect');
}

// Work out the amount to charge, including any configured surcharge.
$payable   = \core_payment\helper::get_payable($component, $paymentarea, $itemid);
$currency  = $payable->get_currency();
$surcharge = \core_payment\helper::get_gateway_surcharge('konnect');
$amount    = \core_payment\helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
$accountid = $payable->get_account_id();

// Konnect works in millimes: 1 TND = 1000 millimes.
$amountmillimes = (int) round($amount * 1000);

// Build the callback URLs. These must be reachable by Konnect over HTTPS.
$successurl = (new moodle_url('/payment/gateway/konnect/callback.php'))->out(false);
$failurl    = (new moodle_url('/payment/gateway/konnect/callback.php'))->out(false);
$webhookurl = (new moodle_url('/payment/gateway/konnect/webhook.php'))->out(false);

$payload = [
    'receiverWalletId'       => $config->receiverwalletid,
    'token'                  => $currency,
    'amount'                 => $amountmillimes,
    'type'                   => 'immediate',
    'description'            => \core_text::substr($description, 0, 200),
    'acceptedPaymentMethods' => ['wallet', 'bank_card', 'e-DINAR'],
    'lifespan'               => 30,
    'checkoutForm'           => true,
    'addPaymentFeesToAmount' => false,
    'firstName'              => $USER->firstname,
    'lastName'               => $USER->lastname,
    'email'                  => $USER->email,
    'orderId'                => 'moodle-' . $USER->id . '-' . $itemid . '-' . time(),
    'webhook'                => $webhookurl,
    'silentWebhook'          => true,
    'successUrl'             => $successurl,
    'failUrl'                => $failurl,
    'theme'                  => 'light',
];

// Only send a phone number when the user actually has one.
if (!empty($USER->phone1)) {
    $payload['phoneNumber'] = $USER->phone1;
}

$result = \paygw_konnect\konnect_helper::init_payment($config, $payload);

$data = $result['data'];
if ($result['httpcode'] !== 200 || empty($data) || empty($data->payUrl) || empty($data->paymentRef)) {
    debugging('Konnect init-payment failed: HTTP ' . $result['httpcode'] . ' body: ' . $result['raw'], DEBUG_DEVELOPER);
    throw new \moodle_exception('errorinitpayment', 'paygw_konnect');
}

// Store the mapping so the callback/webhook can find this item by payment reference.
$record = new \stdClass();
$record->paymentref   = $data->paymentRef;
$record->component    = $component;
$record->paymentarea  = $paymentarea;
$record->itemid       = $itemid;
$record->userid       = $USER->id;
$record->accountid    = $accountid;
$record->amount       = $amount;
$record->currency     = $currency;
$record->status       = 'pending';
$record->timecreated  = time();
$record->timemodified = time();
$DB->insert_record('paygw_konnect', $record);

// Hand the user over to Konnect to complete the payment.
redirect(new moodle_url($data->payUrl));

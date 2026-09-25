<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * English strings for paygw_konnect.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Konnect';
$string['pluginname_desc'] = 'The Konnect plugin allows you to receive payments via Konnect (konnect.network).';
$string['gatewayname'] = 'Konnect';
$string['gatewaydescription'] = 'Konnect is an authorised payment gateway provider for accepting card and wallet payments in Tunisian Dinar (TND).';

// Gateway configuration fields.
$string['apikey'] = 'API key';
$string['apikey_help'] = 'The x-api-key value from your Konnect dashboard (My profile > API keys). Use the sandbox key with the sandbox environment and the live key with the production environment.';
$string['receiverwalletid'] = 'Receiver wallet ID';
$string['receiverwalletid_help'] = 'The wallet ID that will receive the payments. You can find it in your Konnect dashboard.';
$string['environment'] = 'Environment';
$string['environment_sandbox'] = 'Sandbox (testing)';
$string['environment_production'] = 'Production (live)';

// Messages.
$string['paymentsuccessful'] = 'Your payment was successful. You now have access.';
$string['paymentpending'] = 'Your payment is being processed. Access will be granted as soon as Konnect confirms the payment.';
$string['paymentcancelled'] = 'The payment was cancelled.';
$string['paymentfailed'] = 'The payment could not be completed. Please try again.';
$string['paymentnotverified'] = 'The payment could not be verified with Konnect. If you were charged, please contact the site administrator.';
$string['errornoconfig'] = 'The Konnect payment gateway is not configured correctly. Please contact the site administrator.';
$string['errorinitpayment'] = 'Konnect could not start the payment. Please try again later.';
$string['continuetosite'] = 'Continue';

// Privacy.
$string['privacy:metadata:paygw_konnect'] = 'Stores information about Konnect payment transactions.';
$string['privacy:metadata:paygw_konnect:paymentid'] = 'The identifier of the payment in the Moodle payments table.';
$string['privacy:metadata:paygw_konnect:paymentref'] = 'The Konnect payment reference associated with the transaction.';
$string['privacy:metadata:paygw_konnect:userid'] = 'The identifier of the user who made the payment.';
$string['privacy:metadata:paygw_konnect:status'] = 'The status of the transaction.';
$string['privacy:metadata:paygw_konnect:timecreated'] = 'The time the transaction was created.';
$string['privacy:metadata:konnect'] = 'The following data is sent to Konnect in order to process a payment.';
$string['privacy:metadata:konnect:firstname'] = 'The first name of the user making the payment.';
$string['privacy:metadata:konnect:lastname'] = 'The last name of the user making the payment.';
$string['privacy:metadata:konnect:email'] = 'The email address of the user making the payment.';
$string['privacy:metadata:konnect:amount'] = 'The amount to be paid.';

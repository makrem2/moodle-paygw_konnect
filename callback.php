<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Browser return endpoint. Konnect redirects the user here (successUrl / failUrl)
 * with a payment_ref query parameter. We re-verify the payment server side and,
 * if it is completed, deliver the order and send the user to the success page.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

global $DB, $OUTPUT, $PAGE;

$paymentref = required_param('payment_ref', PARAM_ALPHANUMEXT);

$status = \paygw_konnect\konnect_helper::verify_and_deliver($paymentref);
$record = $DB->get_record('paygw_konnect', ['paymentref' => $paymentref], '*', IGNORE_MISSING);

if ($status === 'completed' && $record) {
    $successurl = \core_payment\helper::get_success_url($record->component, $record->paymentarea, (int) $record->itemid);
    redirect($successurl, get_string('paymentsuccessful', 'paygw_konnect'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Not completed: show an informational page rather than silently failing.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/konnect/callback.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'paygw_konnect'));

if ($status === 'pending') {
    $message = get_string('paymentpending', 'paygw_konnect');
    $type = \core\output\notification::NOTIFY_INFO;
} else if ($status === 'notfound') {
    $message = get_string('paymentnotverified', 'paygw_konnect');
    $type = \core\output\notification::NOTIFY_ERROR;
} else {
    $message = get_string('paymentfailed', 'paygw_konnect');
    $type = \core\output\notification::NOTIFY_ERROR;
}

echo $OUTPUT->header();
echo $OUTPUT->notification($message, $type);
echo $OUTPUT->continue_button(new moodle_url('/'));
echo $OUTPUT->footer();

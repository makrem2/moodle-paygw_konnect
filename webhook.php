<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Server-to-server webhook endpoint. Konnect calls this URL (silentWebhook)
 * with a payment_ref query parameter when the payment status changes. We
 * re-verify with Konnect and deliver the order if it is completed.
 *
 * No session and no login are used here; the request comes from Konnect, and
 * everything is re-verified against the Konnect API before any order is delivered.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);
define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/../../../config.php');

$paymentref = required_param('payment_ref', PARAM_ALPHANUMEXT);

$status = \paygw_konnect\konnect_helper::verify_and_deliver($paymentref);

// Always answer 200 so Konnect marks the notification as delivered.
header('Content-Type: application/json');
echo json_encode(['received' => true, 'status' => $status]);

<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Helper for talking to the Konnect API and delivering completed orders.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_konnect;

defined('MOODLE_INTERNAL') || die();

/**
 * Static helper methods for the Konnect gateway.
 */
class konnect_helper {

    /**
     * Return the Konnect API base URL for the given environment.
     *
     * @param string $environment 'sandbox' or 'production'
     * @return string
     */
    public static function get_api_base(string $environment): string {
        if ($environment === 'production') {
            return 'https://api.konnect.network/api/v2';
        }
        return 'https://api.sandbox.konnect.network/api/v2';
    }

    /**
     * Call the Konnect init-payment endpoint.
     *
     * @param \stdClass $config Gateway configuration (apikey, receiverwalletid, environment).
     * @param array $payload The init-payment request body.
     * @return array [ 'httpcode' => int, 'data' => stdClass|null, 'raw' => string ]
     */
    public static function init_payment(\stdClass $config, array $payload): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'x-api-key: ' . $config->apikey,
        ]);
        $options = [
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_RETURNTRANSFER' => 1,
        ];
        $url = self::get_api_base($config->environment ?? 'sandbox') . '/payments/init-payment';
        $response = $curl->post($url, json_encode($payload), $options);
        $info = $curl->get_info();

        return [
            'httpcode' => isset($info['http_code']) ? (int) $info['http_code'] : 0,
            'data'     => json_decode($response),
            'raw'      => $response,
        ];
    }

    /**
     * Fetch the details of a payment from Konnect.
     *
     * @param \stdClass $config Gateway configuration.
     * @param string $paymentref The Konnect payment reference.
     * @return \stdClass|null The decoded response, or null on failure.
     */
    public static function get_payment_details(\stdClass $config, string $paymentref): ?\stdClass {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setHeader(['x-api-key: ' . $config->apikey]);
        $options = [
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_RETURNTRANSFER' => 1,
        ];
        $url = self::get_api_base($config->environment ?? 'sandbox') . '/payments/' . $paymentref;
        $response = $curl->get($url, [], $options);
        $data = json_decode($response);

        return $data ?: null;
    }

    /**
     * Verify a payment with Konnect and, when it is completed, deliver the order.
     *
     * This method is idempotent and safe to call from both the browser return
     * (callback.php) and the server-to-server notification (webhook.php). A lock
     * ensures the order is only ever delivered once.
     *
     * @param string $paymentref The Konnect payment reference.
     * @return string The resulting status ('completed', 'pending', 'failed', 'notfound', ...).
     */
    public static function verify_and_deliver(string $paymentref): string {
        global $DB;

        $record = $DB->get_record('paygw_konnect', ['paymentref' => $paymentref]);
        if (!$record) {
            return 'notfound';
        }
        if ($record->status === 'completed') {
            return 'completed';
        }

        // Serialise concurrent calls (browser return + webhook) for this reference.
        $lockfactory = \core\lock\lock_config::get_lock_factory('paygw_konnect');
        $lock = $lockfactory->get_lock('paymentref_' . $paymentref, 10);
        if (!$lock) {
            // Could not obtain the lock; another request is handling it. Report current state.
            $fresh = $DB->get_record('paygw_konnect', ['paymentref' => $paymentref]);
            return $fresh ? $fresh->status : 'pending';
        }

        try {
            // Re-read inside the lock in case another request just finished.
            $record = $DB->get_record('paygw_konnect', ['paymentref' => $paymentref]);
            if (!$record) {
                return 'notfound';
            }
            if ($record->status === 'completed') {
                return 'completed';
            }

            $config = (object) \core_payment\helper::get_gateway_configuration(
                $record->component, $record->paymentarea, $record->itemid, 'konnect');

            $details = self::get_payment_details($config, $paymentref);
            $status = '';
            if ($details && isset($details->payment) && isset($details->payment->status)) {
                $status = (string) $details->payment->status;
            }

            if ($status === 'completed') {
                $paymentid = \core_payment\helper::save_payment(
                    (int) $record->accountid,
                    $record->component,
                    $record->paymentarea,
                    (int) $record->itemid,
                    (int) $record->userid,
                    (float) $record->amount,
                    $record->currency,
                    'konnect'
                );

                \core_payment\helper::deliver_order(
                    $record->component,
                    $record->paymentarea,
                    (int) $record->itemid,
                    $paymentid,
                    (int) $record->userid
                );

                $record->status = 'completed';
                $record->paymentid = $paymentid;
                $record->timemodified = time();
                $DB->update_record('paygw_konnect', $record);

                return 'completed';
            }

            // Persist the latest non-complete status for auditing.
            if ($status !== '' && $status !== $record->status) {
                $record->status = $status;
                $record->timemodified = time();
                $DB->update_record('paygw_konnect', $record);
            }

            return $status !== '' ? $status : 'pending';
        } finally {
            $lock->release();
        }
    }
}

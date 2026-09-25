<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Gateway class for paygw_konnect.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_konnect;

/**
 * The gateway class for the Konnect payment gateway.
 */
class gateway extends \core_payment\gateway {

    /**
     * The list of currencies that the payment gateway supports.
     *
     * @return string[] An array of the currency codes in the three-character ISO-4217 format.
     */
    public static function get_supported_currencies(): array {
        // Konnect settles in Tunisian Dinar. TND is also the token sent to the API.
        return ['TND'];
    }

    /**
     * Configuration form for the gateway instance.
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'apikey', get_string('apikey', 'paygw_konnect'));
        $mform->setType('apikey', PARAM_TEXT);
        $mform->addHelpButton('apikey', 'apikey', 'paygw_konnect');

        $mform->addElement('text', 'receiverwalletid', get_string('receiverwalletid', 'paygw_konnect'));
        $mform->setType('receiverwalletid', PARAM_TEXT);
        $mform->addHelpButton('receiverwalletid', 'receiverwalletid', 'paygw_konnect');

        $environments = [
            'sandbox'    => get_string('environment_sandbox', 'paygw_konnect'),
            'production' => get_string('environment_production', 'paygw_konnect'),
        ];
        $mform->addElement('select', 'environment', get_string('environment', 'paygw_konnect'), $environments);
        $mform->setDefault('environment', 'sandbox');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
            \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled && (empty($data->apikey) || empty($data->receiverwalletid))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }
}

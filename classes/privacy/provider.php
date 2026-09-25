<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Privacy provider for paygw_konnect.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_konnect\privacy;

use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider declaring the data stored and transmitted by the plugin.
 */
class provider implements \core_privacy\local\metadata\provider {

    /**
     * Returns metadata about this plugin's data handling.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'paygw_konnect',
            [
                'paymentid'   => 'privacy:metadata:paygw_konnect:paymentid',
                'paymentref'  => 'privacy:metadata:paygw_konnect:paymentref',
                'userid'      => 'privacy:metadata:paygw_konnect:userid',
                'status'      => 'privacy:metadata:paygw_konnect:status',
                'timecreated' => 'privacy:metadata:paygw_konnect:timecreated',
            ],
            'privacy:metadata:paygw_konnect'
        );

        $collection->add_external_location_link(
            'konnect',
            [
                'firstName' => 'privacy:metadata:konnect:firstname',
                'lastName'  => 'privacy:metadata:konnect:lastname',
                'email'     => 'privacy:metadata:konnect:email',
                'amount'    => 'privacy:metadata:konnect:amount',
            ],
            'privacy:metadata:konnect'
        );

        return $collection;
    }
}

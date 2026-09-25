<?php
// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Version details for paygw_konnect.
 *
 * @package    paygw_konnect
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026092500;
$plugin->requires  = 2024100700;          // Moodle 4.5 (safely below 5.2.x).
$plugin->component = 'paygw_konnect';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'v1.0.0';

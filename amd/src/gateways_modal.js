// This file is part of the Konnect payment gateway plugin for Moodle.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Konnect payment process.
 *
 * The core payment modal (core_payment/gateways_modal) imports this module as
 * paygw_konnect/gateways_modal and calls process() when the user clicks the
 * proceed / continue button. For a redirect based gateway like Konnect we send
 * the browser to our server side initiator (pay.php), which creates the Konnect
 * payment and redirects on to the Konnect pay page. The returned promise never
 * resolves because the page is navigating away.
 *
 * @module     paygw_konnect/gateways_modal
 * @copyright  2026 MakremJebali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Start the Konnect payment flow.
 *
 * @param {string} component Name of the component that the itemid belongs to.
 * @param {string} paymentArea The payment area.
 * @param {number} itemId An internal identifier that is used by the component.
 * @param {string} description The description of the payment.
 * @returns {Promise}
 */
export const process = (component, paymentArea, itemId, description) => {
    const url = M.cfg.wwwroot + '/payment/gateway/konnect/pay.php'
        + '?component=' + encodeURIComponent(component)
        + '&paymentarea=' + encodeURIComponent(paymentArea)
        + '&itemid=' + encodeURIComponent(itemId)
        + '&description=' + encodeURIComponent(description)
        + '&sesskey=' + encodeURIComponent(M.cfg.sesskey);

    window.location.href = url;

    // The browser is leaving this page, so keep the modal spinner up.
    return new Promise(() => {
        return;
    });
};

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace smsgateway_customapi;

use core_sms\hook\after_sms_gateway_form_hook;

/**
 * Hook listener for adding Custom API SMS gateway form elements.
 *
 * @package     smsgateway_customapi
 * @copyright   2024 Kewayne Davidson
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {

    /**
     * Adds additional form elements for the Custom API SMS gateway.
     *
     * @param after_sms_gateway_form_hook $hook The form hook object.
     */
    public static function set_form_definition_for_customapi_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_customapi') {
            return;
        }

        $mform = $hook->mform;
        $plugin = $hook->plugin;
        // Removed $gatewayid since not needed without test functionality.

        // API Settings.
        $mform->addElement('header', 'api_settings_header',
            get_string('api_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'api_url',
            get_string('api_url', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('api_url', PARAM_URL);
        $mform->addRule('api_url', null, 'required');
        $mform->addElement('static', 'api_url_desc', '',
            get_string('api_url_desc', 'smsgateway_customapi'));

        $mform->addElement('select', 'request_type',
            get_string('request_type', 'smsgateway_customapi'), [
                'GET' => get_string('request_type_get', 'smsgateway_customapi'),
                'POST' => get_string('request_type_post', 'smsgateway_customapi'),
            ]);
        $mform->addElement('static', 'request_type_desc', '',
            get_string('request_type_desc', 'smsgateway_customapi'));

        // Parameters.
        $mform->addElement('header', 'parameters_settings_header',
            get_string('parameters_settings', 'smsgateway_customapi'));
        $mform->addElement('static', 'placeholders_info',
            get_string('placeholders', 'smsgateway_customapi'),
            get_string('placeholders_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'headers',
            get_string('headers', 'smsgateway_customapi'),
            'wrap="virtual" rows="5" cols="60"');
        $mform->setType('headers', PARAM_TEXT);
        $mform->addElement('static', 'headers_desc', '',
            get_string('headers_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'query_parameters',
            get_string('query_parameters', 'smsgateway_customapi'),
            'wrap="virtual" rows="5" cols="60"');
        $mform->setType('query_parameters', PARAM_TEXT);
        $mform->addElement('static', 'query_parameters_desc', '',
            get_string('query_parameters_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'post_body_parameters',
            get_string('post_body_parameters', 'smsgateway_customapi'),
            'wrap="virtual" rows="5" cols="60"');
        $mform->setType('post_body_parameters', PARAM_TEXT);
        $mform->addElement('static', 'post_body_parameters_desc', '',
            get_string('post_body_parameters_desc', 'smsgateway_customapi'));
        $mform->hideIf('post_body_parameters', 'request_type', 'eq', 'GET');
        $mform->hideIf('post_body_parameters_desc', 'request_type', 'eq', 'GET');

        // Response Handling.
        $mform->addElement('header', 'response_settings_header',
            get_string('response_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'success_condition',
            get_string('success_condition', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('success_condition', PARAM_TEXT);
        $mform->addElement('static', 'success_condition_desc', '',
            get_string('success_condition_desc', 'smsgateway_customapi'));

        // Removed the entire "Test Settings" section and JS.
    }
}

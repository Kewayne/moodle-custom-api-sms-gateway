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
 * Hook listener for Custom API sms gateway.
 *
 * @package    smsgateway_customapi
 * @copyright  2025 Kewayne Davidson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {
    /**
     * Hook listener for the sms gateway setup form.
     *
     * @param after_sms_gateway_form_hook $hook The hook to add to sms gateway setup.
     */
    public static function set_form_definition_for_customapi_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_customapi') {
            return;
        }

        $mform = $hook->mform;
        $plugin = $hook->plugin;
        $gatewayid = $hook->gatewayid;

        // API Settings.
        $mform->addElement('header', 'api_settings_header', get_string('api_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'api_url', get_string('api_url', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('api_url', PARAM_URL);
        $mform->addHelpButton('api_url', 'api_url', 'smsgateway_customapi');
        $mform->addRule('api_url', null, 'required');

        $mform->addElement('select', 'request_type', get_string('request_type', 'smsgateway_customapi'), [
            'GET' => get_string('request_type_get', 'smsgateway_customapi'),
            'POST' => get_string('request_type_post', 'smsgateway_customapi'),
        ]);
        $mform->addHelpButton('request_type', 'request_type', 'smsgateway_customapi');

        // Parameters.
        $mform->addElement('header', 'parameters_settings_header', get_string('parameters_settings', 'smsgateway_customapi'));
        $mform->addElement('static', 'placeholders_info', get_string('placeholders', 'smsgateway_customapi'), get_string('placeholders_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'headers', get_string('headers', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('headers', PARAM_TEXT);
        $mform->addHelpButton('headers', 'headers', 'smsgateway_customapi');

        $mform->addElement('textarea', 'query_parameters', get_string('query_parameters', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('query_parameters', PARAM_TEXT);
        $mform->addHelpButton('query_parameters', 'query_parameters', 'smsgateway_customapi');

        $mform->addElement('textarea', 'post_body_parameters', get_string('post_body_parameters', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('post_body_parameters', PARAM_TEXT);
        $mform->addHelpButton('post_body_parameters', 'post_body_parameters', 'smsgateway_customapi');
        $mform->hideIf('post_body_parameters', 'request_type', 'eq', 'GET');

        // Response Handling.
        $mform->addElement('header', 'response_settings_header', get_string('response_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'success_condition', get_string('success_condition', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('success_condition', PARAM_TEXT);
        $mform->addHelpButton('success_condition', 'success_condition', 'smsgateway_customapi');

        // Test Settings.
        $mform->addElement('header', 'test_settings_header', get_string('test_settings', 'smsgateway_customapi'));
        $mform->addElement('static', 'test_info', '', get_string('test_settings_desc', 'smsgateway_customapi'));
        $mform->addElement('text', 'test_recipient', get_string('test_recipient', 'smsgateway_customapi'), ['size' => 30]);
        $mform->setType('test_recipient', PARAM_TEXT);
        $mform->addElement('text', 'test_message', get_string('test_message', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setDefault('test_message', 'This is a test message from Moodle.');
        $mform->setType('test_message', PARAM_TEXT);

        $mform->addElement('html', '<div id="customapi-test-response-container" class="mt-2"></div>');
        $mform->addElement('button', 'test_button', get_string('test_button', 'smsgateway_customapi'), ['id' => 'customapi-test-button']);

        // Javascript for the test button.
        global $PAGE;
        $testurl = new \moodle_url("/smsgateway/{$plugin}/ajax.php", ['id' => $gatewayid, 'sesskey' => sesskey()]);
        $PAGE->requires->js_init_call('M.smsgateway_customapi.init', [$testurl->out(false)], true);
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace smsgateway_customapi;

use core_sms\hook\after_sms_gateway_form_hook;

class hook_listener {
    public static function set_form_definition_for_customapi_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_customapi') {
            return;
        }

        $mform = $hook->mform;
        $plugin = $hook->plugin;
        $gatewayid = optional_param('id', 0, PARAM_INT);

        // API Settings.
        $mform->addElement('header', 'api_settings_header', get_string('api_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'api_url', get_string('api_url', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('api_url', PARAM_URL);
        $mform->addRule('api_url', null, 'required');
        $mform->addElement('static', 'api_url_desc', '', get_string('api_url_desc', 'smsgateway_customapi'));

        $mform->addElement('select', 'request_type', get_string('request_type', 'smsgateway_customapi'), [
            'GET' => get_string('request_type_get', 'smsgateway_customapi'),
            'POST' => get_string('request_type_post', 'smsgateway_customapi'),
        ]);
        $mform->addElement('static', 'request_type_desc', '', get_string('request_type_desc', 'smsgateway_customapi'));

        // Parameters.
        $mform->addElement('header', 'parameters_settings_header', get_string('parameters_settings', 'smsgateway_customapi'));
        $mform->addElement('static', 'placeholders_info', get_string('placeholders', 'smsgateway_customapi'), get_string('placeholders_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'headers', get_string('headers', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('headers', PARAM_TEXT);
        $mform->addElement('static', 'headers_desc', '', get_string('headers_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'query_parameters', get_string('query_parameters', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('query_parameters', PARAM_TEXT);
        $mform->addElement('static', 'query_parameters_desc', '', get_string('query_parameters_desc', 'smsgateway_customapi'));

        $mform->addElement('textarea', 'post_body_parameters', get_string('post_body_parameters', 'smsgateway_customapi'), 'wrap="virtual" rows="5" cols="60"');
        $mform->setType('post_body_parameters', PARAM_TEXT);
        $mform->addElement('static', 'post_body_parameters_desc', '', get_string('post_body_parameters_desc', 'smsgateway_customapi'));
        $mform->hideIf('post_body_parameters', 'request_type', 'eq', 'GET');
        $mform->hideIf('post_body_parameters_desc', 'request_type', 'eq', 'GET');

        // Response Handling.
        $mform->addElement('header', 'response_settings_header', get_string('response_settings', 'smsgateway_customapi'));
        $mform->addElement('text', 'success_condition', get_string('success_condition', 'smsgateway_customapi'), ['size' => 60]);
        $mform->setType('success_condition', PARAM_TEXT);
        $mform->addElement('static', 'success_condition_desc', '', get_string('success_condition_desc', 'smsgateway_customapi'));

        // Test Settings.
        if ($gatewayid) {
            $mform->addElement('header', 'test_settings_header', get_string('test_settings', 'smsgateway_customapi'));
            $mform->addElement('static', 'test_info', '', get_string('test_settings_desc', 'smsgateway_customapi'));
            $mform->addElement('text', 'test_recipient', get_string('test_recipient', 'smsgateway_customapi'), ['size' => 30]);
            $mform->setType('test_recipient', PARAM_TEXT);
            $mform->addElement('text', 'test_message', get_string('test_message', 'smsgateway_customapi'), ['size' => 60]);
            $mform->setDefault('test_message', 'This is a test message from Moodle.');
            $mform->setType('test_message', PARAM_TEXT);

            $mform->addElement('html', '<div id="customapi-test-response-container" class="mt-2"></div>');
            $mform->addElement('button', 'test_button', get_string('test_button', 'smsgateway_customapi'), ['id' => 'customapi-test-button']);

            // JavaScript for test button functionality.
            global $PAGE;
            $testurl = new \moodle_url("/sms/gateway/customapi/ajax.php", ['sesskey' => sesskey()]);

            $js = "
M.util.js_pending('customapi-test-init');

require(['jquery', 'core/notification', 'core/ajax'], function($, notification, ajax) {
    $('#customapi-test-button').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        button.prop('disabled', true);
        $('#customapi-test-response-container').html('<em>Testing...</em>');

        var config = {
            api_url: $('#id_api_url').val(),
            request_type: $('#id_request_type').val(),
            headers: $('#id_headers').val(),
            query_parameters: $('#id_query_parameters').val(),
            post_body_parameters: $('#id_post_body_parameters').val(),
            success_condition: $('#id_success_condition').val()
        };

        var postData = {
            id: {$gatewayid},
            recipient: $('#id_test_recipient').val(),
            message: $('#id_test_message').val(),
            config: JSON.stringify(config)
        };

        $.ajax({
            url: '{$testurl->out(false)}',
            method: 'POST',
            data: postData,
            dataType: 'json'
        })
        .done(function(response) {
            var container = $('#customapi-test-response-container');
            var output = '<h4>Status Code: ' + $('<div/>').text(response.statuscode).html() + '</h4>';
            output += '<h4>Request Data Sent:</h4><pre>' + $('<div/>').text(JSON.stringify(postData, null, 2)).html() + '</pre>';
            // Check if response.response is an object and stringify it for display
            var apiResponseContent = (typeof response.response === 'object' && response.response !== null) ?
                                     JSON.stringify(response.response, null, 2) :
                                     response.response;
            output += '<h4>API Response:</h4><pre>' + $('<div/>').text(apiResponseContent).html() + '</pre>';


            if (response.success) {
                notification.add('" . get_string('test_success', 'smsgateway_customapi') . "', 'success');
            } else {
                notification.add('" . get_string('test_failed', 'smsgateway_customapi') . "', 'error');
            }
            container.html(output);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            notification.exception(new Error('AJAX request failed: ' + textStatus + ' - ' + errorThrown));
        })
        .always(function() {
            button.prop('disabled', false);
            M.util.js_complete('customapi-test-init');
        });
    });
});
";
            $PAGE->requires->js_init_code($js, true);
        }
    }
}

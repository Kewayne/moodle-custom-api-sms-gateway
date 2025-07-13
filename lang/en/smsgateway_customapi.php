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

/**
 * Strings for component smsgateway_customapi, language 'en'.
 *
 * @package    smsgateway_customapi
 * @copyright  2025 Kewayne Davidson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['api_settings'] = 'API Settings';
$string['api_url'] = 'API URL';
$string['api_url_desc'] = 'The full endpoint URL for the API call.';
$string['headers'] = 'HTTP Headers';
$string['headers_desc'] = 'One header per line in `Key: Value` format (e.g., `Authorization: Bearer your_token`).';
$string['parameters_settings'] = 'Parameters';
$string['placeholders'] = 'Available placeholders';
$string['placeholders_desc'] = 'You can use the following placeholders in your parameter and header values: <ul><li>`{{recipient}}` - The recipient\'s phone number.</li><li>`{{message}}` - The content of the message.</li></ul>';
$string['pluginname'] = 'Custom API Gateway';
$string['post_body_parameters'] = 'Body parameters (for POST only)';
$string['post_body_parameters_desc'] = 'One parameter per line in `key=value` format. These will be sent in the request body.';
$string['privacy:metadata'] = 'The Custom API Gateway plugin does not store any personal data itself, but passes messages to the admin-configured API provider.';
$string['query_parameters'] = 'Query parameters (for GET and POST)';
$string['query_parameters_desc'] = 'One parameter per line in `key=value` format. These will be added to the URL.';
$string['request_type'] = 'Request type';
$string['request_type_desc'] = 'The HTTP method to use for the API request.';
$string['request_type_get'] = 'GET';
$string['request_type_post'] = 'POST';
$string['response_settings'] = 'Response Handling';
$string['success_condition'] = 'Success condition';
$string['success_condition_desc'] = 'A string that must be present in the API response body to consider the message successfully sent. Leave empty to only check for a 2xx HTTP status code.';


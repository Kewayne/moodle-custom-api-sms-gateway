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
 * AJAX handler for testing the Custom API SMS Gateway.
 *
 * @package     smsgateway_customapi
 * @copyright   2024 Kewayne Davidson
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use smsgateway_customapi\gateway;

require_once(__DIR__ . '/../../../config.php');

// AJAX handler for testing the Custom API SMS Gateway.

require_login();
require_sesskey();
require_capability('moodle/site:config', context_system::instance());

header('Content-Type: application/json');

try {
    $gatewayid = required_param('id', PARAM_INT);
    $sesskey = required_param('sesskey', PARAM_ALPHANUM);
    $testrecipient = required_param('recipient', PARAM_TEXT);
    $testmessage = required_param('message', PARAM_TEXT);
    $configdata = required_param('config', PARAM_RAW);

    $config = json_decode($configdata);
    if (is_null($config)) {
        // Handle invalid JSON gracefully.
        header('HTTP/1.1 400 Bad Request');
        echo json_encode([
            'success' => false,
            'statuscode' => 400,
            'response' => 'Invalid configuration data sent.',
        ]);
        exit();
    }

    $manager = \core\di::get(\core_sms\manager::class);
    $gw = $manager->create_gateway_instance(gateway::class, 'customapi', true, $config);

    // Call the test method.
    $result = $gw->test_connection($testrecipient, $testmessage);

    // Decode the inner JSON if it's valid.
    if (isset($result['response']) && is_string($result['response'])) {
        $decoded = json_decode($result['response'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $result['response'] = $decoded;
        }
    }

    echo json_encode($result);
    exit();

} catch (\Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    debugging('Custom API Gateway AJAX Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'statuscode' => 500,
        'response' => 'An unexpected error occurred: ' . $e->getMessage(),
    ]);
    exit();
}

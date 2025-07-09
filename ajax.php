<?php

require_once(__DIR__ . '/../../../config.php');
use smsgateway_customapi\gateway;

try {
    $gatewayid = required_param('id', PARAM_INT);
    $sesskey = required_param('sesskey', PARAM_ALPHANUM);
    $testrecipient = required_param('recipient', PARAM_TEXT);
    $testmessage = required_param('message', PARAM_TEXT);
    $configdata = required_param('config', PARAM_RAW);

    require_sesskey($sesskey);
    require_capability('moodle/site:config', context_system::instance());

    header('Content-Type: application/json');

    $config = json_decode($configdata);
    if (is_null($config)) {
        // Handle invalid JSON gracefully.
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'statuscode' => 400, 'response' => 'Invalid configuration data sent.']);
        exit();
    }

    $manager = \core\di::get(\core_sms\manager::class);
    $gw = $manager->create_gateway_instance(gateway::class, 'customapi', true, $config);

    // Call the new dedicated test method.
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
    error_log("Custom API Gateway AJAX Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'statuscode' => 500,
        'response' => 'An unexpected error occurred: ' . $e->getMessage() . "\n" . $e->getTraceAsString()
    ]);
    exit();
}


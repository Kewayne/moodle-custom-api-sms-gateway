<?php
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');

use smsgateway_customapi\gateway;
use core_sms\message;

$gatewayid = required_param('id', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_ALPHANUM);
$testrecipient = required_param('recipient', PARAM_TEXT);
$testmessage = required_param('message', PARAM_TEXT);
$configdata = required_param('config', PARAM_RAW);

require_sesskey($sesskey);
require_capability('moodle/site:config', context_system::instance());

header('Content-Type: application/json');

$config = json_decode($configdata);
$manager = \core\di::get(\core_sms\manager::class);
$gw = $manager->create_gateway_instance(gateway::class, 'customapi', true, $config);

$message = new message(
    recipientnumber: $testrecipient,
    content: $testmessage
);

$result = $gw->send($message, true);

echo json_encode($result);
exit();

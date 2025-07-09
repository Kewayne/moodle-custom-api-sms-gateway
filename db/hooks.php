<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core_sms\hook\after_sms_gateway_form_hook::class,
        'callback' => \smsgateway_customapi\hook_listener::class . '::set_form_definition_for_customapi_sms_gateway',
    ],
];

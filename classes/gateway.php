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

use core\http_client;
use core_sms\manager;
use core_sms\message;
use core_sms\message_status;
use GuzzleHttp\Exception\GuzzleException;

/**
 * A generic, customizable API gateway for sending SMS.
 *
 * @package    smsgateway_customapi
 * @copyright  2025 Kewayne Davidson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_sms\gateway {

    /**
     * Sends a message using the configured custom API settings.
     * This method's signature MUST match the parent class.
     *
     * @param message $message The message object to send.
     * @return message The updated message object.
     */
    public function send(message $message): message {
        $result = $this->send_request($message->recipientnumber, $message->content);

        return $message->with(
            status: $result['success'] ? message_status::GATEWAY_SENT : message_status::GATEWAY_FAILED,
        );
    }

    /**
     * Performs a test request using the gateway settings.
     * This is called by the AJAX script for the test button.
     *
     * @param string $recipient The recipient number for the test.
     * @param string $content The message content for the test.
     * @return array An array with test results.
     */
    public function test_connection(string $recipient, string $content): array {
        return $this->send_request($recipient, $content);
    }

    /**
     * The core logic for building and sending the API request.
     *
     * @param string $recipientnumber The recipient's phone number.
     * @param string $content The message content.
     * @return array A result array with 'success', 'statuscode', and 'response'.
     */
    private function send_request(string $recipientnumber, string $content): array {
        $apiurl = $this->config->api_url ?? '';
        if (empty($apiurl)) {
            return ['success' => false, 'statuscode' => 0, 'response' => 'API URL is not configured.'];
        }

        $formattedrecipient = manager::format_number(
            phonenumber: $recipientnumber,
            countrycode: $this->config->countrycode ?? null,
        );
        $formattedrecipient = preg_replace('/[^\d]/', '', $formattedrecipient);

        // Replace placeholders.
        $replacements = [
            '{{recipient}}' => $formattedrecipient,
            '{{message}}' => $content,
        ];

        // Prepare options for the HTTP client.
        $options = [
            'connect_timeout' => 5, // 5 second timeout.
            'timeout' => 10,
        ];

        // 1. Headers.
        $options['headers'] = $this->parse_key_value_pairs($this->config->headers ?? '', $replacements, ':');

        // 2. Query Parameters (for both GET and POST).
        $options['query'] = $this->parse_key_value_pairs($this->config->query_parameters ?? '', $replacements);

        // 3. Body Parameters (for POST only).
        if (($this->config->request_type ?? 'GET') === 'POST') {
            $options['form_params'] = $this->parse_key_value_pairs($this->config->post_body_parameters ?? '', $replacements);
        }

        $client = \core\di::get(http_client::class);
        $responsebody = '';
        $statuscode = 0;
        $success = false;

        try {
            if (($this->config->request_type ?? 'GET') === 'POST') {
                $response = $client->post($apiurl, $options);
            } else {
                $response = $client->get($apiurl, $options);
            }

            $responsebody = $response->getBody()->getContents();
            $statuscode = $response->getStatusCode();

            // Check for success.
            $successcondition = trim($this->config->success_condition ?? '');
            if ($statuscode >= 200 && $statuscode < 300) {
                if (empty($successcondition) || str_contains($responsebody, $successcondition)) {
                    $success = true;
                }
            }

            debugging("CustomAPI response status: $statuscode", DEBUG_DEVELOPER);
            debugging("CustomAPI response body: $responsebody", DEBUG_DEVELOPER);

        } catch (GuzzleException $e) {
            $success = false;
            $responsebody = 'GuzzleException: ' . $e->getMessage();
            $statuscode = $e->getCode();
            debugging("CustomAPI exception: " . $e->getMessage(), DEBUG_DEVELOPER);
        }

        return [
            'success' => $success,
            'statuscode' => $statuscode,
            'response' => $responsebody,
        ];
    }

    /**
     * Parses multiline key-value pair strings into an associative array.
     *
     * @param string $text The text to parse.
     * @param array $replacements Placeholders to replace.
     * @param string $separator The separator between key and value.
     * @return array
     */
    private function parse_key_value_pairs(string $text, array $replacements, string $separator = '='): array {
        $params = [];
        $lines = explode("\n", trim($text));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $parts = explode($separator, $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = str_replace(array_keys($replacements), array_values($replacements), trim($parts[1]));
                $params[$key] = $value;
            }
        }
        return $params;
    }

    #[\Override]
    public function get_send_priority(message $message): int {
        return 100; // High priority as it's highly configurable.
    }
}

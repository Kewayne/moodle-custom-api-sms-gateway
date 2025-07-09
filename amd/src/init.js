define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    return {
        init: function(testUrl) {
            $('#customapi-test-button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                button.prop('disabled', true);
                $('#customapi-test-response-container').html('Testing...');

                // Use camelCase in JS, but still fetch values from snake_case form fields.
                var config = {
                    apiUrl: $('#id_api_url').val(),
                    requestType: $('#id_request_type').val(),
                    headers: $('#id_headers').val(),
                    queryParameters: $('#id_query_parameters').val(),
                    postBodyParameters: $('#id_post_body_parameters').val(),
                    successCondition: $('#id_success_condition').val()
                };

                var postData = {
                    recipient: $('#id_test_recipient').val(),
                    message: $('#id_test_message').val(),
                    config: JSON.stringify(config)
                };

                var promise = ajax.post(testUrl, postData);

                promise.done(function(response) {
                    var container = $('#customapi-test-response-container');
                    var output = '<h4>Status Code: ' + $('<div/>').text(response.statuscode).html() + '</h4>';
                    output += '<pre>' + $('<div/>').text(typeof response.response === 'object' 
                        ? JSON.stringify(response.response, null, 2) 
                        : response.response).html() + '</pre>';

                    if (response.success) {
                        notification.add(M.util.get_string('test_success', 'smsgateway_customapi'), 'success');
                    } else {
                        notification.add(M.util.get_string('test_failed', 'smsgateway_customapi'), 'error');
                    }
                    container.html(output);
                }).fail(function(ex) {
                    $('#customapi-test-response-container').html('');
                    notification.exception(ex);
                }).always(function() {
                    button.prop('disabled', false);
                });
            });
        }
    };
});

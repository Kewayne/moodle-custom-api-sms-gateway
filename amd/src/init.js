define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    return {
        init: function(testUrl) {
            $('#customapi-test-button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                button.prop('disabled', true);
                $('#customapi-test-response-container').html('Testing...');

                var config = {
                    api_url: $('#id_api_url').val(),
                    request_type: $('#id_request_type').val(),
                    headers: $('#id_headers').val(),
                    query_parameters: $('#id_query_parameters').val(),
                    post_body_parameters: $('#id_post_body_parameters').val(),
                    success_condition: $('#id_success_condition').val()
                };

                var postData = {
                    recipient: $('#id_test_recipient').val(),
                    message: $('#id_test_message').val(),
                    config: JSON.stringify(config)
                };

                // Use core/ajax to make a POST request to our custom ajax.php script.
                var promise = ajax.post(testUrl, postData);

                promise.done(function(response) {
                    var container = $('#customapi-test-response-container');
                    // Sanitize the output to prevent XSS from the API response.
                    var output = '<h4>Status Code: ' + $('<div/>').text(response.statuscode).html() + '</h4>';
                    output += '<pre>' + $('<div/>').text(response.response).html() + '</pre>';

                    if (response.success) {
                        notification.add(M.util.get_string('test_success', 'smsgateway_customapi'), 'success');
                    } else {
                        notification.add(M.util.get_string('test_failed', 'smsgateway_customapi'), 'error');
                    }
                    container.html(output);
                }).fail(function(ex) {
                    $('#customapi-test-response-container').html(''); // Clear loading indicator
                    notification.exception(ex);
                }).always(function() {
                    button.prop('disabled', false);
                });
            });
        }
    };
});

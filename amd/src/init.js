define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    return {
        init: function(testUrl) {
            $('#customapi-test-button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                button.prop('disabled', true);

                var config = {
                    api_url: $('#id_api_url').val(),
                    request_type: $('#id_request_type').val(),
                    headers: $('#id_headers').val(),
                    query_parameters: $('#id_query_parameters').val(),
                    post_body_parameters: $('#id_post_body_parameters').val(),
                    success_condition: $('#id_success_condition').val()
                };

                var promises = ajax.call([{
                    methodname: 'smsgateway_customapi_test',
                    args: {
                        url: testUrl,
                        recipient: $('#id_test_recipient').val(),
                        message: $('#id_test_message').val(),
                        config: JSON.stringify(config)
                    },
                    done: function(response) {
                        var container = $('#customapi-test-response-container');
                        var result = JSON.parse(response);
                        var output = '<h4>' + result.statuscode + '</h4>';
                        output += '<pre>' + $('<div/>').text(result.response).html() + '</pre>';

                        if (result.success) {
                            notification.add(M.util.get_string('test_success', 'smsgateway_customapi'), 'success');
                        } else {
                            notification.add(M.util.get_string('test_failed', 'smsgateway_customapi'), 'error');
                        }
                        container.html(output);
                    },
                    fail: notification.exception
                }]);

                promises[0].always(function() {
                    button.prop('disabled', false);
                });
            });
        }
    };
});

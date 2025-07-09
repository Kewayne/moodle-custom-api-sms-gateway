{
    "name": "smsgateway_customapi",
    "version": "1.0.0",
    "description": "Custom API Gateway JavaScript modules",
    "author": "Kewayne Davidson",
    "keywords": ["sms", "gateway", "customapi"],
    "license": "GPL-3.0",
    "main": "",
    "dependencies": {
        "jquery": "3.5.1",
        "core/ajax": "*",
        "core/notification": "*"
    },
    "module": {
        "init": {
            "path": "amd/src/init.js",
            "requires": ["jquery", "core/ajax", "core/notification"]
        }
    }
}

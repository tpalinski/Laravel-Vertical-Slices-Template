<?php

return [
    "clients" => [
        "nxsfr" => [
            "name" => "nexus-frontend",
            "redirectUri" => "http://127.0.0.1",
            "confidential" => true,
        ],
        "pnl" => [
            "name" => "panel",
            "redirectUri" => "https://wave.com.pl",
            "confidential" => true,
        ],
    ],
    "tokens" => [
        "access" => [
            "ttl" => 60,
        ],
        "refresh" => [
            "ttl" => 3600,
        ],
    ],
    "encryption" => [
        "privateKeyPath" => env('AUTH_PRIVATE_KEY_PATH', 'oauth-private.key'),
        "key" => env('AUTH_ENCRYPTION_KEY', 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'),
    ]
];

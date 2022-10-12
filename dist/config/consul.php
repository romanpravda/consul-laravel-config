<?php

declare(strict_types=1);

return [
    'enabled' => env('CONSUL_ENABLED', false),
    'address' => env('CONSUL_ADDRESS', 'server.consul.local'),
    'port' => env('CONSUL_PORT', 8500),
    'key-prefix' => env('CONSUL_KEY_PREFIX'),
    'username' => env('CONSUL_USERNAME'),
    'password' => env('CONSUL_PASSWORD'),
];
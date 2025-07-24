<?php

return [
    'configs' => [
        'default' => [
            'entrypoints' => [
                'ssr' => 'frontend/resources/js/ssr.jsx',
                'paths' => [
                    'frontend/resources/css/app.css',
                    'frontend/resources/js/app.jsx',
                ],
                'ignore' => '/\\.(d\\.ts|json)$/',
            ],
            'dev_server' => [
                'enabled' => true,
                'url' => env('VITE_URL', 'http://localhost:5173'),
                'ping_timeout' => 1,
                'ping_interval' => 1,
            ],
            'build_path' => 'build',
        ],
    ],
]; 
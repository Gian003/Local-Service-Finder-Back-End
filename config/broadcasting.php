<?php

return [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('9a9f9541c5b08ea96ad7'),
        'secret' => env('3710268050c4cfe0fdb9'),
        'app_id' => env('2128542'),
        'options' => [
            'cluster' => env('ap1'),
            'useTLS' => true,
        ]
    ]
];

<?php

declare(strict_types=1);

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => true,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => true,
        ],

        // Диск для загрузки подтверждающих документов
        'uploads' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/uploads'),
            'url'        => env('APP_URL').'/uploads',
            'visibility' => 'public',
            'throw'      => true,
        ],

        // Диск для сгенерированных CSV-экспортов (приватный)
        'exports' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/exports'),
            'throw'  => true,
        ],
    ],
];
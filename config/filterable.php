<?php

return [
    'models_path' => env('FILTERABLE_MODELS_PATH', "App\\Models\\"),

    'disk_name' => env('FILTERABLE_DISK', 'local'),

    'get_from_database' => env('FILTERABLE_GET_FROM_DATABASE', true),

    'skip_tables' => env('FILTERABLE_SKIP_TABLES', [
        'jobs',
        'failed_jobs',
        'migrations',
        'users',
        'roles'
    ]),

    'skip_columns' => env('FILTERABLE_SKIP_COLUMNS', ['id']),
];

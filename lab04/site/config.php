<?php

declare(strict_types=1);

return [
    'db' => [
        'path' => getenv('SQLITE_DB_PATH') ?: dirname(__DIR__) . '/db/db.sqlite',
    ],
];

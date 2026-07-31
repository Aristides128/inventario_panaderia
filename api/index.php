<?php

// Forward Vercel serverless requests to Laravel's public/index.php
// Setup writable temporary storage directories for Vercel read-only filesystem

$storageDir = '/tmp/storage';
$directories = [
    '/tmp/views',
    $storageDir . '/app/public',
    $storageDir . '/framework/cache/data',
    $storageDir . '/framework/sessions',
    $storageDir . '/framework/views',
    '/tmp/bootstrap/cache',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

putenv("APP_STORAGE_PATH={$storageDir}");
putenv("VIEW_COMPILED_PATH=/tmp/views");

require __DIR__ . '/../public/index.php';

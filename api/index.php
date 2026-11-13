<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

foreach (['/tmp/storage/framework/views', '/tmp/storage/framework/cache', '/tmp/storage/framework/sessions', '/tmp/storage/logs'] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__.'/../public/index.php';

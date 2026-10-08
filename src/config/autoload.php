<?php

spl_autoload_register(function (string $class): void {
    $directories = [
        __DIR__ . '/../controller/',
        __DIR__ . '/../model/',
        __DIR__ . '/../router/',
        __DIR__ . '/../config/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
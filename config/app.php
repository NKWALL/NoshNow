<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $config = [
        'db' => [
            'host' => getenv('NOSHNOW_DB_HOST') ?: '127.0.0.1',
            'port' => getenv('NOSHNOW_DB_PORT') ?: '3306',
            'name' => getenv('NOSHNOW_DB_NAME') ?: 'noshnow',
            'user' => getenv('NOSHNOW_DB_USER') ?: 'noshnow_app',
            'password' => getenv('NOSHNOW_DB_PASSWORD') ?: '',
            'socket' => getenv('NOSHNOW_DB_SOCKET') ?: '',
        ],
        'google_maps_api_key' => getenv('GOOGLE_MAPS_API_KEY') ?: '',
    ];

    $localConfigPath = __DIR__ . '/local.php';
    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (!is_array($localConfig)) {
            throw new RuntimeException('config/local.php 必須回傳陣列。');
        }
        $config = array_replace_recursive($config, $localConfig);
    }

    return $config;
}

function google_maps_api_key(): string
{
    return (string) (app_config()['google_maps_api_key'] ?? '');
}

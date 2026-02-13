<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | EasyBroker API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con la API de EasyBroker.
    | Documentación: https://api.easybroker.com/v1/docs
    |
    */
    'easybroker' => [
        'api_key' => env('EASYBROKER_API_KEY'),
        'base_url' => env('EASYBROKER_BASE_URL', 'https://api.easybroker.com/v1'),
        'agency_id' => env('EASYBROKER_AGENCY_ID'),
        'rate_limit' => env('EASYBROKER_RATE_LIMIT', 20), // requests per second
        'timeout' => env('EASYBROKER_TIMEOUT', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | MLS AMPI San Miguel de Allende API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con la API del MLS.
    | Documentación: https://ampisanmigueldeallende.com/api/documentation
    |
    */
    'mls' => [
        'api_key' => env('MLS_API_KEY'),
        'base_url' => env('MLS_BASE_URL', 'https://ampisanmigueldeallende.com/api/v1'),
        // Habilita logs detallados (request/response) hacia el MLS API.
        // Útil para depurar la sincronización de relaciones propiedad ↔ agente.
        // Recomendado activar solo temporalmente.
        'http_log' => env('MLS_HTTP_LOG', false),
        // Nivel de log a usar cuando http_log=true (debug|info|notice|warning|error).
        // Por defecto: info, para que sea visible en producción cuando debug está deshabilitado.
        'http_log_level' => env('MLS_HTTP_LOG_LEVEL', 'info'),
        // URL base para construir URLs absolutas de imágenes de agentes cuando el API devuelve rutas relativas
        // Ej: si el API devuelve "/storage/agents/1.jpg" y MLS_IMAGES_BASE_URL="https://ampisanmigueldeallende.com",
        // el sistema construirá "https://ampisanmigueldeallende.com/storage/agents/1.jpg".
        'images_base_url' => env('MLS_IMAGES_BASE_URL'),
        'rate_limit' => env('MLS_RATE_LIMIT', 10), // requests per second
        'timeout' => env('MLS_TIMEOUT', 30), // seconds
        'batch_size' => env('MLS_BATCH_SIZE', 50), // properties per batch
    ],

];

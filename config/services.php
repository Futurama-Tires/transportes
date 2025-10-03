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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
    'key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

    'telegram' => [
    'bot_token'        => env('TELEGRAM_BOT_TOKEN'),
    'default_chat_id'  => env('TELEGRAM_CHAT_ID'),
    'api_url'          => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
    ],

    'db_cli' => [
    'mysqldump' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'mysql'     => env('MYSQL_CLI_PATH', 'mysql'),
],




];

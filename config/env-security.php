<?php
/*
 * This file is part of laravel-env-security.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
return [
    /**
     * This is the default driver we'll use to manage encryption/decryption
     */
    'default' => env('ENV_DRIVER', 'kms'),

    /**
     * Specify the preferred text editor on your system
     */
    'editor' => env('EDITOR', 'vi'),

    /**
     * The directory where should we keep the encrypted .env files
     */
    'store' => base_path(env('ENV_STORAGE_PATH', 'env')),

    /**
     * This is where we will put the decrypted .env file
     */
    'destination' => base_path(env('ENV_DESTINATION_FILE', '.env')),

    /**
     * Should data be compressed prior to encrypting it?
     */
    'enable_compression' => env('ENV_COMPRESSION', false),

    'drivers' => [
        'kms' => [
            'version' => 'latest',
            'key_id' => env('AWS_KMS_KEY'),
            'region' => env('AWS_KMS_REGION', env('AWS_REGION', 'us-east-1')),
        ],

        'google_kms' => [
            'project' => env('GOOGLE_KMS_PROJECT', env('GOOGLE_CLOUD_PROJECT')),
            'location' => env('GOOGLE_KMS_LOCATION', 'global'),
            'key_ring' => env('GOOGLE_KMS_KEY_RING'),
            'key_id' => env('GOOGLE_KMS_KEY'),
        ],
    ]
];

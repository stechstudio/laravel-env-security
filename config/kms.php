<?php

return [
    'kms_key_id' => env('AWS_KMS_KEY'),
    'kms_key_region' => env('AWS_REGION', 'us-east-1'),
    'file_plaintext' => base_path('.env'),
    'file_ciphertext' => sprintf('%s.env.enc', env('APP_ENV')),
    'dir_ciphertext' => base_path('dotenv'),
];

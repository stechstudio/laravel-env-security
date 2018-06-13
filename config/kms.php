<?php

$ciphertextFilenameTemplate = '%s.env.enc';
$cipherTextFilename = sprintf(env('KMS_NAME_TEMPLATE', $ciphertextFilenameTemplate), env('APP_ENV'));
$ciperTextDirectory = base_path(env('KMS_EDITOR', 'dotenv'));

return [
    'kms_key_id' => env('AWS_KMS_KEY'),
    'kms_key_region' => env('AWS_REGION', 'us-east-1'),
    'file_plaintext' => base_path('.env'),
    'file_ciphertext' => $cipherTextFilename,
    'dir_ciphertext' => $ciperTextDirectory,
    'path_ciphertext' => sprintf('%s/%s', $ciperTextDirectory, $cipherTextFilename),
    'ciphertext_filename_template' => env('KMS_NAME_TEMPLATE', $ciphertextFilenameTemplate),
    'editor' => env('KMS_EDITOR', 'vi'),
];

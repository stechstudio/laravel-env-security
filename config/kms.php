<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

$defaultCiphertextFilenameTemplate = '%s.env.enc';
$cipherTextFilename = sprintf(env('KMS_NAME_TEMPLATE', $defaultCiphertextFilenameTemplate), env('APP_ENV'));
$ciperTextDirectory = base_path(env('KMS_DIRECTORY', 'dotenv'));

return [
    'kms_key_id' => env('AWS_KMS_KEY'),
    'kms_key_region' => env('AWS_KMS_REGION', 'us-east-1'),
    'file_plaintext' => base_path('.env'),
    'file_ciphertext' => $cipherTextFilename,
    'dir_ciphertext' => $ciperTextDirectory,
    'path_ciphertext' => sprintf('%s/%s', $ciperTextDirectory, $cipherTextFilename),
    'ciphertext_filename_template' => env('KMS_NAME_TEMPLATE', $defaultCiphertextFilenameTemplate),
    'editor' => env('KMS_EDITOR', 'vi'),
];

<?php

namespace STS\Kms\DotEnv\Facades;

use Illuminate\Support\Facades\Facade;
use STS\Kms\DotEnv\Crypto\Client;

/**
 * Class Profile.
 *
 *
 * @method static Client factory(string $keyId, array $config = [])
 * @method static Client setKeyId(string $keyId)
 * @method static Client encrypt($plaintext, array $options = [])
 * @method static Client decrypt($cyphertextBlob, array $options = [])
 * @method static Client decryptFile(string $path, array $options = [])
 * @method static string ciphertextBase64()
 * @method static string|resource|StreamInterface blob()
 * @method static Client encryptFile(string $path, array $options = [])
 * @method static Client saveEncryptedFile(string $path)
 * @method static Client saveDecryptedFile(string $path)
 * @method static string plaintext()
 * @method static Client editEncryptedFile(string $editorPath, string $path, array $options = [])
 */
class KMSDotEnv extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sts.kmsdotenv';
    }
}

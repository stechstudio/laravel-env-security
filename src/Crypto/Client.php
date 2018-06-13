<?php

namespace STS\Kms\DotEnv\Crypto;

use Aws\Kms\KmsClient;
use Psr\Http\Message\StreamInterface;

class Client
{
    /**
     * Our AWS KMS Client.
     *
     * @var KmsClient
     */
    protected $kmsClient;

    /**
     * Barebones, default configuration to use.
     *
     * @var array
     */
    protected $defaultConfiguration = [
        'version' => 'latest',
        'region' => 'us-east-1',
    ];

    /**
     * Denotes the type of blob we got back, either string or other.
     *
     * @var string
     */
    protected $type;

    /**
     * Result of the last encryption.
     *
     * @var \Aws\Result
     */
    protected $encryptResult;

    /**
     * Result of the last encryption.
     *
     * @var \Aws\Result
     */
    protected $decryptResult;

    /**
     * The id of the KMS key we will use. This can either be an alias or an URN. For example:.
     *
     *      Key ID: 1234abcd-12ab-34cd-56ef-1234567890ab
     *
     *      Key ARN: arn:aws:kms:us-east-2:111122223333:key/1234abcd-12ab-34cd-56ef-1234567890ab
     *
     *      Alias name: alias/ExampleAlias
     *
     *      Alias ARN: arn:aws:kms:us-east-2:111122223333:alias/ExampleAlias
     *
     * @var string
     */
    private $keyId;

    /**
     * Client constructor.
     *
     * @param array $args client configuration arguments, any valid AWS SDK configurations, the only hard requirement
     *                    is that there must be a specified region
     *
     * @throws \InvalidArgumentException if any required options are missing or
     *                                   the service is not supported
     */
    public function __construct(string $keyId, array $config = [])
    {
        $this->kmsClient = new KmsClient(array_merge($this->defaultConfiguration, $config));
        $this->setKeyId($keyId);
    }

    /**
     * @param array $config
     *
     * @return Client
     */
    public static function factory(string $keyId, array $config = []): Client
    {
        return new static($keyId, $config);
    }

    /**
     * The id of the KMS key we will use.
     * For example:.
     *
     *      Key ID: 1234abcd-12ab-34cd-56ef-1234567890ab
     *
     *      Key ARN: arn:aws:kms:us-east-2:111122223333:key/1234abcd-12ab-34cd-56ef-1234567890ab
     *
     *      Alias name: alias/ExampleAlias
     *
     *      Alias ARN: arn:aws:kms:us-east-2:111122223333:alias/ExampleAlias
     *
     * @param string $keyId
     *
     * @return Client
     */
    public function setKeyId(string $keyId): Client
    {
        $this->keyId = $keyId;

        return $this;
    }

    /**
     * @param string|resource|StreamInterface $plaintext Data to encrypt
     * @param array                           $options   If you would like to override any default options, you may provide them here. Possible
     *                                                   options are:
     *                                                   EncryptionContext - Array of custom strings keys (EncryptionContextKey) to strings
     *                                                   Name-value pair that specifies the encryption context to be used for
     *                                                   authenticated encryption. If used here, the  same value must be
     *                                                   supplied to the Decrypt API or decryption will fail. For more
     *                                                   information, see
     *                                                   http://docs.aws.amazon.com/kms/latest/developerguide/encryption-context.html.
     *                                                   GrantTokens - Type: Array of strings A list of grant tokens. For more information, see
     *                                                   http://docs.aws.amazon.com/kms/latest/developerguide/concepts.html#grant_token in the
     *                                                   AWS Key Management Service Developer Guide.
     *
     * @return Client
     */
    public function encrypt($plaintext, array $options = []): Client
    {
        $this->type = is_string($plaintext) ? 'string' : 'other';

        $this->encryptResult = $this->kmsClient->encrypt(array_merge([
            'KeyId' => $this->keyId,
            'Plaintext' => $plaintext,
        ], $options));

        return $this;
    }

    /**
     * @param string|resource|StreamInterface $cyphertextBlob Blob to decrypt
     * @param array                           $options        If you would like to override any default options, you may provide them here. Possible
     *                                                        options are:
     *                                                        EncryptionContext - Array of custom strings keys (EncryptionContextKey) to strings
     *                                                        Name-value pair that specifies the encryption context to be used for
     *                                                        authenticated encryption. If used here, the  same value must be
     *                                                        supplied to the Decrypt API or decryption will fail. For more
     *                                                        information, see
     *                                                        http://docs.aws.amazon.com/kms/latest/developerguide/encryption-context.html.
     *                                                        GrantTokens - Type: Array of strings A list of grant tokens. For more information, see
     *                                                        http://docs.aws.amazon.com/kms/latest/developerguide/concepts.html#grant_token in the
     *                                                        AWS Key Management Service Developer Guide.
     *
     * @return Client
     */
    public function decrypt($cyphertextBlob, array $options = []): Client
    {
        $this->decryptResult = $this->kmsClient->decrypt(array_merge([
            'KeyId' => $this->keyId,
            'CiphertextBlob' => $cyphertextBlob,
        ], $options));

        return $this;
    }

    public function decryptFile(string $path, array $options = []): Client
    {
        $this->decrypt(base64_decode(file_get_contents($path)), $options);

        return $this;
    }

    /**
     * @return string
     */
    public function ciphertextBase64(): string
    {
        return ('string' == $this->type) ?
            base64_encode($this->encryptResult->get('CiphertextBlob')) : $this->encryptResult->get('CiphertextBlob');
    }

    /**
     * Whatever was in the blob of the last result.
     *
     * @return string|resource|StreamInterface
     */
    public function blob()
    {
        return $this->encryptResult->get('CiphertextBlob');
    }

    public function encryptFile(string $path, array $options = []): Client
    {
        $this->encrypt(file_get_contents($path), $options);

        return $this;
    }

    public function saveEncryptedFile(string $path): Client
    {
        file_put_contents(
            sprintf($path),
            $this->ciphertextBase64()
        );

        return $this;
    }

    public function saveDecryptedFile(string $path): Client
    {
        file_put_contents(
            sprintf($path),
            $this->plaintext()
        );

        return $this;
    }

    public function plaintext(): string
    {
        return $this->decryptResult->get('Plaintext');
    }

    public function editEncryptedFile(string $editorPath, string $path, array $options = []): Client
    {
        $this->decryptFile($path, $options);

        $tmpFile = tmpfile();
        fwrite($tmpFile, $this->plaintext());
        $meta = stream_get_meta_data($tmpFile);

        $process = new \Symfony\Component\Process\Process([$editorPath, $meta['uri']]);
        $process->setTty(true);
        $process->mustRun();

        $this->encryptFile($meta['uri'], $options);
        $this->saveEncryptedFile($path);

        return $this;
    }
}

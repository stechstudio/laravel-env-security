<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Crypto;

use Aws\Kms\KmsClient;
use STS\Kms\DotEnv\Crypto\Concerns\HandlesFiles;
use STS\Kms\DotEnv\Crypto\Concerns\Results;

class Client
{
    use HandlesFiles, Results;

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

    protected $options;

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
        $this->options = array_merge($this->defaultConfiguration, $config);
        $this->kmsClient = new KmsClient($this->options);
        $this->setKeyId($keyId);
    }

    public function setOptions(array $config = [])
    {
        $this->options = array_merge($this->options, $config);
    }

    public function getOptions(array $config = [])
    {
        return array_merge($this->options, $config);
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
     * @param string $plaintext Data to encrypt
     * @param array  $options   If you would like to override any default options, you may provide them here. Possible
     *                          options are:
     *                          EncryptionContext - Array of custom strings keys (EncryptionContextKey) to strings
     *                          Name-value pair that specifies the encryption context to be used for
     *                          authenticated encryption. If used here, the  same value must be
     *                          supplied to the Decrypt API or decryption will fail. For more
     *                          information, see
     *                          http://docs.aws.amazon.com/kms/latest/developerguide/encryption-context.html.
     *                          GrantTokens - Type: Array of strings A list of grant tokens. For more information, see
     *                          http://docs.aws.amazon.com/kms/latest/developerguide/concepts.html#grant_token in the
     *                          AWS Key Management Service Developer Guide.
     *
     * @return Client
     */
    public function encrypt($plaintext, array $options = []): Client
    {
        $this->type = is_string($plaintext) ? 'string' : 'other';

        $this->encryptResult = $this->kmsClient->encrypt(array_merge([
            'KeyId' => $this->keyId,
            'Plaintext' => $plaintext,
        ], $this->getOptions($options)));

        return $this;
    }

    /**
     * @param string $cyphertextBlob Blob to decrypt
     * @param array  $options        If you would like to override any default options, you may provide them here.
     *                               Possible options are:
     *                               EncryptionContext - Array of custom strings keys (EncryptionContextKey) to strings
     *                               Name-value pair that specifies the encryption context to be used for
     *                               authenticated encryption. If used here, the  same value must be
     *                               supplied to the Decrypt API or decryption will fail. For more
     *                               information, see
     *                               http://docs.aws.amazon.com/kms/latest/developerguide/encryption-context.html.
     *                               GrantTokens - Type: Array of strings A list of grant tokens. For more information,
     *                               see http://docs.aws.amazon.com/kms/latest/developerguide/concepts.html#grant_token
     *                               in the AWS Key Management Service Developer Guide.
     *
     * @return Client
     */
    public function decrypt($cyphertextBlob, array $options = []): Client
    {
        $this->decryptResult = $this->kmsClient->decrypt(array_merge([
            'KeyId' => $this->keyId,
            'CiphertextBlob' => $cyphertextBlob,
        ], $this->getOptions($options)));

        return $this;
    }
}

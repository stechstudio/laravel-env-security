<?php

namespace STS\EnvSecurity\Pipeline;

use Illuminate\Contracts\Encryption\Encrypter;
use RuntimeException;
use STS\EnvSecurity\EnvSecurityManager;

class Payload
{
    public const ENCRYPT = 1;
    public const DECRYPT = 2;

    protected bool $isResolved = false;
    protected string $plainTextFilePath;
    protected string $encryptedFilePath;
    public string $content;

    public function __construct(
        protected ?string $environment,
        protected EnvSecurityManager $manager,
        protected int $operation
    ) {
        $this->setOperation($this->operation);
        $this->plainTextFilePath = base_path('.env');
        $this->encryptedFilePath = config('env-security.store')."/{$this->getEnvironment()}.env.enc";
    }

    public function getSourceFilePath(): string
    {
        return match ($this->operation) {
            self::ENCRYPT => $this->plainTextFilePath,
            self::DECRYPT => $this->encryptedFilePath,
        };
    }

    public function getDestinationFilePath(): string
    {
        return match ($this->operation) {
            self::ENCRYPT => $this->encryptedFilePath,
            self::DECRYPT => $this->plainTextFilePath,
        };
    }

    public function setOperation(int $op): static
    {
        if ($op !== 1 && $op !== 2) {
            throw new \InvalidArgumentException('There are only two operation types and $op is not supported.');
        }
        $this->operation = $op;

        return $this;
    }

    public function setPlainTextFilePath(string $path): self
    {
        $this->plainTextFilePath = $path;

        return $this;
    }

    public function getPlainTextFilePath(): string
    {
        return $this->plainTextFilePath;
    }

    public function manager(): EnvSecurityManager
    {
        return $this->manager;
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }

    public function setResolution(bool $resolved): self
    {
        $this->isResolved = $resolved;

        return $this;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function checkZlibExtension(string $message): void
    {
        if (!\in_array('zlib', get_loaded_extensions())) {
            throw new RuntimeException($message);
        }
    }

    public function driver(): Encrypter
    {
        return $this->manager()->driver();
    }
}

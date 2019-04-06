<?php declare(strict_types=1);

namespace AwsSecretsBundle;

use AwsSecretsBundle\Provider\AwsSecretsEnvVarProviderInterface;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Class AwsSecretsEnvVarProcessor
 * @package AwsSecretsBundle
 * @author  Joe Mizzi <joe@casechek.com>
 */
class AwsSecretsEnvVarProcessor implements EnvVarProcessorInterface
{
    public const AWS_SECRET_ID = 'SecretId';
    public const AWS_SECRET_STRING = 'SecretString';

    private $delimiter;
    private $decodedSecrets = [];
    private $ignore;
    private $provider;

    public function __construct(
        AwsSecretsEnvVarProviderInterface $provider,
        bool $ignore = false,
        string $delimiter = ','
    ) {
        $this->ignore = $ignore;
        $this->delimiter = $delimiter;
        $this->provider = $provider;
    }

    /**
     * Returns the value of the given variable as managed by the current instance.
     *
     * @param string $prefix The namespace of the variable
     * @param string $name The name of the variable within the namespace
     * @param \Closure $getEnv A closure that allows fetching more env vars
     *
     * @return mixed
     *
     * @throws RuntimeException on error
     */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        if ($this->ignore === true) {
            return $getEnv($name);
        }

        $value = $getEnv($name);

        $parts = explode($this->delimiter, $value);
        $result = $this->provider->get($parts[0]);

        if (isset($parts[1])) {
            if (!isset($this->decodedSecrets[$parts[0]])) {
                $this->decodedSecrets[$parts[0]] = json_decode($result, true);
            }
            if (isset($this->decodedSecrets[$parts[0]][$parts[1]])) {
                return (string)$this->decodedSecrets[$parts[0]][$parts[1]];
            } else {
                throw new RuntimeException(sprintf("Key '%s' not found in secret '%s'.", $parts[1], $parts[0]));
            }
        }

        return $result;
    }

    public function setIgnore(bool $ignore): void
    {
        $this->ignore = $ignore;
    }

    /**
     * @return string[] The PHP-types managed by getEnv(), keyed by prefixes
     * @codeCoverageIgnore
     */
    public static function getProvidedTypes(): array
    {
        return [
            'aws' => 'bool|int|float|string',
        ];
    }
}

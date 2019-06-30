<?php declare(strict_types=1);

namespace AwsSecretsBundle\DependencyInjection;

use Aws\Credentials\CredentialProvider;
use Aws\SecretsManager\SecretsManagerClient;

/**
 * Class SecretsManagerClientFactory
 * @package AwsSecretsBundle\DependencyInjection
 * @author  Joe Mizzi <joe@casechek.com>
 *
 * @codeCoverageIgnore
 */
class SecretsManagerClientFactory
{
    /**
     * @param array $config
     * @return SecretsManagerClient
     */
    public function createClient(array $credentialsConfig, bool $ecsEnabled): SecretsManagerClient
    {
        if (!$ecsEnabled) {
            unset(
                $credentialsConfig['credentials']['key'],
                $credentialsConfig['credentials']['secret']
            );
        } else {
            $provider = CredentialProvider::ecsCredentials();
            $memoizedProvider = CredentialProvider::memoize($provider);
            $credentialsConfig['credentials'] = $memoizedProvider;
        }

        return new SecretsManagerClient($credentialsConfig);
    }
}
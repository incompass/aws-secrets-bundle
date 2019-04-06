<?php declare(strict_types=1);
/**
 * This file belongs to Bandit. All rights reserved
 */

use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use AwsSecretsBundle\AwsSecretsEnvVarProcessor;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class FeatureContext
 * @author  Joe Mizzi <themizzi@me.com>
 */
class FeatureContext implements Context, KernelAwareContext
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Prophet */
    private $prophet;

    /** @var SecretsManagerClient|ObjectProphecy */
    private $client;

    public function __construct()
    {
        $this->prophet = new Prophet();
        $this->client = $this->prophet->prophesize(SecretsManagerClient::class);
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario @mockSecretsManagerClient
     */
    public function setSecretsManagerClient()
    {
        $this->kernel->getContainer()->set('aws_secrets.client', $this->client->reveal());
    }

    /**
     * @Given /^the secrets manager value for "([^"]*)" is:$/
     */
    public function theSecretsManagerValueForIs($arg1, PyStringNode $string)
    {
        $this->client->getSecretValue([
            AwsSecretsEnvVarProcessor::AWS_SECRET_ID => $arg1
        ])->willReturn(new Result([AwsSecretsEnvVarProcessor::AWS_SECRET_STRING => $string->getRaw()]));
    }

    /**
     * @Given /^the env var "([^"]*)" is set to "([^"]*)"$/
     * @param string $arg1
     * @param string $arg2
     */
    public function theEnvVarIsSetTo(string $arg1, string $arg2)
    {
        putenv(sprintf('%s=%s', $arg1, $arg2));
    }

    /**
     * @Then /^the value of "([^"]*)" will be "([^"]*)"$/
     * @param string $arg1
     * @param string $arg2
     */
    public function theValueOfWillBe(string $arg1, string $arg2)
    {
        if (!$this->kernel->getContainer()->getParameter($arg1) === $arg2) {
            throw new RuntimeException(sprintf('Parameter "%s" does not equal "%s"', $arg1, $arg2));
        }
    }

    /**
     * @Then /^the value of "([^"]*)" will be int$/
     * @throws Exception
     */
    public function theValueOfWillBeInt($arg1)
    {
        $param = $this->kernel->getContainer()->getParameter($arg1);
        if (!gettype($param) === 'int') {
            throw new RuntimeException('Expected integer');
        }
    }

    /**
     * @Then /^the value of "([^"]*)" will be empty$/
     * @throws Exception
     */
    public function theValueOfWillBeEmpty($arg1)
    {
        $param = $this->kernel->getContainer()->getParameter($arg1);
        if (!empty($param)) {
            throw new RuntimeException('Expected empty value');
        }
    }
}
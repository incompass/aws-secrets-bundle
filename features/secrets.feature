Feature: AWS Secrets
  In order to retrieve AWS secrets
  As a symfony developer
  I want to use an aws environment variable processor

  @mockSecretsManagerClient
  Scenario: AWS Parameter
    Given the secrets manager value for "test/secret" is:
    """
    {
      "key": "value"
    }
    """
    And the env var "AWS_SECRET" is set to "test/secret,key"
    Then the value of "aws_secret" will be "value"

  @mockSecretsManagerClient
  Scenario: AWS Parameter with int type
    Given the secrets manager value for "test/secret" is:
    """
    {
      "key": 1
    }
    """
    And the env var "AWS_SECRET" is set to "test/secret,key"
    Then the value of "aws_secret_int" will be int

  @mockSecretsManagerClient
  Scenario: AWS Parameter without subkey
    Given the secrets manager value for "test/secret" is:
    """
    {
      "key": "val"
    }
    """
    And the env var "AWS_SECRET" is set to "test/secret,subkey"
    Then the value of "aws_secret_empty" will be empty
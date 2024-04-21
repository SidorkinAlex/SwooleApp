<?php

use PHPUnit\Framework\TestCase;
use Sidalex\SwooleApp\Application;
use Sidalex\SwooleApp\Classes\Wrapper\ConfigWrapper;

class ApplicationTest extends TestCase
{
    public function test_constructor_initializes_config_and_routesCollection()
    {
        // Arrange
        $configPath = new \stdClass();
        $configValidationList = [];

        // Act
        $application = new Application($configPath, $configValidationList);

        // Assert
        $this->assertInstanceOf(ConfigWrapper::class, $application->getConfig());
        $this->assertIsArray($application->getRoutesCollection());
    }

    public function test_configValidationList_has_one_invalid_ConfigValidatorInterface()
    {
        // Arrange
        $configPath = new \stdClass();
        $configValidationList = ['InvalidValidator'];

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class \'InvalidValidator\' not found');

        new Application($configPath, $configValidationList);
    }

}
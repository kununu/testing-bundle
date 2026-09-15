<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

abstract class ConfigurationTestCase extends TestCase
{
    use ConfigurationTestCaseTrait;

    #[DataProvider('validProcessedConfigurationDataProvider')]
    public function testProcessedConfigurationForNode(array $values, array $expectedProcessedConfiguration): void
    {
        $this->assertProcessedConfigurationEquals($values, $expectedProcessedConfiguration, $this->getNodeName());
    }

    abstract public static function validProcessedConfigurationDataProvider(): array;

    #[DataProvider('invalidProcessedConfigurationDataProvider')]
    public function testInvalidConfigurationForNode(?array $values): void
    {
        if (null === $values) {
            $this->expectNotToPerformAssertions();

            return;
        }

        // matthiasnoback/symfony-config-test hands the exception object to PHPUnit's ExceptionMessageIsOrContains
        // constraint, which only accepts the message string since PHPUnit 13.2, so assertConfigurationIsInvalid()
        // can not be used here and the configuration is processed directly instead.
        //
        // Fixed upstream in SymfonyTest/SymfonyConfigTest@c3daa66 but not yet released (latest tag: v6.2.0).
        // Once a release ships, bump the dependency and restore:
        // $this->assertConfigurationIsInvalid($values, sprintf('kununu_testing.%s', $this->getNodeName()));
        try {
            new Processor()->processConfiguration($this->getConfiguration(), $values);
        } catch (InvalidConfigurationException $exception) {
            self::assertStringContainsString(
                sprintf('kununu_testing.%s', $this->getNodeName()),
                $exception->getMessage()
            );

            return;
        }

        self::fail(sprintf('Configuration for node "%s" should be invalid', $this->getNodeName()));
    }

    public static function invalidProcessedConfigurationDataProvider(): ?array
    {
        if (empty($data = static::getInvalidProcessedConfigurationData())) {
            return [
                'no_tests' => [null],
            ];
        }

        return $data;
    }

    protected static function getInvalidProcessedConfigurationData(): array
    {
        return [];
    }

    abstract protected function getNodeName(): ?string;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}

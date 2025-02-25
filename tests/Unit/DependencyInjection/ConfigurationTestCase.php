<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

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
        $message = '';
        try {
            $invalid = true;
            if (null !== $values) {
                $this->assertConfigurationIsInvalid($values, sprintf('kununu_testing.%s', $this->getNodeName()));
            }
        } catch (Throwable $t) {
            $invalid = false;
            $message = $t->getMessage();
        }

        self::assertTrue($invalid, $message);
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

<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\DependencyInjection;

use Kununu\TestingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class ConfigurationTestCase extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @dataProvider validProcessedConfigurationDataProvider
     *
     * @param array $values
     * @param array $expectedProcessedConfiguration
     */
    public function testProcessedConfigurationForNode(array $values, array $expectedProcessedConfiguration): void
    {
        $this->assertProcessedConfigurationEquals($values, $expectedProcessedConfiguration, $this->getNodeName());
    }

    abstract public function validProcessedConfigurationDataProvider(): array;

    /**
     * @dataProvider invalidProcessedConfigurationDataProvider
     *
     * @param array|null $values
     *
     * @return void
     */
    public function testInvalidConfigurationForNode(?array $values): void
    {
        if (null === $values) {
            $this->assertTrue(true);
        } else {
            $this->assertConfigurationIsInvalid($values, sprintf('kununu_testing.%s', $this->getNodeName()));
        }
    }

    public function invalidProcessedConfigurationDataProvider(): ?array
    {
        if (empty($data = $this->getInvalidProcessedConfigurationData())) {
            return [
                'no_tests' => [null],
            ];
        }

        return $data;
    }

    protected function getInvalidProcessedConfigurationData(): array
    {
        return [];
    }

    abstract protected function getNodeName(): ?string;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}

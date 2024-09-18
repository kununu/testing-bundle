<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\AbstractTestCase;
use Kununu\TestingBundle\Tests\App\Elasticsearch\ClientFactory;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class AbstractTestCaseTest extends AbstractTestCase
{
    private const string EXISTING_PARAM = 'dummy_param';
    private const string NON_EXISTING_PARAM = 'non_existing_param';

    private const string EXISTING_SERVICE = ClientFactory::class;
    private const string NON_EXISTING_SERVICE = 'non_existing_service';

    public function testGetServiceFromContainer(): void
    {
        self::assertTrue($this->getFixturesContainer()->has(self::EXISTING_SERVICE));
        self::assertInstanceOf(ClientFactory::class, $this->getServiceFromContainer(self::EXISTING_SERVICE));

        self::assertFalse($this->getFixturesContainer()->has(self::NON_EXISTING_SERVICE));

        $this->expectException(ServiceNotFoundException::class);

        $this->getServiceFromContainer(self::NON_EXISTING_SERVICE);
    }

    public function testGetParameterFromContainer(): void
    {
        self::assertTrue($this->getFixturesContainer()->hasParameter(self::EXISTING_PARAM));
        self::assertEquals('i am a parameter', $this->getParameterFromContainer(self::EXISTING_PARAM));

        self::assertFalse($this->getFixturesContainer()->hasParameter(self::NON_EXISTING_PARAM));

        $this->expectException(ParameterNotFoundException::class);

        $this->getParameterFromContainer(self::NON_EXISTING_PARAM);
    }
}

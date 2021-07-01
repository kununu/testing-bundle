<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Service\SchemaCopy\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\UnsupportedDatabasePlatformException;
use Kununu\TestingBundle\Service\SchemaCopy\Factory\AdapterFactory;
use PHPUnit\Framework\TestCase;

final class AdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider createAdapterDataProvider
     *
     * @param string      $platformClass
     * @param string|null $expectedType
     */
    public function testCreateAdapter(string $platformClass, ?string $expectedType): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock($platformClass));

        if (null === $expectedType) {
            $this->expectException(UnsupportedDatabasePlatformException::class);
        }

        $adapter = (new AdapterFactory())->createAdapter($connection);

        if (null !== $expectedType) {
            $this->assertEquals($expectedType, $adapter->type());
        }
    }

    public function createAdapterDataProvider(): array
    {
        return [
            'mysql'   => [
                MySqlPlatform::class,
                'MySql',
            ],
            'invalid' => [
                AbstractPlatform::class,
                null,
            ],
        ];
    }
}

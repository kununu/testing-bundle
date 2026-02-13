<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\Service\SchemaCopy\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Kununu\TestingBundle\Service\SchemaCopy\Exception\UnsupportedDatabasePlatformException;
use Kununu\TestingBundle\Service\SchemaCopy\Factory\AdapterFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdapterFactoryTest extends TestCase
{
    #[DataProvider('createAdapterDataProvider')]
    public function testCreateAdapter(string $platformClass, ?string $expectedType): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($this->createStub($platformClass));

        if (null === $expectedType) {
            $this->expectException(UnsupportedDatabasePlatformException::class);
        }

        $adapter = new AdapterFactory()->createAdapter($connection);

        if (null !== $expectedType) {
            self::assertEquals($expectedType, $adapter->type());
        }
    }

    public static function createAdapterDataProvider(): array
    {
        return [
            'mysql'   => [
                MySQLPlatform::class,
                'MySql',
            ],
            'invalid' => [
                AbstractPlatform::class,
                null,
            ],
        ];
    }
}

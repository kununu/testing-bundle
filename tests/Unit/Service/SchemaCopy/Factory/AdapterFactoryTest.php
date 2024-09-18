<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Unit\Service\SchemaCopy\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
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
            ->expects(self::any())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock($platformClass));

        if (null === $expectedType) {
            $this->expectException(UnsupportedDatabasePlatformException::class);
        }

        $adapter = (new AdapterFactory())->createAdapter($connection);

        if (null !== $expectedType) {
            self::assertEquals($expectedType, $adapter->type());
        }
    }

    public static function createAdapterDataProvider(): array
    {
        return [
            'mysql'   => [
                MySQL80Platform::class,
                'MySql',
            ],
            'invalid' => [
                AbstractPlatform::class,
                null,
            ],
        ];
    }
}

<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test\Options;

use Kununu\TestingBundle\Test\Options\DbOptions;
use Kununu\TestingBundle\Test\Options\DbOptionsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DbOptionsTest extends TestCase
{
    #[DataProvider('dbOptionsDataProvider')]
    public function testDbOptions(
        DbOptionsInterface $options,
        bool $expectedAppend,
        bool $expectedClear,
        bool $expectedTransactional,
    ): void {
        self::assertEquals($expectedAppend, $options->append());
        self::assertEquals($expectedClear, $options->clear());
        self::assertEquals($expectedTransactional, $options->transactional());
    }

    public static function dbOptionsDataProvider(): array
    {
        return [
            'default'                               => [
                DbOptions::create(),
                false,
                true,
                true,
            ],
            'create_non_transactional'              => [
                DbOptions::createNonTransactional(),
                false,
                true,
                false,
            ],
            'without_transactional'                 => [
                DbOptions::create()->withoutTransactional(),
                false,
                true,
                false,
            ],
            'with_transactional'                    => [
                DbOptions::create()->withTransactional(),
                false,
                true,
                true,
            ],
            'with_parent_options_and_transactional' => [
                DbOptions::create()->withAppend()->withoutClear()->withTransactional(),
                true,
                false,
                true,
            ],
        ];
    }
}

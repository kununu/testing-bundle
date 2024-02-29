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
        bool $expectedTransactional
    ): void {
        $this->assertEquals($expectedAppend, $options->append());
        $this->assertEquals($expectedClear, $options->clear());
        $this->assertEquals($expectedTransactional, $options->transactional());
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
            'create non transactional'              => [
                DbOptions::createNonTransactional(),
                false,
                true,
                false,
            ],
            'without transactional'                 => [
                DbOptions::create()->withoutTransactional(),
                false,
                true,
                false,
            ],
            'with transactional'                    => [
                DbOptions::create()->withTransactional(),
                false,
                true,
                true,
            ],
            'with parent options and transactional' => [
                DbOptions::create()->withAppend()->withoutClear()->withTransactional(),
                true,
                false,
                true,
            ],
        ];
    }
}

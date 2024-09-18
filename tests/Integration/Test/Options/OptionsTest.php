<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test\Options;

use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Test\Options\OptionsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    #[DataProvider('optionsDataProvider')]
    public function testOptions(OptionsInterface $options, bool $expectedAppend, bool $expectedClear): void
    {
        self::assertEquals($expectedAppend, $options->append());
        self::assertEquals($expectedClear, $options->clear());
    }

    public static function optionsDataProvider(): array
    {
        return [
            'default'               => [
                Options::create(),
                false,
                true,
            ],
            'with_append'           => [
                Options::create()->withAppend(),
                true,
                true,
            ],
            'without_append'        => [
                Options::create()->withoutAppend(),
                false,
                true,
            ],
            'with_clear'            => [
                Options::create()->withClear(),
                false,
                true,
            ],
            'without_clear'         => [
                Options::create()->withoutClear(),
                false,
                false,
            ],
            'with_append_and_clear' => [
                Options::create()->withAppend()->withClear(),
                true,
                true,
            ],
        ];
    }
}

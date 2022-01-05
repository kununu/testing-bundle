<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test\Options;

use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Test\Options\OptionsInterface;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    /**
     * @dataProvider optionsDataProvider
     *
     * @param OptionsInterface $options
     * @param bool             $expectedAppend
     * @param bool             $expectedClear
     *
     * @return void
     */
    public function testOptions(OptionsInterface $options, bool $expectedAppend, bool $expectedClear): void
    {
        $this->assertEquals($expectedAppend, $options->append());
        $this->assertEquals($expectedClear, $options->clear());
    }

    public function optionsDataProvider(): array
    {
        return [
            'default'               => [
                Options::create(),
                false,
                true,
            ],
            'with append'           => [
                Options::create()->withAppend(),
                true,
                true,
            ],
            'without append'        => [
                Options::create()->withoutAppend(),
                false,
                true,
            ],
            'with clear'            => [
                Options::create()->withClear(),
                false,
                true,
            ],
            'without clear'         => [
                Options::create()->withoutClear(),
                false,
                false,
            ],
            'with append and clear' => [
                Options::create()->withAppend()->withClear(),
                true,
                true,
            ],
        ];
    }
}

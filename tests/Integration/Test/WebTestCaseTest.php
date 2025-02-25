<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;
use Kununu\TestingBundle\Tests\Integration\Test\DataFixtures\OtherWebTestCaseFixtures;
use Kununu\TestingBundle\Tests\Integration\Test\DataFixtures\WebTestCaseFixtures;
use PHPUnit\Framework\Attributes\DataProvider;

final class WebTestCaseTest extends WebTestCase
{
    public function testDoRequest(): void
    {
        $response = $this->doRequest(
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        self::assertEquals('{"key":"value"}', $response->getContent());
    }

    #[DataProvider('thatHttpFixturesGetLoadedDataProvider')]
    public function testThatHttpFixturesGetLoaded(array $fixtureClassNames): void
    {
        $this->loadHttpClientFixtures('http_client', Options::create(), ...$fixtureClassNames);

        $this->doRequest(
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        self::assertCount(count($fixtureClassNames), $this->getHttpClientFixtures('http_client'));
    }

    public static function thatHttpFixturesGetLoadedDataProvider(): array
    {
        return [
            'single_fixture' => [
                [WebTestCaseFixtures::class],
            ],
            'multiple_fixtures' => [
                [WebTestCaseFixtures::class, OtherWebTestCaseFixtures::class],
            ],
        ];
    }
}

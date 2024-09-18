<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;
use Kununu\TestingBundle\Tests\Integration\Test\DataFixtures\WebTestCaseFixtures;

final class WebTestCaseTest extends WebTestCase
{
    public function testDoRequest(): void
    {
        $response = $this->doRequest(
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        self::assertEquals('{"key":"value"}', $response->getContent());
    }

    public function testThatExtendsFixturesAwareTestCase(): void
    {
        self::assertTrue(is_subclass_of($this, FixturesAwareTestCase::class));
    }

    public function testThatHttpFixturesGetLoaded(): void
    {
        $this->loadHttpClientFixtures('http_client', Options::create(), WebTestCaseFixtures::class);

        $this->doRequest(
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        self::assertNotEmpty($this->getHttpClientFixtures('http_client'));
    }
}

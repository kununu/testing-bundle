<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;
use Kununu\TestingBundle\Tests\Integration\Test\DataFixtures\WebTestCaseFixture1;
use Kununu\TestingBundle\Tests\Integration\Test\DataFixtures\WebTestCaseFixture2;
use Symfony\Component\HttpFoundation\Response;

final class WebTestCaseTest extends WebTestCase
{
    private const string HTTP_CLIENT = 'http_client';

    public function testNoFixturesLoaded(): void
    {
        $response = $this->executeRequest();

        self::assertResponse($response);
        self::assertEmpty($this->getCurrentFixtures());
    }

    public function testFixturesLoadedAndRestored(): void
    {
        $this->loadHttpClientFixtures(
            self::HTTP_CLIENT,
            Options::create(),
            WebTestCaseFixture1::class,
            WebTestCaseFixture2::class
        );

        $this->assertLoadedFixtures();

        // Do a bunch of requests, all of them should restore the loaded http client fixtures
        // and those fixtures should respond if any call is made to the (fake) http client inside the code
        // handled by the request (in this case our controller can call a putative external service with
        // the http client)
        foreach ([1, null, 1, 1, 2, 2, 1, null] as $id) {
            $response = $this->executeRequest($id);

            self::assertResponse($response, $id);
            $this->assertLoadedFixtures();
        }
    }

    private static function assertResponse(Response $response, ?int $id = null): void
    {
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals(sprintf('{"key":%s}', $id ?? '"value"'), $response->getContent());
    }

    private function assertLoadedFixtures(): void
    {
        self::assertEquals(
            [WebTestCaseFixture1::class, WebTestCaseFixture2::class],
            array_keys($this->getCurrentFixtures())
        );
    }

    private function getCurrentFixtures(): array
    {
        return $this->getHttpClientFixtures(self::HTTP_CLIENT);
    }

    private function executeRequest(?int $id = null): Response
    {
        $request = RequestBuilder::aGetRequest()
            ->withUri(sprintf('/app/response%s', $id === null ? '' : sprintf('/%d', $id)));

        return $this->doRequest($request);
    }
}

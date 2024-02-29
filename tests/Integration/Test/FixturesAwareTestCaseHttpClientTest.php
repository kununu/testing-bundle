<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\Options\Options;
use Kununu\TestingBundle\Tests\App\Fixtures\HttpClient\HttpClientFixture1;
use Kununu\TestingBundle\Tests\App\Fixtures\HttpClient\HttpClientFixture2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FixturesAwareTestCaseHttpClientTest extends FixturesAwareTestCase
{
    private HttpClientInterface $httpClient;

    public function testLoadHttpClientFixturesWithAppend(): void
    {
        $this->registerInitializableFixtureForHttpClient('http_client', HttpClientFixture1::class);

        $this->loadHttpClientFixtures(
            'http_client',
            $options = Options::create()->withAppend(),
            HttpClientFixture1::class
        );
        $this->loadHttpClientFixtures('http_client', $options, HttpClientFixture2::class);

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data'
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data'
        );
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON
            ,
            $response->getContent()
        );
    }

    public function testLoadHttpClientFixturesWithoutAppend(): void
    {
        $this->loadHttpClientFixtures('http_client', $options = Options::create(), HttpClientFixture1::class);
        $this->loadHttpClientFixtures('http_client', $options, HttpClientFixture2::class);

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            'https://my.server/b7dd0cc2-381d-4e92-bc9b-b78245142e0a/data'
        );
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            'https://my.server/f2895c23-28cb-4020-b038-717cca64bf2d/data'
        );
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "id": 1000,
    "name": {
        "first": "The",
        "surname": "Name"
    },
    "age": 39,
    "newsletter": true
}
JSON
            ,
            $response->getContent()
        );
    }

    public function testClearFixtures(): void
    {
        $this->loadHttpClientFixtures(
            'http_client',
            Options::create(),
            HttpClientFixture1::class,
            HttpClientFixture2::class
        );
        $this->clearHttpClientFixtures('http_client');
        $this->assertEmpty($this->getHttpClientFixtures('http_client'));
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->getFixturesContainer()->get('http_client');
    }
}

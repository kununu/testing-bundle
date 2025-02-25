<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Integration\Test;

use Kununu\TestingBundle\Test\RequestBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestBuilderTest extends TestCase
{
    public function testBuildGetRequest(): void
    {
        $request = RequestBuilder::aGetRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('GET', $method);
        self::assertNull($uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildGetRequestWithQueryParameters(): void
    {
        $request = RequestBuilder::aGetRequest()
            ->withUri('/a/uri/path')
            ->withQueryParameters(['parameter1' => 'one', 'parameter_2' => 2]);

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('GET', $method);
        self::assertEquals('/a/uri/path?parameter1=one&parameter_2=2', $uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildPostRequest(): void
    {
        $request = RequestBuilder::aPostRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('POST', $method);
        self::assertNull($uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildPostRequestWithParameters(): void
    {
        $request = RequestBuilder::aPostRequest()->withParameters(['parameters']);

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('POST', $method);
        self::assertNull($uri);
        self::assertEquals(['parameters'], $parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildPostRequestWithQueryParameters(): void
    {
        $request = RequestBuilder::aPostRequest()
            ->withUri('/a/uri/path')
            ->withQueryParameters(['parameter1' => 'one', 'parameter_2' => 2]);

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('POST', $method);
        self::assertEquals('/a/uri/path?parameter1=one&parameter_2=2', $uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildPutRequest(): void
    {
        $request = RequestBuilder::aPutRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('PUT', $method);
        self::assertNull($uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildPatchRequest(): void
    {
        $request = RequestBuilder::aPatchRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('PATCH', $method);
        self::assertNull($uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildDeleteRequest(): void
    {
        $request = RequestBuilder::aDeleteRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('DELETE', $method);
        self::assertNull($uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildDeleteRequestWithQueryParameters(): void
    {
        $request = RequestBuilder::aDeleteRequest()
            ->withUri('/a/uri/path/')
            ->withQueryParameters(['param1' => 'value1', 'param_2' => 2]);

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        self::assertEquals('DELETE', $method);
        self::assertEquals('/a/uri/path/?param1=value1&param_2=2', $uri);
        self::assertEmpty($parameters);
        self::assertEmpty($files);
        self::assertEmpty($server);
        self::assertNull($content);
    }

    public function testBuildRequestWithMethod(): void
    {
        $request = RequestBuilder::aGetRequest()->withMethod('POST');

        [$method] = $request->build();

        self::assertEquals('POST', $method);
    }

    public function testBuildRequestWithUri(): void
    {
        $request = RequestBuilder::aGetRequest()->withUri('/v1/uri');

        [, $uri] = $request->build();

        self::assertEquals('/v1/uri', $uri);
    }

    public function testBuildRequestWithContent(): void
    {
        $request = RequestBuilder::aGetRequest()->withContent(['data' => ['key' => 'value']]);

        [, , , , , $content] = $request->build();

        self::assertEquals(json_encode(['data' => ['key' => 'value']]), $content);
    }

    public function testBuildRequestWithRawContent(): void
    {
        $request = RequestBuilder::aGetRequest()->withRawContent('just a content');

        [, , , , , $content] = $request->build();

        self::assertEquals('just a content', $content);
    }

    public function testBuildRequestWithAuthorization(): void
    {
        $request = RequestBuilder::aGetRequest()->withAuthorization('ACCESS_TOKEN_VALUE');

        [, , , , $server] = $request->build();

        self::assertEquals('Bearer ACCESS_TOKEN_VALUE', $server['HTTP_AUTHORIZATION']);
    }

    #[DataProvider('buildRequestWithHeaderDataProvider')]
    public function testBuildRequestWithHeader(string $headerName, string $expectedHeaderName): void
    {
        $headerValue = 'value';

        $request = RequestBuilder::aGetRequest()->withHeader($headerName, $headerValue);

        [, , , , $server] = $request->build();

        self::assertEquals($headerValue, $server[$expectedHeaderName]);
    }

    public static function buildRequestWithHeaderDataProvider(): array
    {
        return [
            'with_http'    => [
                'HTTP_AuthOrization',
                'HTTP_AUTHORIZATION',
            ],
            'without_http' => [
                'AuthOrization',
                'HTTP_AUTHORIZATION',
            ],
        ];
    }

    public function testBuildRequestWithServerParameters(): void
    {
        $request = RequestBuilder::aGetRequest()
            ->withServerParameter('REMOTE_ADDR', '127.0.0.1')
            ->withServerParameter('HTTP_ACCEPT', 'application/json');

        [, , , , $server] = $request->build();

        self::assertEquals(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_ACCEPT' => 'application/json'], $server);
    }
}

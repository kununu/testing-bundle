<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Kununu\TestingBundle\Test\RequestBuilder;
use PHPUnit\Framework\TestCase;

final class RequestBuilderTest extends TestCase
{
    public function testBuildGetRequest(): void
    {
        $request = RequestBuilder::aGetRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('GET', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildPostRequest(): void
    {
        $request = RequestBuilder::aPostRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('POST', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildPostRequestWithParameters(): void
    {
        $request = RequestBuilder::aPostRequest()->withParameters(['parameters']);

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('POST', $method);
        $this->assertNull($uri);
        $this->assertEquals(['parameters'], $parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildPutRequest(): void
    {
        $request = RequestBuilder::aPutRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('PUT', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildPatchRequest(): void
    {
        $request = RequestBuilder::aPatchRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('PATCH', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildDeleteRequest(): void
    {
        $request = RequestBuilder::aDeleteRequest();

        [$method, $uri, $parameters, $files, $server, $content] = $request->build();

        $this->assertEquals('DELETE', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildRequestWithMethod(): void
    {
        $request = RequestBuilder::aGetRequest()->withMethod('POST');

        [$method] = $request->build();
        $this->assertEquals('POST', $method);
    }

    public function testBuildRequestWithUri(): void
    {
        $request = RequestBuilder::aGetRequest()->withUri('/v1/uri');

        [, $uri] = $request->build();
        $this->assertEquals('/v1/uri', $uri);
    }

    public function testBuildRequestWithContent(): void
    {
        $request = RequestBuilder::aGetRequest()->withContent(['data' => ['key' => 'value']]);

        [, , , , , $content] = $request->build();
        $this->assertEquals(json_encode(['data' => ['key' => 'value']]), $content);
    }

    public function testBuildRequestWithRawContent(): void
    {
        $request = RequestBuilder::aGetRequest()->withRawContent('just a content');

        [, , , , , $content] = $request->build();
        $this->assertEquals('just a content', $content);
    }

    public function testBuildRequestWithAuthorization(): void
    {
        $request = RequestBuilder::aGetRequest()->withAuthorization('ACCESS_TOKEN_VALUE');

        [, , , , $server] = $request->build();
        $this->assertEquals('Bearer ACCESS_TOKEN_VALUE', $server['HTTP_AUTHORIZATION']);
    }

    /**
     * @dataProvider buildRequestWithHeaderDataProvider
     */
    public function testBuildRequestWithHeader(string $headerName, string $expectedHeaderName): void
    {
        $headerValue = 'value';

        $request = RequestBuilder::aGetRequest()->withHeader($headerName, $headerValue);

        [, , , , $server] = $request->build();
        $this->assertEquals($headerValue, $server[$expectedHeaderName]);
    }

    public function buildRequestWithHeaderDataProvider(): array
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
        $this->assertEquals(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_ACCEPT' => 'application/json'], $server);
    }
}

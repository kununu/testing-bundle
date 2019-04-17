<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Kununu\TestingBundle\Test\RequestBuilder;
use PHPUnit\Framework\TestCase;

final class RequestBuilderTest extends TestCase
{
    public function testBuildGetRequest()
    {
        $request = RequestBuilder::aGetRequest();

        list($method, $uri, $parameters, $files, $server, $content) = $request->build();

        $this->assertEquals('GET', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildPostRequest()
    {
        $request = RequestBuilder::aPostRequest();

        list($method, $uri, $parameters, $files, $server, $content) = $request->build();

        $this->assertEquals('POST', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildDeleteRequest()
    {
        $request = RequestBuilder::aDeleteRequest();

        list($method, $uri, $parameters, $files, $server, $content) = $request->build();

        $this->assertEquals('DELETE', $method);
        $this->assertNull($uri);
        $this->assertEmpty($parameters);
        $this->assertEmpty($files);
        $this->assertEmpty($server);
        $this->assertNull($content);
    }

    public function testBuildRequestWithMethod()
    {
        $request = RequestBuilder::aGetRequest();
        $request
            ->withMethod('POST');

        list($method) = $request->build();
        $this->assertEquals('POST', $method);
    }

    public function testBuildRequestWithUri()
    {
        $request = RequestBuilder::aGetRequest();
        $request
            ->withUri('/v1/uri');

        list(, $uri) = $request->build();
        $this->assertEquals('/v1/uri', $uri);
    }

    public function testBuildRequestWithContent()
    {
        $request = RequestBuilder::aGetRequest();
        $request
            ->withContent(['data' => ['key' => 'value']]);

        list(, , , , , $content) = $request->build();
        $this->assertEquals(json_encode(['data' => ['key' => 'value']]), $content);
    }

    public function testBuildRequestWithRawContent()
    {
        $request = RequestBuilder::aGetRequest();
        $request
            ->withRawContent('just a content');

        list(, , , , , $content) = $request->build();
        $this->assertEquals('just a content', $content);
    }

    public function testBuildRequestWithAuthorization()
    {
        $request = RequestBuilder::aGetRequest();
        $request
            ->withAuthorization('ACCESS_TOKEN_VALUE');

        list(, , , , $server) = $request->build();
        $this->assertEquals('Bearer ACCESS_TOKEN_VALUE', $server['HTTP_AUTHORIZATION']);
    }
}

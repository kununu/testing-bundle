<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Component\HttpFoundation\Request;

final class RequestBuilder
{
    private $method;

    private $uri;

    private $parameters = [];

    private $files = [];

    private $server = [];

    private $content;

    private function __construct(string $method)
    {
        $this->method = $method;
    }

    public static function aGetRequest(): self
    {
        return new self(Request::METHOD_GET);
    }

    public static function aPostRequest(): self
    {
        return new self(Request::METHOD_POST);
    }

    public static function aDeleteRequest(): self
    {
        return new self(Request::METHOD_DELETE);
    }

    public static function aPutRequest(): self
    {
        return new self(Request::METHOD_PUT);
    }

    public static function aPatchRequest(): self
    {
        return new self(Request::METHOD_PATCH);
    }

    public function build(): array
    {
        return [$this->method, $this->uri, $this->parameters, $this->files, $this->server, $this->content];
    }

    public function withParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function withMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function withUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function withContent(array $content): self
    {
        return $this->withRawContent(json_encode($content));
    }

    public function withRawContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withAuthorization(string $token): self
    {
        return $this->withHeader('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));
    }

    public function withHeader(string $headerName, string $headerValue): self
    {
        $headerName = strtoupper($headerName);

        if (substr($headerName, 0, 5) !== 'HTTP_') {
            $headerName = sprintf('HTTP_%s', $headerName);
        }

        $this->server[$headerName] = $headerValue;

        return $this;
    }

    public function withServerParameter(string $parameterName, string $parameterValue): self
    {
        $this->server[$parameterName] = $parameterValue;

        return $this;
    }
}

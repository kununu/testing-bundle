<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use Symfony\Component\HttpFoundation\Request;

final class RequestBuilder
{
    private ?string $uri = null;
    private array $parameters = [];
    private array $queryParameters = [];
    private array $files = [];
    private array $server = [];
    private ?string $content = null;

    private function __construct(private string $method)
    {
    }

    private function getUriWithQueryStringParameters(): ?string
    {
        return empty($this->queryParameters)
            ? $this->uri
            : sprintf('%s?%s', $this->uri, http_build_query($this->queryParameters));
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
        return [$this->method, $this->getUriWithQueryStringParameters(), $this->parameters, $this->files, $this->server, $this->content];
    }

    public function withParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function withQueryParameters(array $queryParameters): self
    {
        $this->queryParameters = $queryParameters;

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

        if (!str_starts_with($headerName, 'HTTP_')) {
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

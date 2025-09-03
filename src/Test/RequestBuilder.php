<?php
declare(strict_types=1);

namespace Kununu\TestingBundle\Test;

use BadMethodCallException;
use Kununu\Collection\AbstractBasicItem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method static self aConnectRequest()
 * @method static self aDeleteRequest()
 * @method static self aGetRequest()
 * @method static self aHeadRequest()
 * @method static self aOptionsRequest()
 * @method static self aPatchRequest()
 * @method static self aPostRequest()
 * @method static self aPurgeRequest()
 * @method static self aPutRequest()
 * @method static self aTraceRequest()
 * @method        self withFiles(array $files)
 * @method        self withMethod(string $method)
 * @method        self withParameters(array $parameters)
 * @method        self withQueryParameters(array $queryParameters)
 * @method        self withRawContent(string $rawContent)
 * @method        self withUri(string $uri)
 */
final class RequestBuilder extends AbstractBasicItem
{
    protected const string SETTER_PREFIX = 'with';
    protected const string GETTER_PREFIX = '';
    protected const array PROPERTIES = [
        self::FILES,
        self::METHOD,
        self::PARAMETERS,
        self::QUERY_PARAMETERS,
        self::RAW_CONTENT,
        self::URI,
    ];

    private const string FILES = 'files';
    private const string METHOD = 'method';
    private const string PARAMETERS = 'parameters';
    private const string QUERY_PARAMETERS = 'queryParameters';
    private const string RAW_CONTENT = 'rawContent';
    private const string URI = 'uri';

    private const array DEFAULTS = [
        self::FILES            => [],
        self::PARAMETERS       => [],
        self::QUERY_PARAMETERS => [],
    ];

    private const array HTTP_METHODS = [
        Request::METHOD_CONNECT,
        Request::METHOD_DELETE,
        Request::METHOD_GET,
        Request::METHOD_HEAD,
        Request::METHOD_OPTIONS,
        Request::METHOD_PATCH,
        Request::METHOD_POST,
        Request::METHOD_PURGE,
        Request::METHOD_PUT,
        Request::METHOD_TRACE,
    ];

    private const string STATIC_BUILDER_METHODS_TEMPLATE = 'a%sRequest';
    private const string INVALID_METHOD = 'Invalid static method "%s" called';
    private const string HTTP_HEADER_PREFIX = 'HTTP_';
    private const string HTTP_AUTHORIZATION_HEADER = 'HTTP_AUTHORIZATION';
    private const string HTTP_AUTHORIZATION_HEADER_BEARER = 'Bearer %s';

    private array $server = [];

    private function __construct(string $method)
    {
        parent::__construct(array_merge(self::DEFAULTS, [self::METHOD => $method]));
    }

    public static function __callStatic(string $method, array $args): self
    {
        foreach (self::HTTP_METHODS as $httpMethod) {
            if ($method === sprintf(self::STATIC_BUILDER_METHODS_TEMPLATE, ucwords(strtolower($httpMethod)))) {
                return new self($httpMethod);
            }
        }

        throw new BadMethodCallException(sprintf(self::INVALID_METHOD, $method));
    }

    public function build(): array
    {
        return [
            $this->getAttribute(self::METHOD),
            $this->buildUri(),
            $this->getAttribute(self::PARAMETERS),
            $this->getAttribute(self::FILES),
            $this->server,
            $this->getAttribute(self::RAW_CONTENT),
        ];
    }

    public function withAuthorization(string $token): self
    {
        return $this->withHeader(
            self::HTTP_AUTHORIZATION_HEADER,
            sprintf(self::HTTP_AUTHORIZATION_HEADER_BEARER, $token)
        );
    }

    public function withContent(array $content): self
    {
        return $this->withRawContent(json_encode($content));
    }

    public function withHeader(string $headerName, string $headerValue): self
    {
        $headerName = strtoupper($headerName);

        if (!str_starts_with($headerName, self::HTTP_HEADER_PREFIX)) {
            $headerName = sprintf('%s%s', self::HTTP_HEADER_PREFIX, $headerName);
        }

        $this->server[$headerName] = $headerValue;

        return $this;
    }

    public function withServerParameter(string $parameterName, string $parameterValue): self
    {
        $this->server[$parameterName] = $parameterValue;

        return $this;
    }

    private function buildUri(): ?string
    {
        $queryParameters = $this->getAttribute(self::QUERY_PARAMETERS);
        $uri = $this->getAttribute(self::URI);

        return empty($queryParameters)
            ? $uri
            : sprintf('%s?%s', $uri, http_build_query($queryParameters));
    }
}

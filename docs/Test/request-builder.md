# Request Builder

This bundle provides a [Request Builder](../../src/Test/RequestBuilder.php) which makes calling an endpoint more expressive.

```php
//----------------------------------------------------------------------------------------------------------------------

// Creates and returns a builder that you can use to do a CONNECT request
public static function aConnectRequest(): self;

// Creates and returns a builder that you can use to do a DELETE request
public static function aDeleteRequest(): self;

// Creates and returns a builder that you can use to do a GET request
public static function aGetRequest(): self;

// Creates and returns a builder that you can use to do a HEAD request
public static function aHeadRequest(): self;

// Creates and returns a builder that you can use to do a OPTIONS request
public static function aOptionsRequest(): self

// Creates and returns a builder that you can use to do a PATCH request
public static function aPatchRequest(): self;

// Creates and returns a builder that you can use to do a POST request
public static function aPostRequest(): self;

// Creates and returns a builder that you can use to do a PURGE request
public static function aPurgeRequest(): self

// Creates and returns a builder that you can use to do a PUT request
public static function aPutRequest(): self;

// Creates and returns a builder that you can use to do a TRACE request
public static function aTraceRequest(): self;

//----------------------------------------------------------------------------------------------------------------------

// Return an array used to build a request
public function build(): array

//----------------------------------------------------------------------------------------------------------------------

// Set an HTTP_AUTHORIZATION header with the value of "Bearer $token"
public function withAuthorization(string $token): self;

// Set the content of the request as an array that internally is transformed to JSON and provided as the raw body data
public function withContent(array $content): self;

// Set the request files
public function withFiles(array $files): self;

// Set a request header. In converts any header name to uppercase and prepends "HTTP_" if the header name does
// not contain it
public function withHeader(string $headerName, string $headerValue): self;

// Set the request method
public function withMethod(string $method): self;

// Set the request parameters
public function withParameters(array $parameters): self;

// Set the request query string parameters
public function withQueryParameters(array $queryParameters): self;

// Set the request raw body data
public function withRawContent(string $content): self;

// Set a request server parameter (HTTP headers are referenced with an HTTP_ prefix as PHP does)
public function withServerParameter(string $parameterName, string $parameterValue): self;

// Set the request URI
public function withUri(string $uri): self;
```

## Example

```php
<?php

use Kununu\TestingBundle\Test\RequestBuilder;

$requestBuilder = RequestBuilder::aGetRequest()
    ->withUri('/find-company')
    ->withQueryParameters(['id' => 10, 'name' => 'kununu'])
    ->withAuthorization('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjYyZDVkNzc5NmQxOTk')
    ->withServerParameter('REMOTE_ADDR', '127.0.0.1')
    ->withHeader('x-extra-header', 'my-value');
```

---

[Back to Index](../../README.md)

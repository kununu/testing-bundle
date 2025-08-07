<?php
declare(strict_types=1);

return [
    // HTTP GET to check reload after kernel shutdown
    [
        'url'    => '/test-http-fixtures',
        'body'   => <<<'JSON'
{
}
JSON,
    ],
];

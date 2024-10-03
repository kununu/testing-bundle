<?php
declare(strict_types=1);

return [
    // HTTP GET to check reload after kernel shutdown
    [
        'url'    => '/other-test-http-fixtures',
        'body'   => <<<'JSON'
{
}
JSON,
    ],
];

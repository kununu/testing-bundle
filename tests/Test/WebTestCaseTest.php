<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;

final class WebTestCaseTest extends WebTestCase
{
    public function testDoRequest()
    {
        $response = $this->doRequest(
            $this->getClient(),
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        $this->assertEquals('{"key":"value"}', $response->getContent());
    }
}

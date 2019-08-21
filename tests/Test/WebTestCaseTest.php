<?php declare(strict_types=1);

namespace Kununu\TestingBundle\Tests\Test;

use Kununu\TestingBundle\Test\FixturesAwareTestCase;
use Kununu\TestingBundle\Test\RequestBuilder;
use Kununu\TestingBundle\Test\WebTestCase;

/**
 * @group integration
 */
final class WebTestCaseTest extends WebTestCase
{
    public function testDoRequest(): void
    {
        $response = $this->doRequest(
            RequestBuilder::aGetRequest()->withUri('/app/response')
        );

        $this->assertEquals('{"key":"value"}', $response->getContent());
    }

    public function testThatExtendsFixturesAwareTestCase(): void
    {
        $this->assertTrue(is_subclass_of($this, FixturesAwareTestCase::class));
    }
}

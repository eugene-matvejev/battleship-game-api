<?php

namespace EM\Tests\Behat;

use Behat\Behat\Context\Context;
use EM\Tests\Environment\AbstractControllerTestCase;

class CommonControllerContext extends AbstractControllerTestCase implements Context
{
    /**
     * @BeforeScenario
     */
    public static function beforeEachScenario()
    {
        static::setUpBeforeClass();
    }

    /**
     * @Given request API :route route via :method
     *
     * @param string $route
     * @param string $method
     * @param string $content
     */
    public function requestAPIRoute(string $route, string $method, string $content = null)
    {
        $this->requestRoute(
            $route,
            $method,
            ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => 'application/json'],
            $content
        );
    }

    /**
     * @Given request :route route via :method
     *
     * @param string   $route
     * @param string   $method
     * @param string[] $server
     * @param string   $content
     */
    public function requestRoute(string $route, string $method, array $server = [], string $content = null)
    {
        static::$client->request(
            $method,
            $route,
            [],
            [],
            $server,
            $content
        );
    }

    /**
     * @Then observe response status code :statusCode
     *
     * @param int $statusCode
     */
    public function observeResponseStatusCode(int $statusCode)
    {
        $this->assertEquals($statusCode, static::$client->getResponse()->getStatusCode());
    }

    /**
     * @Then observe valid JSON response
     */
    public function observeValidJsonResponse()
    {
        $this->assertJson(static::$client->getResponse()->getContent());
    }

    /**
     * @Given observe redirection to :route
     *
     * @param string $route
     */
    public function observeRedirectionTo(string $route)
    {
        $this->assertEquals($route, static::$client->getResponse()->headers->get('location'));
    }
}

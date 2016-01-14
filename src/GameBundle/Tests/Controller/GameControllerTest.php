<?php

namespace GameBundle\Tests\Controller;

use GameBundle\Library\TestEnvironment\ExtendedAssertTestCase;
use Symfony\Component\HttpFoundation\Request;

class GameControllerTest extends ExtendedAssertTestCase
{
    /**
     * @test
     * @see GameBundle\Controller\GameController::indexAction()
     */
    public function index()
    {
        $client = $this->getClient();

        $client->request(Request::METHOD_GET, $this->getRouter()->generate('battleship.game.gui.index'));

        $this->assertCorrectResponse($client->getResponse());
    }

    /**
     * @test
     * @see GameBundle\Controller\GameController::initAction()
     */
    public function init()
    {
//        $client = $this->getClient();
//
//        $client->request(Request::METHOD_POST, $this->getRouter()->generate('battleship.game.api.init'));
//
//        $this->assertJsonCorrectResponse($client->getResponse());
    }

    /**
     * @test
     * @see GameBundle\Controller\GameController::turnAction()
     */
    public function turn()
    {
//        $client = $this->getClient();
//
//        $client->request(Request::METHOD_POST, $this->getRouter()->generate('battleship.game.api.turn'));
//
//        $this->assertJsonCorrectResponse($client->getResponse());
    }
}

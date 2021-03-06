<?php

namespace EM\Tests\PHPUnit\GameBundle\Controller;

use EM\GameBundle\Model\CellModel;
use EM\FoundationBundle\Model\PlayerModel;
use EM\Tests\Environment\AbstractControllerTestCase;
use EM\Tests\Environment\Cleaner\CellModelCleaner;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see GameController
 */
class GameControllerTest extends AbstractControllerTestCase
{
    /**
     * @see GameController::initAction
     * @test
     */
    public function unsuccessfulInitAction()
    {
        foreach (['application/xml', 'application/json'] as $acceptHeader) {
            $client = static::$client;
            $client->request(
                Request::METHOD_POST,
                '/api/game-init',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => $acceptHeader]
            );
            $this->assertUnsuccessfulResponse($client->getResponse());
        }
    }

    /**
     * @see     GameController::initAction
     * @test
     *
     * @depends unsuccessfulInitAction
     */
    public function successfulInitAction_JSON()
    {
        $client = static::$client;
        $client->request(
            Request::METHOD_POST,
            '/api/game-init',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => 'application/json'],
            static::getSharedFixtureContent('game-initiation-requests/valid/valid-1-opponent-7x7.json')
        );
        $this->assertSuccessfulJSONResponse($client->getResponse());

        $response = json_decode($client->getResponse()->getContent());
        $this->assertInternalType('array', $response);

        foreach ($response as $battlefield) {
            $this->assertInternalType('int', $battlefield->id);
            $this->assertInstanceOf(\stdClass::class, $battlefield->player);

            $this->assertInternalType('int', $battlefield->player->id);
            $this->assertInternalType('int', $battlefield->player->flags);
            $this->assertInternalType('string', $battlefield->player->name);

            $this->assertCount(49, (array)$battlefield->cells);
            foreach ($battlefield->cells as $cell) {
                $this->assertInternalType('int', $cell->id);
                $this->assertInternalType('int', $cell->flags);
                $this->assertInternalType('string', $cell->coordinate);

                /** as CPU fields should have CellModel::FLAG_NONE on initiation */
                $expected = $battlefield->player->flags == PlayerModel::FLAG_AI_CONTROLLED ? CellModel::FLAG_NONE : $cell->flags;
                $this->assertEquals($expected, $cell->flags);
            }
        }

        /** pass the response to the dependant class */
        return $response;
    }

    /**
     * @see     GameController::initAction
     * @test
     *
     * @depends unsuccessfulInitAction
     */
    public function successfulInitAction_XML()
    {
        $client = static::$client;
        $client->request(
            Request::METHOD_POST,
            '/api/game-init',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => 'application/xml'],
            static::getSharedFixtureContent('game-initiation-requests/valid/valid-1-opponent-7x7.json')
        );

        $this->assertSuccessfulXMLResponse($client->getResponse());

        $response = simplexml_load_string($client->getResponse()->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->assertInstanceOf(\SimpleXMLElement::class, $response);

        foreach ($response as $battlefield) {
            /** @var \SimpleXMLElement $battlefield */
            $this->assertInstanceOf(\SimpleXMLElement::class, $battlefield);
            $this->assertEquals('battlefield', $battlefield->getName());

            $player = $battlefield->player;
            /** @var \SimpleXMLElement $player */
            $this->assertInstanceOf(\SimpleXMLElement::class, $player);

            $this->assertInternalType('string', (string)$player->id);
            $this->assertInternalType('string', (string)$player->flags);
            $this->assertInternalType('string', (string)$player->name);

            $cells = $battlefield->cells->children();
            $this->assertEquals(49, $cells->count());
            foreach ($cells as $cell) {
                $this->assertInstanceOf(\SimpleXMLElement::class, $cell);

                $this->assertInternalType('string', (string)$cell->id);
                $this->assertInternalType('string', (string)$cell->flags);
                $this->assertInternalType('string', (string)$cell->coordinate);

                /** as CPU fields should have CellModel::FLAG_NONE on initiation */
                if ((string)$player->flags == PlayerModel::FLAG_AI_CONTROLLED) {
                    $this->assertEquals(CellModel::FLAG_NONE, (string)$cell->flags);
                } else {
                    $this->assertContains((string)$cell->flags, [CellModel::FLAG_NONE, CellModel::FLAG_SHIP, CellModel::FLAG_DEAD_SHIP]);
                }
            }
        }
    }

    /**
     * @see     GameController::turnAction
     * @test
     *
     * @depends successfulInitAction_JSON
     * @depends successfulInitAction_XML
     */
    public function unsuccessfulTurnActionOnNotExistingCell()
    {
        $client = static::$client;
        foreach (['application/xml', 'application/json'] as $acceptHeader) {
            $client->request(
                Request::METHOD_PATCH,
                '/api/game-turn/cell-id/0',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => $acceptHeader]
            );
            $this->assertUnsuccessfulResponse($client->getResponse());
        }
    }

    /**
     * simulate human interaction until game has been finished
     *
     * @see     GameController::turnAction
     * @test
     *
     * @depends successfulInitAction_JSON
     *
     * @param   \stdClass[] $response
     */
    public function successfulTurnAction(array $response)
    {
        foreach ($response as $battlefield) {
            if ($battlefield->player->flags !== PlayerModel::FLAG_AI_CONTROLLED) {
                continue;
            }

            foreach ($battlefield->cells as $cell) {
                CellModelCleaner::resetChangedCells();

                $client = static::$client;
                $client->request(
                    Request::METHOD_PATCH,
                    "/api/game-turn/cell-id/{$cell->id}",
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => 'application/json']
                );
                $this->assertSuccessfulJSONResponse($client->getResponse());

                $parsed = json_decode($client->getResponse()->getContent());
                if (isset($parsed->result)) {
                    return;
                }
            }
        }
    }

    /**
     * simulate human interaction until game has been finished
     *
     * @see     GameController::turnAction
     * @test
     *
     * @depends successfulInitAction_JSON
     *
     * @param  \stdClass[] $response
     */
    public function unsuccessfulTurnActionOnDeadCell(array $response)
    {
        foreach ($response as $battlefield) {
            if ($battlefield->player->flags === PlayerModel::FLAG_AI_CONTROLLED) {
                foreach ($battlefield->cells as $cell) {
                    CellModelCleaner::resetChangedCells();

                    $client = static::$client;
                    $client->request(
                        Request::METHOD_PATCH,
                        "/api/game-turn/cell-id/{$cell->id}",
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json', 'HTTP_accept' => 'application/json']
                    );
                    $this->assertUnsuccessfulResponse($client->getResponse());
                    break 2;
                }
            }
        }
    }
}

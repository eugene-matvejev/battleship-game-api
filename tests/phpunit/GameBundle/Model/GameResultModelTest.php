<?php

namespace EM\Tests\PHPUnit\GameBundle\Model;

use EM\GameBundle\Entity\GameResult;
use EM\GameBundle\Entity\Player;
use EM\GameBundle\Model\GameResultModel;
use EM\GameBundle\Response\GameResultsResponse;
use EM\Tests\PHPUnit\Environment\ExtendedTestSuite;
use EM\Tests\PHPUnit\Environment\MockFactory\Entity\GameMockTrait;
use EM\Tests\PHPUnit\Environment\MockFactory\Entity\GameResultMockTrait;

/**
 * @see GameResultModel
 */
class GameResultModelTest extends ExtendedTestSuite
{
    use GameMockTrait, GameResultMockTrait;
    /**
     * @var GameResultModel
     */
    private $gameResultModel;

    protected function setUp()
    {
        parent::setUp();
        $this->gameResultModel = $this->getContainer()->get('battleship.game.services.game.result.model');
    }

    /**
     * @see GameResultModel::prepareResponse()
     * @test
     */
    public function prepareResponse()
    {
        $player = static::$om->getRepository('GameBundle:Player')->find(1);
        for ($i = 0; $i < 21; $i++) {
            $game = $this->getGameMock(0);

            $result = $this->getGameResultMock()
                ->setPlayer($player);
            $game->setResult($result);
            static::$om->persist($game);
        }

        static::$om->flush();

        $response = $this->gameResultModel->prepareResponse(1);
        $this->assertEquals(3, $response->getMeta()[GameResultsResponse::META_INDEX_TOTAL_PAGES]);
        $this->assertEquals(1, $response->getMeta()[GameResultsResponse::META_INDEX_CURRENT_PAGE]);
        $this->assertCount(10, $response->getResults());
        $this->assertContainsOnlyInstancesOf(GameResult::class, $response->getResults());

        $response = $this->gameResultModel->prepareResponse(2);
        $this->assertEquals(3, $response->getMeta()[GameResultsResponse::META_INDEX_TOTAL_PAGES]);
        $this->assertEquals(2, $response->getMeta()[GameResultsResponse::META_INDEX_CURRENT_PAGE]);
        $this->assertCount(10, $response->getResults());
        $this->assertContainsOnlyInstancesOf(GameResult::class, $response->getResults());

        $response = $this->gameResultModel->prepareResponse(3);
        $this->assertEquals(3, $response->getMeta()[GameResultsResponse::META_INDEX_TOTAL_PAGES]);
        $this->assertEquals(3, $response->getMeta()[GameResultsResponse::META_INDEX_CURRENT_PAGE]);
        $this->assertCount(1, $response->getResults());
        $this->assertContainsOnlyInstancesOf(GameResult::class, $response->getResults());
    }
}

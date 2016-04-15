<?php

namespace EM\Tests\PHPUnit\GameBundle\Model;

use EM\GameBundle\Entity\GameResult;
use EM\GameBundle\Entity\Player;
use EM\GameBundle\Model\GameResultModel;
use EM\GameBundle\Response\GameResultsResponse;
use EM\Tests\Environment\ContainerAwareTestSuite;
use EM\Tests\Environment\MockFactory\Entity\GameMockTrait;
use EM\Tests\Environment\MockFactory\Entity\GameResultMockTrait;

/**
 * @see GameResultModel
 */
class GameResultModelTest extends ContainerAwareTestSuite
{
    use GameMockTrait, GameResultMockTrait;
    /**
     * @var GameResultModel
     */
    private $gameResultModel;

    protected function setUp()
    {
        parent::setUp();
        $this->gameResultModel = static::$container->get('battleship.game.services.game.result.model');
    }

    /**
     * @see GameResultModel::prepareResponse
     * @test
     */
    public function prepareResponse()
    {
        $resultsToPersist = 21;
        $player = static::$om->getRepository('GameBundle:Player')->find(1);
        for ($i = 0; $i < $resultsToPersist; $i++) {
            $game = $this->getGameMock(0);

            $result = $this->getGameResultMock()
                ->setPlayer($player);
            $game->setResult($result);
            static::$om->persist($game);
        }
        static::$om->flush();

        $perPage = static::$container->getParameter('battleship_game.game_results_per_page');
        $pages = ceil($resultsToPersist / $perPage);
        for ($page = 1; $page < $pages; $page++) {
            $response = $this->gameResultModel->prepareResponse($page);

            $this->assertEquals($pages, $response->getMeta()[GameResultsResponse::META_INDEX_TOTAL_PAGES]);
            $this->assertEquals($page, $response->getMeta()[GameResultsResponse::META_INDEX_CURRENT_PAGE]);

            $this->assertInternalType('array', $response->getResults());
            $this->assertContainsOnlyInstancesOf(GameResult::class, $response->getResults());
            $this->assertLessThanOrEqual($perPage, count($response->getResults()));
        }
    }
}

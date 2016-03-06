<?php

namespace EM\Tests\PHPUnit\GameBundle\Service\AI;

use EM\GameBundle\Entity\Battlefield;
use EM\GameBundle\Entity\Cell;
use EM\GameBundle\Entity\CellState;
use EM\GameBundle\Model\CellModel;
use EM\GameBundle\Service\AI\AIStrategyService;
use EM\Tests\PHPUnit\Environment\ExtendedTestCase;

/**
 * @see AIStrategyService
 */
class AIStrategyServiceTest extends ExtendedTestCase
{
    /**
     * @var AIStrategyService
     */
    private $strategyService;

    protected function setUp()
    {
        parent::setUp();
        $this->strategyService = $this->getContainer()->get('battleship.game.services.ai.strategy.service');
    }

    /**
     * @see AIStrategyService::attack()
     * @test
     */
    public function attack()
    {
        $battlefield = $this->getMockedBattlefield();
        $cells = $this->strategyService->attack($battlefield);
        $this->assertCount(0, $cells);

        $cellState = (new CellState())
            ->setId(CellModel::STATE_SHIP_DIED)
            ->setName('test cell state');

        foreach ($battlefield->getCells() as $cell) {
            if ($cell->getX() === 2 && $cell->getY() === 2) {
                $cell->setState($cellState);
            }
        }

//        $cells = $this->strategyService->attack($battlefield);
//        $this->assertCount(4, $cells);
//
//        foreach ($battlefield->getCells() as $cell) {
//            if ($cell->getState()->getId() !== CellModel::STATE_SHIP_DIED || $this->isShipDead($cell)) {
//                continue;
//            }
//
//            switch ($this->decideStrategy($cell)) {
//                case self::STRATEGY_X:
//                    return $this->xStrategy->verify($cell);
//                case self::STRATEGY_Y:
//                    return $this->yStrategy->verify($cell);
//            }
//
//            return $this->randStrategy->verify($cell);
//        }
//
//        return [];
    }

    /**
     * @coversNothing
     */
    protected function getMockedBattlefield() : Battlefield
    {
        $battlefield = new Battlefield();
        $cellState = (new CellState())
            ->setName('test cell state')
            ->setId(CellModel::STATE_WATER_LIVE);
        for ($x = 0; $x < 10; $x++) {
            for ($y = 0; $y < 10; $y++) {
                $cell = (new Cell())
                    ->setX($x)
                    ->setY($y)
                    ->setState($cellState);

                $battlefield->addCell($cell);
            }
        }

        return $battlefield;
    }

//    const STRATEGY_X        = 0;
//    const STRATEGY_Y        = 1;
//    const STRATEGY_RAND     = 2;
//    const COORDINATES_STEPS = [-1, 0, 1];
//    /**
//     * @var CellModel
//     */
//    private $cellModel;
//
//    public function __construct(CellModel $model, XStrategy $xStrategy, YStrategy $yStrategy, RandomStrategy $randomStrategy)
//    {
//        $this->cellModel = $model;
//        $this->xStrategy = $xStrategy;
//        $this->yStrategy = $yStrategy;
//        $this->randStrategy = $randomStrategy;
//    }
//
//    /**
//     * @since 3.5
//     *
//     * @param Cell $cell
//     *
//     * @return int
//     */
//    private function decideStrategy(Cell $cell) : int
//    {
//        $coordinates = [
//            new CoordinatesPair(CoordinatesPair::WAY_LEFT, $cell->getX() + 1, $cell->getY()), // -- left
//            new CoordinatesPair(CoordinatesPair::WAY_RIGHT, $cell->getX() - 1, $cell->getY()),// ++ right
//        ];
//        foreach ($coordinates as $coordinatePair) {
//            if (null !== $_cell = $this->cellModel->getByCoordinatesPair($coordinatePair)) {
//                if ($_cell->getState()->getId() === CellModel::STATE_SHIP_DIED) {
//                    return self::STRATEGY_X;
//                }
//            }
//        }
//
//        $coordinates = [
//            new CoordinatesPair(CoordinatesPair::WAY_UP, $cell->getX(), $cell->getY() + 1),   // -- up
//            new CoordinatesPair(CoordinatesPair::WAY_DOWN, $cell->getX(), $cell->getY() - 1)  // ++ down
//        ];
//        foreach ($coordinates as $coordinatePair) {
//            if (null !== $_cell = $this->cellModel->getByCoordinatesPair($coordinatePair)) {
//                if ($_cell->getState()->getId() === CellModel::STATE_SHIP_DIED) {
//                    return self::STRATEGY_Y;
//                }
//            }
//        }
//
//        return self::STRATEGY_RAND;
//    }
//
//    public function isShipDead(Cell $cell) : bool
//    {
//        if ($cell->getState()->getId() !== CellModel::STATE_SHIP_DIED) {
//            return false;
//        }
//
//        $coordinates = [
//            new CoordinatesPair(CoordinatesPair::WAY_LEFT, $cell->getX() + 1, $cell->getY()), // -- left
//            new CoordinatesPair(CoordinatesPair::WAY_RIGHT, $cell->getX() - 1, $cell->getY()),// ++ right
//            new CoordinatesPair(CoordinatesPair::WAY_UP, $cell->getX(), $cell->getY() + 1),   // -- up
//            new CoordinatesPair(CoordinatesPair::WAY_DOWN, $cell->getX(), $cell->getY() - 1)  // ++ down
//        ];
//        $cells = [$cell];
//
//        /**
//         * @var CoordinatesPair[] $coordinates
//         */
//        foreach ($coordinates as $coordinatesPair) {
//            while (null !== $cell = $this->cellModel->getByCoordinatesPair($coordinatesPair)) {
//                if (!in_array($cell->getState()->getId(), CellModel::STATES_SHIP)) {
//                    break;
//                }
//                if ($cell->getState()->getId() !== CellModel::STATE_SHIP_DIED) {
//                    return false;
//                }
//
//                $coordinatesPair->prepareForNextStep();
//                $cells[] = $cell;
//            }
//        }
//
//        /**
//         * @var Cell[] $cells
//         *
//         *  x-1; y-1 | x ; y-1 | x+1; y-1
//         *  x-1;   y | x ; y   | x+1; y
//         *  x-1; y+1 | x ; y+1 | x+1; y+1
//         */
//        foreach ($cells as $cell) {
//            foreach (self::COORDINATES_STEPS as $x) {
//                foreach (self::COORDINATES_STEPS as $y) {
//                    if (null !== $_cell = $this->cellModel->getByCoordinates($cell->getX() + $x, $cell->getY() + $y)) {
//                        $this->cellModel->switchStateToSkipped($_cell);
//                    }
//                }
//            }
//        }
//
//        return true;
//    }
}

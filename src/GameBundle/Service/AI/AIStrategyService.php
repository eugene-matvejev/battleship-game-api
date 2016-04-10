<?php

namespace EM\GameBundle\Service\AI;

use EM\GameBundle\Entity\Battlefield;
use EM\GameBundle\Entity\Cell;
use EM\GameBundle\Model\CellModel;
use EM\GameBundle\Service\CoordinateSystem\PathProcessor;

/**
 * @since 3.0
 */
class AIStrategyService
{
    const STRATEGY_MAP = [
        PathProcessor::PATH_LEFT  => AIStrategyProcessor::STRATEGY_HORIZONTAL,
        PathProcessor::PATH_RIGHT => AIStrategyProcessor::STRATEGY_HORIZONTAL,
        PathProcessor::PATH_UP    => AIStrategyProcessor::STRATEGY_VERTICAL,
        PathProcessor::PATH_DOWN  => AIStrategyProcessor::STRATEGY_VERTICAL
    ];
    /**
     * @var AIStrategyProcessor
     */
    private $strategyProcessor;
    /**
     * @var CellModel
     */
    private $cellModel;

    public function __construct(CellModel $model, AIStrategyProcessor $strategyProcessor)
    {
        $this->cellModel = $model;
        $this->strategyProcessor = $strategyProcessor;
    }

    /**
     * @param Battlefield $battlefield
     *
     * @return Cell[]
     */
    public function chooseCells(Battlefield $battlefield) : array
    {
        foreach ($battlefield->getCells() as $cell) {
            if ($cell->getState()->getId() !== CellModel::STATE_SHIP_DIED || $this->cellModel->isShipDead($cell)) {
                continue;
            }

            return $this->strategyProcessor->process($cell, $this->chooseStrategy($cell));
        }

        return [];
    }

    /**
     * @since 3.5
     *
     * @param Cell $cell
     *
     * @return int
     */
    private function chooseStrategy(Cell $cell) : int
    {
        $service = new PathProcessor($cell);

        foreach (self::STRATEGY_MAP as $way => $strategyId) {
            $service->setPath($way);

            if (null !== $_cell = $cell->getBattlefield()->getCellByCoordinate($service->getNextCoordinate())) {
                if ($_cell->getState()->getId() === CellModel::STATE_SHIP_DIED) {
                    return $strategyId;
                }
            }
        }

        return AIStrategyProcessor::STRATEGY_BOTH;
    }
}

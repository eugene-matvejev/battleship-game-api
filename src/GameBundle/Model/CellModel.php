<?php

namespace EM\GameBundle\Model;

use EM\GameBundle\Entity\Battlefield;
use EM\GameBundle\Entity\Cell;
use EM\GameBundle\Entity\CellState;
use EM\GameBundle\Repository\CellStateRepository;
use EM\GameBundle\Service\CoordinateSystem\CoordinatesPair;

/**
 * @since 2.0
 */
class CellModel
{
    const STATE_WATER_LIVE = 1;
    const STATE_WATER_DIED = 2;
    const STATE_SHIP_LIVE  = 3;
    const STATE_SHIP_DIED  = 4;
    const STATE_WATER_SKIP = 5;
    /** instead of functions, as const array is faster */
    const STATES_WATER = [self::STATE_WATER_LIVE, self::STATE_WATER_DIED];
    const STATES_SHIP  = [self::STATE_SHIP_LIVE, self::STATE_SHIP_DIED];
    const STATES_LIVE  = [self::STATE_WATER_LIVE, self::STATE_SHIP_LIVE];
    const STATES_DIED  = [self::STATE_WATER_DIED, self::STATE_SHIP_DIED];
    const STATES_ALL   = [self::STATE_WATER_LIVE, self::STATE_WATER_DIED, self::STATE_SHIP_LIVE, self::STATE_SHIP_DIED, self::STATE_WATER_SKIP];

    /**
     * @var CellState[]
     */
    private static $cellStates;
    /**
     * @var Cell[][]
     */
    private $cachedCells;
    /**
     * @var Cell[]
     */
    private static $changedCells = [];

    public function __construct(CellStateRepository $repository)
    {
        if (null === self::$cellStates) {
            self::$cellStates = $repository->getAllIndexed();
        }
    }

    /**
     * @return CellState[]
     */
    public function getCellStates() : array
    {
        return self::$cellStates;
    }

    /**
     * @return Cell[]
     */
    public static function getChangedCells() : array
    {
        return self::$changedCells;
    }

    public function switchState(Cell $cell, int $state = null) : Cell
    {
        switch ($cell->getState()->getId()) {
            case self::STATE_WATER_LIVE:
                $cell->setState(self::$cellStates[$state ?? self::STATE_WATER_DIED]);
                self::$changedCells[] = $cell;
                break;
            case self::STATE_SHIP_LIVE:
                if (!isset($state)) {
                    $cell->setState(self::$cellStates[self::STATE_SHIP_DIED]);
                    self::$changedCells[] = $cell;
                }
                break;
        }

        return $cell;
    }

    public function switchStateToSkipped(Cell $cell) : Cell
    {
        return $this->switchState($cell, self::STATE_WATER_SKIP);
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return Cell|null
     */
    public function getByCoordinates(int $x, int $y)
    {
        return $this->cachedCells[$x][$y] ?? null;
    }

    /**
     * @param CoordinatesPair $pair
     *
     * @return Cell|null
     */
    public function getByCoordinatesPair(CoordinatesPair $pair)
    {
        return $this->getByCoordinates($pair->getX(), $pair->getY());
    }

    public function indexCells(Battlefield $battlefield)
    {
        $this->cachedCells = [];

        foreach ($battlefield->getCells() as $cell) {
            if (!isset($this->cachedCells[$cell->getX()])) {
                $this->cachedCells[$cell->getX()] = [];
            }

            $this->cachedCells[$cell->getX()][$cell->getY()] = $cell;
        }
    }
}

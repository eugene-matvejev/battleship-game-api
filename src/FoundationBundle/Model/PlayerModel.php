<?php

namespace EM\FoundationBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use EM\FoundationBundle\Entity\Player;
use EM\FoundationBundle\Exception\PlayerException;

/**
 * @since 2.0
 */
class PlayerModel
{
    const FLAG_NONE          = 0x00;
    const FLAG_AI_CONTROLLED = 0x01;
    /**
     * @var ObjectRepository
     */
    private $repository;

    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $name
     *
     * @return Player
     * @throws PlayerException
     */
    public function createOnRequestAIControlled(string $name) : Player
    {
        return $this->createOnRequest($name, true);
    }

    /**
     * @param string $name
     *
     * @return Player
     * @throws PlayerException
     */
    public function createOnRequestHumanControlled(string $name) : Player
    {
        return $this->createOnRequest($name, false);
    }

    /**
     * @param string $name
     * @param bool   $controlledByAI
     *
     * @return Player
     * @throws PlayerException
     */
    protected function createOnRequest(string $name, bool $controlledByAI) : Player
    {
        /** @var Player $player */
        $player = $this->repository->findOneBy(['name' => $name]);

        if (null !== $player && $controlledByAI !== static::isAIControlled($player)) {
            throw new PlayerException("player with '$name' already exists and controlledByAI do not match");
        }

        return $player ?? (new Player())
            ->setName($name)
            ->setFlags($controlledByAI ? static::FLAG_AI_CONTROLLED : static::FLAG_NONE);
    }

    public static function isAIControlled(Player $player) : bool
    {
        return $player->hasFlag(self::FLAG_AI_CONTROLLED);
    }
}

<?php

namespace EM\Tests\PHPUnit\FoundationBundle\ORM;

use EM\Tests\Environment\AbstractKernelTestSuite;
use EM\Tests\Environment\Factory\MockFactory;

/**
 * @see TimestampedTrait
 */
class TimestampedTraitTest extends AbstractKernelTestSuite
{
    /**
     * @see TimestampedTrait::setTimestamp
     * @test
     */
    public function setTimestampSetOnPersist()
    {
        $result = MockFactory::getGameResultMock(2, 0);
        $player = $result->getGame()->getBattlefields()[0]->getPlayer();
        $result->setPlayer($player);
        static::$om->persist($result->getGame());

        $this->assertInstanceOf(\DateTime::class, $result->getTimestamp());
    }
}

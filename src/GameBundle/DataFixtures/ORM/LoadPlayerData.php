<?php

namespace EM\GameBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use EM\FoundationBundle\Entity\Player;
use EM\FoundationBundle\Model\PlayerModel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @since 3.5
 */
class LoadPlayerData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    const TEST_HUMAN_PLAYER_EMAIL         = 'Human';
    const TEST_AI_CONTROLLED_PLAYER_EMAIL = 'CPU 0';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $model = $this->container->get('em.game_bundle.service.player_model');

        $om->persist($model->createOnRequestHumanControlled(static::TEST_HUMAN_PLAYER_EMAIL));
        $om->persist($model->createOnRequestAIControlled(static::TEST_AI_CONTROLLED_PLAYER_EMAIL));

        $om->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}

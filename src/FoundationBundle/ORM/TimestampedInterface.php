<?php

namespace EM\FoundationBundle\ORM;

/**
 * @since 3.1
 */
interface TimestampedInterface
{
    public function setTimestamp();

    public function getTimestamp() : \DateTime;
}

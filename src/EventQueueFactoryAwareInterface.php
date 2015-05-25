<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

/**
 * Interface for injection of the event queue factory, which resolves
 * connection instances to event queue instances.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface EventQueueFactoryAwareInterface
{
    /**
     * Sets the client for the implementing class to use.
     *
     * @param \Phergie\Irc\Bot\React\EventQueueFactoryInterface $queueFactory
     */
    public function setEventQueueFactory(EventQueueFactoryInterface $queueFactory);
}

<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\ConnectionInterface;

/**
 * Interface for a factory to maintain connection-specific event queue objects.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface EventQueueFactoryInterface
{
    /**
     * Returns the event queue for a specified connection.
     *
     * @param \Phergie\Irc\ConnectionInterface $connection
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    public function getEventQueue(ConnectionInterface $connection);
}

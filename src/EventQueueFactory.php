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
 * Default implementation of a factory to maintain connection-specific event
 * queue objects.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueueFactory implements EventQueueFactoryInterface
{
    /**
     * Stores event queues keyed by connection
     *
     * @var \SplObjectStorage
     */
    protected $queues;

    /**
     * Initializes storage for event queues.
     */
    public function __construct()
    {
        $this->queues = new \SplObjectStorage;
    }

    /**
     * Returns the event queue for a specified connection.
     *
     * @param \Phergie\Irc\ConnectionInterface $connection
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    public function getEventQueue(ConnectionInterface $connection)
    {
        if (!isset($this->queues[$connection])) {
            $this->queues[$connection] = new EventQueue;
        }
        return $this->queues[$connection];
    }
}

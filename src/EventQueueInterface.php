<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\GeneratorInterface;

/**
 * Interface for a queue to contain commands issued by plugins to be sent to
 * servers so as to allow for manipulation of those commands by plugins prior
 * to their transmission.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface EventQueueInterface extends GeneratorInterface, \IteratorAggregate, \Countable
{
    /**
     * Removes and returns an event from the front of the queue.
     *
     * @return \Phergie\Irc\Event\EventInterface|null Removed event or null if
     *         the queue is empty
     */
    public function extract();
}

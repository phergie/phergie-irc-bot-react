<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

/**
 * Implementation of SplPriorityQueue specifically for internal use within
 * the EventQueue class.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueueInternal extends \SplPriorityQueue
{
    /**
     * Overrides native default comparison logic to assign higher priority to
     * events inserted earlier.
     *
     * @param \Phergie\Irc\Bot\React\EventQueuePriority $priority1
     * @param \Phergie\Irc\Bot\React\EventQueuePriority $priority2
     * @return int
     */
    public function compare($priority1, $priority2)
    {
        if (!$priority1 instanceof EventQueuePriority || !$priority2 instanceof EventQueuePriority) {
            return parent::compare($priority1, $priority2);
        }

        $priority = $priority1->value - $priority2->value;
        if (!$priority) {
            $priority = $priority2->timestamp - $priority1->timestamp;
        }
        return $priority;
    }
}

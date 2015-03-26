<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

/**
 * Value object for an event queue priority.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueuePriority
{
    /**
     * Priority value associated with the event command
     *
     * @var int
     */
    public $value;

    /**
     * Timestamp for insertion of the event, used to compare events with the
     * same command in order to assign higher priority to events inserted
     * earlier
     *
     * @var int
     */
    public $timestamp;
}

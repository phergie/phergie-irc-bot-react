<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

/**
 * Minimum interface for a plugin implementation.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface PluginInterface
{
    /**
     * Returns a mapping of events to applicable callbacks.
     *
     * @return array Associative array keyed by event name referencing strings
     *         containing names of instance methods in the class implementing
     *         this interface or valid callables
     */
    public function getSubscribedEvents();
}

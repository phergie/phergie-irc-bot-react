<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\Plugin;

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
     * This method name is taken from Symfony\Component\EventDispatcher\EventSubscriberInterface.
     * The main difference is that this implementation uses an instance method
     * rather than a static method. The reason for this is to allow different
     * instances of the same plugin to listen for different events based on
     * configuration and other environmental conditions.
     *
     * @return array Associative array keyed by event name where each value i
     *         a valid callback or array of callback
     */
    public function getSubscribedEvents();
}

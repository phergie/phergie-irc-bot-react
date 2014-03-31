<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

/**
 * Specialized connection interface to take property values from an array
 * passed to the constructor and add accessor methods for connection-specific
 * plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface ConnectionInterface extends \Phergie\Irc\ConnectionInterface
{
    /**
     * Constructor to accept property values.
     *
     * @param array $config Associative array keyed by property name
     */
    public function __construct(array $config);

    /**
     * Sets a list of plugins to associate with the connection.
     *
     * @param \Phergie\Irc\Bot\React\Plugin\PluginInterface[] $plugins
     */
    public function setPlugins(array $plugins);

    /** 
     * Returns a list of plugins associated with the connection.
     *
     * @return \Phergie\Irc\Bot\React\Plugin\PluginInterface[]
     */
    public function getPlugins();
}

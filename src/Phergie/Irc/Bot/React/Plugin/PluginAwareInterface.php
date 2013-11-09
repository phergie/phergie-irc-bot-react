<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React\Plugin
 */

namespace Phergie\Irc\Bot\React\Plugin;

/**
 * Interface for handling the optional plugin collection dependency of plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface PluginAwareInterface
{
    /**
     * Sets the plugin collection for the plugin to use.
     *
     * @param \Phergie\Irc\Bot\React\Plugin\CollectionInterface $collection
     */
    public function setPluginCollection(CollectionInterface $collection);

    /**
     * Returns the plugin collection in use by the plugin.
     *
     * @return \Phergie\Irc\Bot\React\Plugin\CollectionInterface
     */
    public function getPluginCollection();
}

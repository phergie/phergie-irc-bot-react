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
 * Interface for plugins that contain nested plugins which
 * the bot would not ordinarily be aware of.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface PluginContainerInterface
{
    /**
     * Returns an array of plugins within the container.
     *
     * @return \Phergie\Irc\Bot\React\Plugin[]
     */
    public function getPlugins();
}

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
 * Interface for implementing bots.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface BotInterface
{
    /**
     * Sets the configuration files to be read and used by the bot in its
     * operations.
     *
     * See config.sample.php for an example configuration file.
     *
     * @param array $configs List of one or more paths to configuration files
     *        for the bot to use
     */
    public function setConfigs(array $configs);

    /**
     * Initiates an event loop for the bot in which it will connect to servers
     * and monitor those connects for events to forward to plugins.
     */
    public function run();
}

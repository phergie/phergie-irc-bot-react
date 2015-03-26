<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\PluginProcessor;

use Phergie\Irc\Bot\React\Bot;
use Phergie\Irc\Bot\React\ClientAwareInterface;
use Phergie\Irc\Bot\React\PluginInterface;

/**
 * Plugin processor that injects the plugin with the bot's client if
 * possible.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class ClientInjector implements PluginProcessorInterface
{
    /**
     * Injects the bot's client into the plugin if it implements
     * \Phergie\Irc\Bot\React\ClientAwareInterface.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface $plugin Loaded plugin
     * @param \Phergie\Irc\Bot\React\Bot $bot Bot that loaded the plugin
     */
    public function process(PluginInterface $plugin, Bot $bot)
    {
        if ($plugin instanceof ClientAwareInterface) {
            $plugin->setClient($bot->getClient());
        }
    }
}

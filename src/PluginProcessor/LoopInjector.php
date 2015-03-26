<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\PluginProcessor;

use Phergie\Irc\Bot\React\Bot;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Client\React\LoopAccessorInterface;
use Phergie\Irc\Bot\React\PluginInterface;

/**
 * Plugin processor that injects the plugin with the event loop of the bot's
 * client if possible.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class LoopInjector implements PluginProcessorInterface
{
    /**
     * Injects the event loop of the bot's client into the plugin if it implements
     * \Phergie\Irc\Bot\React\LoopInterface.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface $plugin Loaded plugin
     * @param \Phergie\Irc\Bot\React\Bot $bot Bot that loaded the plugin
     */
    public function process(PluginInterface $plugin, Bot $bot)
    {
        $client = $bot->getClient();
        if ($plugin instanceof LoopAwareInterface
            && $client instanceof LoopAccessorInterface) {
            $plugin->setLoop($client->getLoop());
        }
    }
}

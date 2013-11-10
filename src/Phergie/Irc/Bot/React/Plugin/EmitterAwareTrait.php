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

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;

/**
 * Trait for handling the optional event emitter dependency of plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
trait EmitterAwareTrait implements EmitterAwareInterface
{
    /**
     * Event emitter in use by the plugin
     *
     * @var \Evenement\EventEmitterInterface
     */
    protected $emitter;

    /**
     * Sets the event emitter for the plugin to use.
     *
     * @param \Evenement\EventEmitterInterface $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Returns the event emitter in use by the plugin.
     *
     * @return \Evenement\EventEmitterInterface
     */
    public function getEventEmitter()
    {
        if (!$this->emitter) {
            $this->emitter = new EventEmitter;
        }
        return $this->emitter;
    }
}

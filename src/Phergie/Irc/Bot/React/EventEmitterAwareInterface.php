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

use Evenement\EventEmitterInterface;

/**
 * Interface for injection of an event emitter.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface EventEmitterAwareInterface
{
    public function setEventEmitter(EventEmitterInterface $emitter);
}

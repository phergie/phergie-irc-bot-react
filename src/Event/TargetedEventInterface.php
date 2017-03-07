<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Event
 */

namespace Phergie\Event;

/**
 * Interface for the TargetedEvent
 */
interface TargetedEventInterface
{
    /**
     * Accessor method to retrieve targets
     *
     * @return array an array of targets
     */
    public function getTargets();

    /**
     * Accessor method to set targets
     * 
     * @param array $targets an array of targets
     */
    public function setTargets(array $targets);
}

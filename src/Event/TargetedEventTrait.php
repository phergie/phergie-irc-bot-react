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
 * TargetedEvent Trait
 *
 * @category Phergie
 * @package Phergie\Event
 */
trait TargetedEventTrait implements TargetedEventInterface
{
    /**
     * Array of targets
     *
     * @var array $targets an array of targets
     */
    protected $targets;

    /**
     * Accessor method to retrieve targets
     *
     * @return array array of targets
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Accessor method to set the targets
     *
     * @param array array of targets
     */
    public function setTargets(array $targets)
    {
        $this->targets = $targets;
    }
}

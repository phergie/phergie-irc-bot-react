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
 * Interface for a container of plugin objects.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface CollectionInterface extends \IteratorAggregate, \Countable, \ArrayAcce
{
    /**
     * Returns an iterator for a filtered subset of plugins in the container
     * that extend a given class, implement a given interface, or use a given
     * trait.
     *
     * @param string $entity Fully qualified class, interface, or trait name
     * @return \Iterator
     */
    public function getIteratorForClass($class);
}

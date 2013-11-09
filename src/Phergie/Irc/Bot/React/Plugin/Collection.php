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

/**
 * Container for plugin objects.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class Collection implements CollectionInterface
{
    /**
     * List of plugin
     *
     * @var array
     */
    protected $plugins;

    /**
     * Initializes the container with a list of plugins.
     *
     * @param array $plugin
     */
    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    /**
     * Returns an iterator for the contained list of plugins.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->plugins);
    }

    /**
     * Returns the number of plugins in the container.
     *
     * @return int
     */
    public function count()
    {
        return count($this->plugins);
    }

    /**
     * Returns whether a plugin exists at a given offset in the container.
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->plugins[$offset];
    }

    /**
     * Returns the plugin at a given offset.
     *
     * @param mixed $offset
     * @return \Phergie\Irc\Bot\React\Plugin\PluginInterface
     */
    public function offsetGet($offset)
    {
        return $this->plugins[$offset];
    }

    /**
     * Sets the plugin at a given offset.
     *
     * @param mixed $offset
     * @param \Phergie\Irc\Bot\React\Plugin\PluginInterface $value
     * @throws \InvalidArgumentException $value is not a plugin
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof PluginInterface) {
            throw new \InvalidArgumentException('$value is not an instance of PluginInterface');
        }
        $this->plugins[$offset] = $value;
    }

    /**
     * Removes the plugin at a given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->plugins[$offset]);
    }

    /**
     * Returns an iterator for a filtered subset of plugins in the container
     * that extend a given class, implement a given interface, or use a given
     * trait.
     *
     * @param string $entity Fully qualified class, interface, or trait name
     * @return \Iterator
     */
    public function getIteratorForEntity($entity)
    {
        return new \ArrayIterator(
            array_filter(
                $this->plugins,
                function($plugin) use ($entity) {
                    return $plugin instanceof $entity;
                }
            )
        );
    }
}

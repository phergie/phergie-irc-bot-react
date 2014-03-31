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
 * Specialized connection class to take property values from an array passed to
 * the constructor and add accessor methods for connection-specific plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class Connection extends \Phergie\Irc\Connection implements ConnectionInterface
{
    /**
     * List of plugins associated with the connection
     *
     * @var \Phergie\Irc\Bot\React\Plugin\PluginInterface[]
     */
    protected $plugins = array();

    /**
     * Constructor to accept property values.
     *
     * @param array $config Associative array keyed by property name
     */
    public function __construct(array $config = array())
    {
        foreach ($config as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($config[$key]);
            }
        }
    }

    /**
     * Sets a list of plugins to associate with the connection.
     *
     * @param \Phergie\Irc\Bot\React\Plugin\PluginInterface[] $plugins
     */
    public function setPlugins(array $plugins)
    {
        $this->plugins = $plugins;
    }

    /** 
     * Returns a list of plugins associated with the connection.
     *
     * @return \Phergie\Irc\Bot\React\Plugin\PluginInterface[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /*
     * Sets connection options.
     *
     * @param array $options Associative array of option-value pairs
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * Returns connection options.
     *
     * @return array Associative array of option-value pairs
     */
    public function getOptions()
    {
        return $this->options;
    }
}

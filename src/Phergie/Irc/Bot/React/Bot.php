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

use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Bot\React\ConnectionInterface as BotConnectionInterface;
use Phergie\Irc\Client\React\Client;
use Phergie\Irc\Event\CtcpEvent;
use Phergie\Irc\Event\ParserConverter;
use Phergie\Irc\Event\ParserConverterInterface;
use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Event\ServerEvent;
use Psr\Log\LoggerInterface;

/**
 * Class for an IRC bot that reads in configuration files, connects to IRC
 * servers, and configures plugins to receive events of interest from those
 * servers.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class Bot
{
    /**
     * IRC client in use by the bot
     *
     * @var \Phergie\Irc\Client\React\Client
     */
    protected $client;

    /**
     * Configuration in use by the bot
     *
     * @var array
     */
    protected $config = array();

    /**
     * Logger in use by the bot, defaults to logger in use by the IRC client
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Converter for event data from the IRC client's underlying parser
     *
     * @var \Phergie\Irc\Event\ParserConverter
     */
    protected $converter;

    /**
     * Sets the IRC client for the bot to use.
     *
     * @param \Phergie\Irc\Client\React\Client $client
     */
    public function setClient(Client $client)
    {
        $this->registerClientSubscribers($client);
        $this->client = $client;
    }

    /**
     * Returns the IRC client in use by the bot.
     *
     * @return \Phergie\Irc\Client\React\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->setClient(new Client);
        }
        return $this->client;
    }

    /**
     * Sets the configuration to be used by the bot in its operations.
     *
     * See config.sample.php for an example configuration file.
     *
     * @param array $config Associative array keyed by setting name
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the configuration in use by the bot.
     *
     * @return array Associative array keyed by setting name
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the logger in use by the bot.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the logger in use by the bot.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getClient()->getLogger();
        }
        return $this->logger;
    }

    /**
     * Sets the parser converter for event data in use by the bot.
     *
     * @param \Phergie\Irc\Event\ParserConverterInterface $converter
     */
    public function setConverter(ParserConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Returns the parser converter for event data in use by the bot.
     *
     * @return \Phergie\Irc\Event\ParserConverterInterface
     */
    public function getConverter()
    {
        if (!$this->converter) {
            $this->converter = new ParserConverter;
        }
        return $this->converter;
    }

    /**
     * Initiates an event loop for the bot in which it will connect to servers
     * and monitor those connections for events to forward to plugins.
     *
     * @throws \InvalidArgumentException if configuration is inconsistent with
     *         expected structure
     */
    public function run()
    {
        $client = $this->getClient();

        // Register global plugins
        $plugins = $this->getPlugins($this->config);
        $this->registerGlobalPluginSubscribers($client, $plugins);

        // Register connection-specific plugins
        $connections = $this->getConnections($this->config);
        foreach ($connections as $connection) {
            if ($connection instanceof BotConnectionInterface) {
                $this->registerConnectionPluginSubscribers($client, $connection);
            }
        }

        $client->run($connections);
    }

    /**
     * Extracts connections from configuration.
     *
     * @param array $config Associative array keyed by setting name
     * @return \Phergie\Irc\ConnectionInterface[]
     */
    protected function getConnections(array $config)
    {
        if (!isset($config['connections'])) {
            throw new \InvalidArgumentException('Configuration must contain a "connections" key');
        }

        if (!is_array($config['connections']) || !$config['connections']) {
            throw new \InvalidArgumentException('Configuration "connections" key must reference a non-empty array');
        }

        $connections = array_filter(
            $config['connections'],
            function($connection) {
                return $connection instanceof ConnectionInterface;
            }
        );
        if (count($connections) != count($config['connections'])) {
            throw new \InvalidArgumentException(
                'Configuration "connections" array must contain at least one value that implements \Phergie\Irc\ConnectionInterface'
            );
        }

        $filtered = array_filter(
            $connections,
            function($connection) {
                return $connection instanceof BotConnectionInterface;
            }
        );
        foreach ($filtered as $connection) {
            foreach ($connection->getPlugins() as $plugin) {
                $this->validatePluginEvents($plugin);
            }
        }

        return $connections;
    }

    /**
     * Extracts plugins from configuration.
     *
     * @param array $config Associative array keyed by setting name
     * @return \Phergie\Irc\Bot\React\Plugin\PluginInterface[]
     * @throws \InvalidArgumentException if any plugin event callback is invalid
     */
    protected function getPlugins(array $config)
    {
        if (!isset($config['plugins'])) {
            $this->getLogger()->notice('Configuration does not contain a "plugins" key');
            return;
        }

        if (!is_array($config['plugins']) || !$config['plugins']) {
            throw new \InvalidArgumentException('Configuration "plugins" key must reference a non-empty array');
        }

        $plugins = array_filter(
            $config['plugins'],
            function($plugin) {
                return $plugin instanceof PluginInterface;
            }
        );
        if (count($plugins) != count($config['plugins'])) {
            throw new \InvalidArgumentException(
                'All configuration "plugins" array values must implement \Phergie\Irc\Bot\React\PluginInterface'
            );
        }

        $client = $this->getClient();
        $logger = $this->getLogger();
        foreach ($plugins as $plugin) {
            $this->validatePluginEvents($plugin);
            $plugin->setEventEmitter($client);
            $plugin->setLogger($logger);
        }

        return $plugins;
    }

    /**
     * Validates a plugin's event callbacks.
     *
     * @param \Phergie\Irc\Bot\React\Plugin\PluginInterface $plugin
     * @throws \InvalidArgumentException if any event callback is invalid
     */
    protected function validatePluginEvents(PluginInterface $plugin)
    {
        $events = $plugin->getSubscribedEvents();
        foreach ($events as $event => $callback) {
            if (!is_callable($callback)) {
                throw new \InvalidArgumentException(
                    'Plugin with key "' . $key .
                        '" of class ' . get_class($plugin) .
                        ' returns invalid callback for event "' . $event . '"'
                );
            }
        }
    }

    /**
     * Configures the client to emit events for specific types of messages.
     *
     * @param \Phergie\Irc\Client\React\Client $client Client for which to
     *        configure events
     */
    protected function registerClientSubscribers(Client $client)
    {
        $converter = $this->getConverter();
        $callback = function($type, $message, $connection) use ($client, $converter) {
            $converted = $converter->convert($message);
            if ($converted instanceof CtcpEvent) {
                $event = 'ctcp.' . strtolower($converted->getCtcpCommand());
            } elseif ($converted instanceof UserEvent) {
                $event = strtolower($converted->getCommand());
            } elseif ($converted instanceof ServerEvent) {
                $event = strtolower($converted->getCode());
            }
            $converted->setConnection($connection);
            $client->emit('irc.' . $type, $converted);
            $client->emit('irc.' . $type . '.' . $event, $converted);
        };
        $client->on('irc.received', function($message, $write, $connection, $logger) use ($callback) {
            $callback('received', $message, $connection);
        });
        $client->on('irc.sent', function($message, $connection, $logger) use ($callback) {
            $callback('sent', $message, $connection);
        });
    }

    /**
     * Registers event callbacks from connection-specific plugins.
     *
     * @param \Phergie\Irc\Client\React\Client $client Client with which to
     *        register callbacks
     * @param \Phergie\Irc\Bot\React\PluginInterface[] $plugins Plugins from
     *        which to get callbacks
     */
    protected function registerGlobalPluginSubscribers(Client $client, array $plugins)
    {
        foreach ($plugins as $plugin) {
            $callbacks = $plugin->getSubscribedEvents();
            foreach ($callbacks as $event => $callback) {
                $client->on($event, $callback);
            }
        }
    }

    /**
     * Registers event callbacks from connection-specific plugins.
     *
     * @param \Phergie\Irc\Client\React\Client $client Client with which to
     *        register callbacks
     * @param \Phergie\Irc\Bot\React\ConnectionInterface $connection Connection where
     *        plugin callbacks will only receive events pertaining to that
     *        connection for events that are connection-specific
     */
    protected function registerConnectionPluginSubscribers(Client $client, BotConnectionInterface $connection)
    {
        // Define a callback wrapper used to limit callback invocations to
        // the specific connection
        $wrapper = function($callback) use ($connection) {
            return function() use ($callback, $connection) {
                $args = func_get_args();
                $connections = array_filter(
                        $args,
                        function($arg) {
                            return $arg instanceof ConnectionInterface;
                        }
                    );
                if ($connections && reset($connections) === $connection) {
                    return call_user_func_array($callback, $args);
                }
            };
        };

        // Register plugin callbacks with the client
        foreach ($connection->getPlugins() as $plugin) {
            $callbacks = $plugin->getSubscribedEvents();
            foreach ($callbacks as $event => $callback) {
                $client->on($event, $wrapper($callback));
            }
        }
    }
}

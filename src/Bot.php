<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Monolog\Logger;
use Phergie\Irc\Bot\React\PluginProcessor\ClientInjector;
use Phergie\Irc\Bot\React\PluginProcessor\EventEmitterInjector;
use Phergie\Irc\Bot\React\PluginProcessor\EventQueueFactoryInjector;
use Phergie\Irc\Bot\React\PluginProcessor\LoggerInjector;
use Phergie\Irc\Bot\React\PluginProcessor\LoopInjector;
use Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface;
use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Client\React\Client;
use Phergie\Irc\Client\React\ClientInterface;
use Phergie\Irc\Client\React\WriteStream;
use Phergie\Irc\Event\CtcpEvent;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Event\ParserConverter;
use Phergie\Irc\Event\ParserConverterInterface;
use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Event\ServerEvent;
use Phergie\Irc\Parser;
use Phergie\Irc\ParserInterface;
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
     * @var \Phergie\Irc\Client\React\ClientInterface
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
     * Parser for converting generated IRC commands into event objects
     *
     * @var \Phergie\Irc\ParserInterface
     */
    protected $parser;

    /**
     * Converter for event data from the IRC client's underlying parser
     *
     * @var \Phergie\Irc\Event\ParserConverterInterface
     */
    protected $converter;

    /**
     * Event queue factory for creating connection-specific event queues
     *
     * @var \Phergie\Irc\Bot\React\EventQueueFactoryInterface
     */
    protected $queueFactory;

    /**
     * Sets the IRC client for the bot to use.
     *
     * @param \Phergie\Irc\Client\React\ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->registerClientSubscribers($client);
        $this->client = $client;
    }

    /**
     * Returns the IRC client in use by the bot.
     *
     * @return \Phergie\Irc\Client\React\ClientInterface
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
     * Sets the parser for generated event data in use by the bot.
     *
     * @param \Phergie\Irc\ParserInterface $parser
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Returns the parser for generated event data in use by the bot.
     *
     * @return \Phergie\Irc\ParserInterface
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new Parser;
        }
        return $this->parser;
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
     * Sets the event queue factory for the bot to use.
     *
     * @param \Phergie\Irc\Bot\React\EventQueueFactoryInterface $queueFactory
     */
    public function setEventQueueFactory(EventQueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * Returns the event queue factory in use by the bot.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueFactoryInterface
     */
    public function getEventQueueFactory()
    {
        if (!$this->queueFactory) {
            $this->queueFactory = new EventQueueFactory;
        }
        return $this->queueFactory;
    }

    /**
     * Initiates an event loop for the bot in which it will connect to servers
     * and monitor those connections for events to forward to plugins.
     *
     * @throws \RuntimeException if configuration is inconsistent with
     *         expected structure
     */
    public function run()
    {
        $this->setDependencyOverrides($this->config);
        $this->getPlugins($this->config);
        $connections = $this->getConnections($this->config);
        $this->getClient()->run($connections);
    }

    /**
     * Sets dependencies from configuration.
     *
     * @param array $config
     */
    protected function setDependencyOverrides(array $config)
    {
        if (isset($config['client'])) {
            $this->setClient($config['client']);
        }

        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
        }

        if (isset($config['parser'])) {
            $this->setParser($config['parser']);
        }

        if (isset($config['converter'])) {
            $this->setConverter($config['converter']);
        }

        if (isset($config['eventQueueFactory'])) {
            $this->setEventQueueFactory($config['eventQueueFactory']);
        }
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
            throw new \RuntimeException('Configuration must contain a "connections" key');
        }

        if (!is_array($config['connections']) || !$config['connections']) {
            throw new \RuntimeException('Configuration "connections" key must reference a non-empty array');
        }

        $connections = array_filter(
            $config['connections'],
            function($connection) {
                return $connection instanceof ConnectionInterface;
            }
        );
        if (count($connections) != count($config['connections'])) {
            throw new \RuntimeException(
                'All configuration "connections" array values must implement \Phergie\Irc\ConnectionInterface'
            );
        }

        return $connections;
    }

    /**
     * Extracts plugins from configuration.
     *
     * @param array $config Associative array keyed by setting name
     * @return \Phergie\Irc\Bot\React\PluginInterface[]
     * @throws \RuntimeException if any plugin event callback is invalid
     */
    protected function getPlugins(array $config)
    {
        if (!isset($config['plugins'])) {
            throw new \RuntimeException('Configuration must contain a "plugins" key');
        }

        if (!is_array($config['plugins'])) {
            throw new \RuntimeException('Configuration "plugins" key must reference an array');
        }

        $plugins = array_filter(
            $config['plugins'],
            function($plugin) {
                return $plugin instanceof PluginInterface;
            }
        );
        if (count($plugins) != count($config['plugins'])) {
            throw new \RuntimeException(
                'All configuration "plugins" array values must implement \Phergie\Irc\Bot\React\PluginInterface'
            );
        }

        $this->registerPluginSubscribers($plugins);

        $processors = $this->getPluginProcessors($config);
        $this->processPlugins($plugins, $processors);

        return $plugins;
    }

    /**
     * Processes a list of plugins for use.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface[]
     * @param \Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface[]
     * @param \SplObjectStorage $processedPlugins
     */
    protected function processPlugins(array $plugins, array $processors, \SplObjectStorage $processedPlugins = null)
    {
        // Initialise store of already-processed plugins, to prevent container-based endless recursion
        if ($processedPlugins === null) {
            $processedPlugins = new \SplObjectStorage;
        }
        foreach ($plugins as $plugin) {
            if ($processedPlugins->contains($plugin)) {
                continue;
            }
            $processedPlugins->attach($plugin);
            foreach ($processors as $processor) {
                $processor->process($plugin, $this);
            }
            if ($plugin instanceof PluginContainerInterface) {
                $this->processPlugins($plugin->getPlugins(), $processors, $processedPlugins);
            }
        }
    }

    /**
     * Returns a list of processors for plugins.
     *
     * @param array $config Associative array keyed by setting name
     */
    protected function getPluginProcessors(array $config)
    {
        $processors = isset($config['pluginProcessors'])
            ? $config['pluginProcessors']
            : $this->getDefaultPluginProcessors();

        if (!is_array($processors)) {
            throw new \RuntimeException('Configuration "pluginProcessors" key must reference an array');
        }

        if (!empty($processors)) {
            $invalid = array_filter(
                $processors,
                function($processor) {
                    return !$processor instanceof PluginProcessorInterface;
                }
            );
            if (!empty($invalid)) {
                throw new \RuntimeException(
                    'All configuration "pluginProcessors" array values must implement'
                        . ' \Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface'
                );
            }
        }

        return $processors;
    }

    /**
     * Returns a list of default plugin processors used when none are set via
     * configuration.
     *
     * @param \Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface[]
     *
     * @return PluginProcessorInterface[]
     */
    protected function getDefaultPluginProcessors()
    {
        return array(
            new ClientInjector,
            new EventEmitterInjector,
            new EventQueueFactoryInjector,
            new LoggerInjector,
            new LoopInjector,
        );
    }

    /**
     * Validates a plugin's event callbacks.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface $plugin
     * @throws \RuntimeException if any event callback is invalid
     */
    protected function validatePluginEvents(PluginInterface $plugin)
    {
        $events = $plugin->getSubscribedEvents();
        if (!is_array($events)) {
            throw new \RuntimeException(
                'Plugin of class ' . get_class($plugin) .
                    ' has getSubscribedEvents() implementation' .
                    ' that does not return an array'
            );
        }
        foreach ($events as $event => $callback) {
            if (!is_string($event)
                || !is_callable(array($plugin, $callback))
                && !is_callable($callback)) {
                throw new \RuntimeException(
                    'Plugin of class ' . get_class($plugin) .
                        ' returns non-string event name or invalid callback' .
                        ' for event "' . $event . '"'
                );
            }
        }
    }

    /**
     * Configures the client to emit events for specific types of messages.
     *
     * @param \Phergie\Irc\Client\React\ClientInterface $client Client for
     *        which to configure events
     */
    protected function registerClientSubscribers(ClientInterface $client)
    {
        $bot = $this;

        $client->on('irc.received', function($message, $write, $connection) use ($bot) {
            $bot->processClientEvent('irc.received', $message, $connection, $write);
        });

        $parser = $this->getParser();
        $client->on('irc.sent', function($message, $write, $connection) use ($bot, $parser) {
            $parsed = $parser->parse($message);
            if (!$parsed) {
                return;
            }
            $bot->processClientEvent('irc.sent', $parsed, $connection, $write);
        });

        $client->on('irc.tick', function($write, $connection) use ($bot) {
            $bot->processOutgoingEvents($connection, $write);
        });
    }

    /**
     * Callback to process client events. Not intended to be called from
     * outside this class.
     *
     * @param string $event Received client event
     * @param array $message Parsed message
     * @param \Phergie\Irc\ConnectionInterface $connection Connection on which
     *        the event occurred
     * @param \Phergie\Irc\Client\React\WriteStream $write Stream used to send
     *        commands to the server
     */
    public function processClientEvent($event, array $message, ConnectionInterface $connection, WriteStream $write)
    {
        $converter = $this->getConverter();
        $converted = $converter->convert($message);
        $converted->setConnection($connection);

        $client = $this->getClient();
        $queue = $this->getEventQueueFactory()->getEventQueue($connection);
        $params = array($converted, $queue);
        $subtype = $this->getEventSubtype($converted);
        $client->emit($event . '.each', $params);
        $client->emit($event . '.' . $subtype, $params);
        
        $this->processOutgoingEvents($connection, $write);
    }

    /**
     * Callback to process any queued outgoing events. Not intended to be
     * called from outside thie class.
     *
     * @param \Phergie\Irc\ConnectionInterface $connection Connection on which
     *        the event occurred
     * @param \Phergie\Irc\Client\React\WriteStream $write Stream used to send
     *        commands to the server
     */
    public function processOutgoingEvents(ConnectionInterface $connection, WriteStream $write)
    {
        $client = $this->getClient();
        $queue = $this->getEventQueueFactory()->getEventQueue($connection);

        $client->emit('irc.sending.all', array($queue));
        while ($extracted = $queue->extract()) {
            $extracted->setConnection($connection);
            $params = array($extracted, $queue);
            $subtype = $this->getEventSubtype($extracted);
            $client->emit('irc.sending.each', $params);
            $client->emit('irc.sending.' . $subtype, $params);

            if ($extracted instanceof CtcpEvent) {
                $method = 'ctcp' . $extracted->getCtcpCommand();
                if ($extracted->getCommand() === 'NOTICE') {
                    $method .= 'Response';
                }
            } else {
                $method = 'irc' . $extracted->getCommand();
            }
            call_user_func_array(
                array($write, $method),
                $extracted->getParams()
            );
        }
    }

    /**
     * Returns an event subtype corresponding to a given event object, used to
     * generate event names when emitting events.
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @return string
     */
    protected function getEventSubtype(EventInterface $event)
    {
        $subevent = '';
        if ($event instanceof CtcpEvent) {
            $subevent = 'ctcp.' . strtolower($event->getCtcpCommand());
        } elseif ($event instanceof UserEvent) {
            $subevent = strtolower($event->getCommand());
        } elseif ($event instanceof ServerEvent) {
            $subevent = strtolower($event->getCode());
        }
        return $subevent;
    }

    /**
     * Registers event callbacks from plugins.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface[] $plugins Plugins from
     *        which to get callbacks
     */
    protected function registerPluginSubscribers(array $plugins)
    {
        $client = $this->getClient();
        foreach ($plugins as $plugin) {
            $this->validatePluginEvents($plugin);
            $callbacks = $plugin->getSubscribedEvents();
            foreach ($callbacks as $event => $callback) {
                $pluginCallback = array($plugin, $callback);
                if (is_callable($pluginCallback)) {
                    $callback = $pluginCallback;
                }
                $client->on($event, $callback);
            }
        }
    }
}

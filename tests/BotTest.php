<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Tests\Bot\React;

use Phake;
use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\Bot;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Bot\React\EventQueueFactory;
use Phergie\Irc\Bot\React\PluginContainerInterface;

/**
 * Tests for Bot class.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class BotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Bot\React\Bot
     */
    protected $bot;

    /**
     * Instantiates the class under test.
     */
    protected function setUp()
    {
        $this->bot = new Bot;
    }

    /*** UNIT TESTS ***/

    /**
     * Tests setConfig().
     */
    public function testSetConfig()
    {
        $config = array('foo' => 'bar');
        $this->bot->setConfig($config);
        $this->assertSame($config, $this->bot->getConfig());
    }

    /**
     * Tests getConfig().
     */
    public function testGetConfig()
    {
        $this->assertSame(array(), $this->bot->getConfig());
    }

    /**
     * Tests setLogger().
     */
    public function testSetLogger()
    {
        $logger = $this->getMockLogger();
        $this->bot->setLogger($logger);
        $this->assertSame($logger, $this->bot->getLogger());
    }

    /**
     * Tests getLogger().
     */
    public function testGetLogger()
    {
        $logger = $this->getMockLogger();
        $client = $this->getMockClient();
        Phake::when($client)->getLogger()->thenReturn($logger);
        $this->bot->setClient($client);
        $this->assertSame($logger, $this->bot->getLogger());
    }

    /**
     * Tests setClient().
     */
    public function testSetClient()
    {
        $client = $this->getMockClient();
        $this->bot->setClient($client);
        $this->assertSame($client, $this->bot->getClient());
    }

    /**
     * Tests getClient().
     */
    public function testGetClient()
    {
        $this->assertInstanceOf(
            '\Phergie\Irc\Client\React\Client',
            $this->bot->getClient()
        );
    }

    /**
     * Tests setParser().
     */
    public function testSetParser()
    {
        $client = $this->getMockParser();
        $this->bot->setParser($client);
        $this->assertSame($client, $this->bot->getParser());
    }

    /**
     * Tests getParser().
     */
    public function testGetParser()
    {
        $this->assertInstanceOf(
            '\Phergie\Irc\ParserInterface',
            $this->bot->getParser()
        );
    }

    /**
     * Tests setConverter().
     */
    public function testSetConverter()
    {
        $converter = $this->getMockConverter();
        $this->bot->setConverter($converter);
        $this->assertSame($converter, $this->bot->getConverter());
    }

    /**
     * Tests getConverter().
     */
    public function testGetConverter()
    {
        $this->assertInstanceOf(
            '\Phergie\Irc\Event\ParserConverterInterface',
            $this->bot->getConverter()
        );
    }

    /**
     * Tests setEventQueueFactory().
     */
    public function testSetEventQueueFactory()
    {
        $queue = $this->getMockEventQueueFactory();
        $this->bot->setEventQueueFactory($queue);
        $this->assertSame($queue, $this->bot->getEventQueueFactory());
    }

    /**
     * Tests getEventQueueFactory().
     */
    public function testGetEventQueueFactory()
    {
        $this->assertInstanceOf(
            '\Phergie\Irc\Bot\React\EventQueueFactoryInterface',
            $this->bot->getEventQueueFactory()
        );
    }

    /**
     * Data provider for testRunWithInvalidConfiguration().
     *
     * @return array
     */
    public function dataProviderRunWithInvalidConfiguration()
    {
        $data = array();

        // No "plugins" key
        $data[] = array(
            array(),
            'Configuration must contain a "plugins" key',
        );

        // Non-array "plugins" value
        $data[] = array(
            array('plugins' => 'foo'),
            'Configuration "plugins" key must reference an array',
        );

        // "plugins" value contains an object that doesn't implement PluginInterface
        $data[] = array(
            array('plugins' => array(new \stdClass)),
            'All configuration "plugins" array values must implement \Phergie\Irc\Bot\React\PluginInterface',
        );

        // "plugins" value contains a plugin with a getSubscribedEvents()
        // implementation that does not return an array
        $nonArrayPlugin = $this->getMockPlugin();
        Phake::when($nonArrayPlugin)->getSubscribedEvents()->thenReturn('foo');
        $data[] = array(
            array('plugins' => array($nonArrayPlugin)),
            'Plugin of class ' . get_class($nonArrayPlugin) .
                ' has getSubscribedEvents() implementation' .
                ' that does not return an array'
        );

        // "plugins" value contains a plugin with a getSubscribedEvents()
        // implementation that returns an array with a non-string key
        $badKeyPlugin = $this->getMockPlugin();
        Phake::when($badKeyPlugin)->getSubscribedEvents()->thenReturn(array(0 => function(){}));
        $data[] = array(
            array('plugins' => array($badKeyPlugin)),
            'Plugin of class ' . get_class($badKeyPlugin) .
                ' returns non-string event name or invalid callback' .
                ' for event "0"'
        );

        // "plugins" value contains a plugin with a getSubscribedEvents()
        // implementation that returns an array with a non-callable value
        $badValuePlugin = $this->getMockPlugin();
        Phake::when($badValuePlugin)->getSubscribedEvents()->thenReturn(array('foo' => 'foo'));
        $data[] = array(
            array('plugins' => array($badValuePlugin)),
            'Plugin of class ' . get_class($badValuePlugin) .
                ' returns non-string event name or invalid callback' .
                ' for event "foo"'
        );

        // No "connections" key
        $plugin = $this->getMockPlugin();
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => 'getSubscribedEvents'));
        $data[] = array(
            array('plugins' => array($plugin)),
            'Configuration must contain a "connections" key',
        );

        // Non-array "connections" value
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => 'foo'),
            'Configuration "connections" key must reference a non-empty array',
        );

        // Empty array "connections" value
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array()),
            'Configuration "connections" key must reference a non-empty array',
        );

        // "connections" value contains an object that doesn't implement ConnectionInterface
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array(new \stdClass)),
            'All configuration "connections" array values must implement \Phergie\Irc\ConnectionInterface',
        );

        // Non-array "pluginProcessors" value
        $connection = $this->getMockConnection();
        $plugin = $this->getMockPlugin();
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => function(){}));
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array($connection), 'pluginProcessors' => 'foo'),
            'Configuration "pluginProcessors" key must reference an array'
        );

        // "pluginProcessors" value contains an object that doesn't implement PluginProcessorInterface
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array($connection), 'pluginProcessors' => array(new \stdClass)),
            'All configuration "pluginProcessors" array values must implement'
                . ' \Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface'
        );

        return $data;
    }

    /**
     * Tests run() with invalid configuration.
     *
     * @param array $config Invalid configuration
     * @param string $message Expected exception message
     * @dataProvider dataProviderRunWithInvalidConfiguration
     */
    public function testRunWithInvalidConfiguration(array $config, $message)
    {
        $this->bot->setConfig($config);
        $this->bot->setLogger($this->getMockLogger());
        try {
            $this->bot->run();
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }

    /**
     * Tests overriding plugin processors via configuration.
     */
    public function testOverridePluginProcessors()
    {
        $plugin = $this->getMockPlugin();
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array());
        $connection = $this->getMockConnection();
        $connections = array($connection);
        $client = $this->getMockClient();
        Phake::when($client)->run($connections)->thenReturn(null);
        $processor = Phake::mock('\Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface');

        $config = array(
            'plugins' => array($plugin),
            'connections' => $connections,
            'pluginProcessors' => array($processor),
        );

        $this->bot->setClient($client);
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($processor)->process($plugin, $this->bot);
    }

    /**
     * Tests plugin processors being passed through plugin containers.
     */
    public function testProcessPluginContainers()
    {
        $plugin = $this->getMockPlugin();
        $container = new TestPluginContainer([$plugin]);
        $processor = Phake::mock('\Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface');

        $config = array(
            'connections' => array($this->getMockConnection()),
            'plugins' => array($container),
            'pluginProcessors' => array($processor),
        );

        $this->bot->setClient($this->getMockClient());
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($processor)->process($plugin, $this->bot);
    }

    /**
     * Tests recursion for nested plugins in plugin containers.
     */
    public function testProcessPluginsWithRecursion()
    {
        $singlePlugin = $this->getMockPlugin();
        $repeatedPlugin = $this->getMockPlugin();
        $container = new TestPluginContainer([$singlePlugin, $repeatedPlugin]);
        $recursiveContainer = new TestPluginContainer([$container, $repeatedPlugin]);
        $processor = Phake::mock('\Phergie\Irc\Bot\React\PluginProcessor\PluginProcessorInterface');

        $config = array(
            'connections' => array($this->getMockConnection()),
            'plugins' => array($recursiveContainer),
            'pluginProcessors' => array($processor),
        );

        $this->bot->setClient($this->getMockClient());
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($processor, Phake::times(1))->process($singlePlugin, $this->bot);
        Phake::verify($processor, Phake::times(1))->process($repeatedPlugin, $this->bot);
    }

    /**
     * Tests disabling plugin processors via configuration.
     */
    public function testDisablePluginProcessors()
    {
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => 'setLogger'));

        $connection = $this->getMockConnection();

        $logger = $this->getMockLogger();
        $client = $this->getMockClient();
        Phake::when($client)->getLogger()->thenReturn($logger);

        $config = array(
            'plugins' => array($plugin),
            'connections' => array($connection),
            'pluginProcessors' => array(),
        );

        $this->bot->setClient($client);
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($plugin, Phake::never())->setEventEmitter($client);
        Phake::verify($plugin, Phake::never())->setLogger($logger);
    }

    /**
     * Tests that plugins can subscribe to events with callables.
     */
    public function testPluginsSubscribeWithCallables()
    {
        $connection = $this->getMockConnection();
        $plugin = $this->getMockPlugin();
        $called = false;
        $callback = function() use (&$called) { $called = true; };
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => $callback));

        $config = array(
            'plugins' => array($plugin),
            'connections' => array($connection),
        );

        $this->bot->setClient($this->getMockClient());
        $this->bot->setConfig($config);
        $this->bot->run();
    }

    /*** INTEGRATION TESTS ***/

    /**
     * Tests run() with a subclass of AbstractPlugin included in the plugins
     * list to verify that default plugin processors work.
     */
    public function testRunWithDefaultPluginProcessors()
    {
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => 'setLogger'));

        $connection = $this->getMockConnection();

        $logger = $this->getMockLogger();
        $client = $this->getMockClient();
        $factory = $this->getMockEventQueueFactory();
        $loop = Phake::mock('\React\EventLoop\LoopInterface');
        Phake::when($client)->getLogger()->thenReturn($logger);
        Phake::when($client)->getLoop()->thenReturn($loop);

        $config = array(
            'plugins' => array($plugin),
            'connections' => array($connection),
        );

        $this->bot->setClient($client);
        $this->bot->setConfig($config);
        $this->bot->setLogger($logger);
        $this->bot->setEventQueueFactory($factory);
        $this->bot->run();

        Phake::verify($plugin)->setEventEmitter($client);
        Phake::verify($plugin)->setClient($client);
        Phake::verify($plugin)->setEventQueueFactory($factory);
        Phake::verify($plugin)->setLogger($logger);
        Phake::verify($plugin)->setLoop($loop);
    }

    /**
     * Data provider for testEventCallbacks().
     *
     * @return array
     */
    public function dataProviderEventCallbacks()
    {
        $data = array();

        foreach (array('received', 'sent') as $eventType) {
            $eventObject = Phake::mock('\Phergie\Irc\Event\CtcpEvent');
            Phake::when($eventObject)->getCtcpCommand()->thenReturn('ACTION');
            $data[] = array($eventObject, $eventType, 'ctcp.action');

            $eventObject = Phake::mock('\Phergie\Irc\Event\UserEvent');
            Phake::when($eventObject)->getCommand()->thenReturn('PRIVMSG');
            $data[] = array($eventObject, $eventType, 'privmsg');
        }

        $eventObject = Phake::mock('\Phergie\Irc\Event\ServerEvent');
        Phake::when($eventObject)->getCode()->thenReturn('ERR_NOSUCHNICK');
        $data[] = array($eventObject, 'received', 'err_nosuchnick');

        return $data;
    }

    /**
     * Tests listeners set up by supporting methods when the client receives a
     * an event.
     *
     * @param \Phergie\Irc\Event\EventInterface $eventObject
     * @param string $eventType
     * @param string $eventSubtype
     * @dataProvider dataProviderEventCallbacks
     */
    public function testEventCallbacks(EventInterface $eventObject, $eventType, $eventSubtype)
    {
        $params = array();
        $message = $params[] = array('foo' => 'bar');

        $converter = $this->getMockConverter();
        Phake::when($converter)->convert($message)->thenReturn($eventObject);
        $this->bot->setConverter($converter);

        $parser = $this->getMockParser();
        Phake::when($parser)->parse($message)->thenReturn($message);
        $this->bot->setParser($parser);

        $client = new \Phergie\Irc\Client\React\Client;
        $this->bot->setClient($client);

        $write = $params[] = $this->getMockWriteStream();
        $connection = $params[] = $this->getMockConnection();
        $logger = $params[] = $this->getMockLogger();

        $queue = $this->getMockEventQueue();
        $queueFactory = $this->getMockEventQueueFactory();
        Phake::when($queueFactory)->getEventQueue($connection)->thenReturn($queue);
        $this->bot->setEventQueueFactory($queueFactory);

        $test = $this;
        $allCalled = false;
        $typeCalled = false;
        $client->on('irc.' . $eventType . '.each', function($param, $otherQueue) use (&$allCalled, $test, $eventObject, $queue) {
            $allCalled = true;
            $test->assertSame($eventObject, $param);
            $test->assertSame($otherQueue, $queue);
        });
        $client->on('irc.' . $eventType . '.' . $eventSubtype, function($param, $otherQueue) use (&$typeCalled, $test, $eventObject, $queue) {
            $typeCalled = true;
            $test->assertSame($eventObject, $param);
            $test->assertSame($otherQueue, $queue);
        });

        $client->emit('irc.' . $eventType, $params);

        $this->assertTrue($allCalled);
        $this->assertTrue($typeCalled);
        Phake::verify($eventObject)->setConnection($connection);
    }

    /**
     * Tests the irc.tick event.
     */
    public function testTickEvent()
    {
        $eventObject = Phake::mock('\Phergie\Irc\Event\UserEvent');
        $eventParams = array('receivers' => '#channel', 'text' => 'message');
        Phake::when($eventObject)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($eventObject)->getParams()->thenReturn($eventParams);

        $queue = $this->getMockEventQueue();
        Phake::when($queue)->extract()->thenReturn($eventObject)->thenReturn(false);

        $client = new \Phergie\Irc\Client\React\Client;
        $this->bot->setClient($client);

        $test = $this;

        $allCalled = false;
        $client->on('irc.sending.all', function($otherQueue) use (&$allCalled, $test, $queue) {
            $allCalled = true;
            $test->assertSame($otherQueue, $queue);
        });

        $eachCalled = false;
        $client->on(
            'irc.sending.each',
            function($otherEvent, $otherQueue)
                use (&$eachCalled, $test, $eventObject, $queue) {
                $eachCalled = true;
                $test->assertSame($otherEvent, $eventObject);
                $test->assertSame($otherQueue, $queue);
            }
        );

        $typeCalled = false;
        $client->on(
            'irc.sending.privmsg',
            function($otherEvent, $otherQueue)
                use (&$typeCalled, $test, $eventObject, $queue) {
                $typeCalled = true;
                $test->assertSame($otherEvent, $eventObject);
                $test->assertSame($otherQueue, $queue);
            }
        );

        $write = $params[] = $this->getMockWriteStream();
        $connection = $params[] = $this->getMockConnection();

        $queueFactory = $this->getMockEventQueueFactory();
        Phake::when($queueFactory)->getEventQueue($connection)->thenReturn($queue);
        $this->bot->setEventQueueFactory($queueFactory);

        $client->emit('irc.tick', $params);

        Phake::verify($eventObject)->setConnection($connection);
        call_user_func_array(array(Phake::verify($write), 'ircPrivmsg'), $eventParams);
        $this->assertTrue($allCalled);
        $this->assertTrue($eachCalled);
        $this->assertTrue($typeCalled);
    }

    /**
     * Data provider for testPluginEmittedEvents().
     *
     * @return array
     */
    public function dataProviderPluginEmittedEvents()
    {
        return array(
            array('notice', '\Phergie\Irc\Event\UserEvent', 'ircNotice'),
            array('ctcp.action', '\Phergie\Irc\Event\CtcpEvent', 'ctcpAction'),
            array('ctcp.action', '\Phergie\Irc\Event\CtcpEvent', 'ctcpActionResponse'),
        );
    }

    /**
     * Tests that plugins can emit events.
     *
     * @param string $event Name of the plugin-emitted event
     * @param string $class Class of the emitted event
     * @param string $method Method invoked to queue the event
     * @param array $params Parameters passed to the method invoked to queue
     *        the event
     * @dataProvider dataProviderPluginEmittedEvents
     */
    public function testPluginEmittedEvents($event, $class, $method)
    {
        $message = array('foo' => 'bar');
        $write = $this->getMockWriteStream();
        $logger = $this->getMockLogger();

        $connection = $this->getMockConnection();
        $connections = array($connection);

        $queue = new EventQueue;
        $queueFactory = $this->getMockEventQueueFactory();
        Phake::when($queueFactory)->getEventQueue($connection)->thenReturn($queue);
        $this->bot->setEventQueueFactory($queueFactory);

        $eventObject = Phake::mock('\Phergie\Irc\Event\UserEvent');
        $eventParams = array('#channel', 'message');
        Phake::when($eventObject)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($eventObject)->getParams()->thenReturn($eventParams);

        $converter = $this->getMockConverter();
        Phake::when($converter)->convert($message)->thenReturn($eventObject);
        $this->bot->setConverter($converter);

        $plugin = $this->getMockTestPlugin();
        Phake::when($plugin)
            ->getSubscribedEvents()
            ->thenReturn(array('irc.received.privmsg' => 'handleEvent'));
        $callback = function($eventObject, $queue) use ($method, $eventParams) {
            call_user_func_array(array($queue, $method), $eventParams);
        };
        Phake::when($plugin)
            ->handleEvent($eventObject, $queue)
            ->thenReturnCallback($callback);

        $config = array(
            'plugins' => array($plugin),
            'connections' => $connections,
        );
        $this->bot->setConfig($config);

        $client = Phake::partialMock('\Phergie\Irc\Client\React\Client');
        Phake::when($client)->run($connections)->thenReturn(null);
        $this->bot->setClient($client);

        $this->bot->run();

        $client->emit('irc.received', array($message, $write, $connection, $logger));

        Phake::verify($client)->emit('irc.sending.all', Phake::capture($allParams));
        $this->assertSame($queue, $allParams[0]);

        Phake::verify($client)->emit('irc.sending.each', Phake::capture($eachParams));
        $this->assertInstanceOf($class, $eachParams[0]);
        $this->assertSame($queue, $eachParams[1]);

        Phake::verify($client)->emit('irc.sending.' . $event, $eachParams);

        call_user_func_array(array(Phake::verify($write), $method), $eventParams);
    }

    /**
     * Tests that dependencies can be overridden via configuration.
     */
    public function testOverrideDependencies()
    {
        $client = $this->getMockClient();
        $logger = $this->getMockLogger();
        $parser = $this->getMockParser();
        $converter = $this->getMockConverter();
        $eventQueueFactory = $this->getMockEventQueueFactory();

        $config = array(
            'client' => $client,
            'logger' => $logger,
            'parser' => $parser,
            'converter' => $converter,
            'eventQueueFactory' => $eventQueueFactory,
            'plugins' => array(),
            'connections' => array($this->getMockConnection()),
        );

        $this->bot->setConfig($config);
        $this->bot->run();

        $this->assertSame($client, $this->bot->getClient());
        $this->assertSame($logger, $this->bot->getLogger());
        $this->assertSame($parser, $this->bot->getParser());
        $this->assertSame($converter, $this->bot->getConverter());
        $this->assertSame($eventQueueFactory, $this->bot->getEventQueueFactory());
    }

    /*** SUPPORTING METHODS ***/

    /**
     * Returns a mock logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function getMockLogger()
    {
        return Phake::mock('\Psr\Log\LoggerInterface');
    }

    /**
     * Returns a mock IRC client.
     *
     * @return \Phergie\Irc\Client\React\Client
     */
    protected function getMockClient()
    {
        return Phake::mock('\Phergie\Irc\Client\React\Client');
    }

    /**
     * Returns a mock parser for generated event data.
     *
     * @return \Phergie\Irc\ParserInterface
     */
    protected function getMockParser()
    {
        return Phake::mock('\Phergie\Irc\ParserInterface');
    }

    /**
     * Returns a mock converter for event data from the client's IRC parser.
     *
     * @return \Phergie\Irc\Event\ParserConverter
     */
    protected function getMockConverter()
    {
        return Phake::mock('\Phergie\Irc\Event\ParserConverter');
    }

    /**
     * Returns a mock event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected function getMockEventQueue()
    {
        return Phake::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
    }

    /**
     * Returns a mock event queue factory.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueFactoryInterface
     */
    protected function getMockEventQueueFactory()
    {
        return Phake::mock('\Phergie\Irc\Bot\React\EventQueueFactoryInterface');
    }

    /**
     * Returns a mock plugin.
     *
     * @return \Phergie\Irc\Bot\React\PluginInterface
     */
    protected function getMockPlugin()
    {
        return Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
    }

    /**
     * Returns a mock plugin with a valid callback for stubbing.
     *
     * @return \Phergie\Irc\Bot\React\TestPlugin
     */
    protected function getMockTestPlugin()
    {
        return Phake::mock('\Phergie\Irc\Tests\Bot\React\TestPlugin');
    }

    /**
     * Returns a specialized mock connection.
     *
     * @return \Phergie\Irc\ConnectionInterface
     */
    protected function getMockConnection()
    {
        return Phake::mock('\Phergie\Irc\ConnectionInterface');
    }

    /**
     * Returns a mock stream for sending events to the server.
     *
     * @return \Phergie\Irc\Client\React\WriteStream
     */
    protected function getMockWriteStream()
    {
        return Phake::mock('\Phergie\Irc\Client\React\WriteStream');
    }
}

/**
 * Plugin class with a valid event callback used for testing
 * connection-specific plugins.
 */
class TestPlugin extends AbstractPlugin
{
    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function getSubscribedEvents()
    {
        return array(
            $this->event => 'handleEvent',
        );
    }

    public function handleEvent()
    {
        // left empty for stubbing
    }
}

/**
 * Plugin class which implements PluginContainerInterface.
 */
class TestPluginContainer extends AbstractPlugin implements PluginContainerInterface
{
    protected $plugins = [];

    public function __construct(array $plugins) {
        $this->plugins = $plugins;
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function getSubscribedEvents()
    {
        return [];
    }
}

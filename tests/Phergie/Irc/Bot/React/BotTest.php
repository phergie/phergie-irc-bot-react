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

use Phake;
use Phergie\Irc\Event\EventInterface;
use React\EventLoop\LoopInterface;

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
     * Tests setEventQueue().
     */
    public function testSetEventQueue()
    {
        $queue = $this->getMockEventQueue();
        $this->bot->setEventQueue($queue);
        $this->assertSame($queue, $this->bot->getEventQueue());
    }

    /**
     * Tests getEventQueue().
     */
    public function testGetEventQueue()
    {
        $this->assertInstanceOf(
            '\Phergie\Irc\Bot\React\EventQueueInterface',
            $this->bot->getEventQueue()
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

        // "connections" value contains a connection with a plugin with a
        // getSubscribedEvents() implementation that does not return an array
        $connection = $this->getMockConnection();
        Phake::when($connection)->getPlugins()->thenReturn(array($nonArrayPlugin));
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array($connection)),
            'Plugin of class ' . get_class($nonArrayPlugin) .
                ' has getSubscribedEvents() implementation' .
                ' that does not return an array'
        );

        // "connections" value contains a connection with a plugin with a
        // getSubscribedEvents() implementation that returns an array with a
        // non-string key
        $connection = $this->getMockConnection();
        Phake::when($connection)->getPlugins()->thenReturn(array($badKeyPlugin));
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array($connection)),
            'Plugin of class ' . get_class($badKeyPlugin) .
                ' returns non-string event name or invalid callback' .
                ' for event "0"'
        );

        // "connections" value contains a connection with a plugin with a
        // getSubscribedEvents() implementation that returns an array with a
        // non-callable value
        $connection = $this->getMockConnection();
        Phake::when($connection)->getPlugins()->thenReturn(array($badValuePlugin));
        $data[] = array(
            array('plugins' => array($plugin), 'connections' => array($connection)),
            'Plugin of class ' . get_class($badValuePlugin) .
                ' returns non-string event name or invalid callback' .
                ' for event "foo"'
        );

        // Non-array "pluginProcessors" value
        $connection = $this->getMockConnection();
        Phake::when($connection)->getPlugins()->thenReturn(array());
        $plugin = $this->getMockPlugin();
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => 'setLogger'));
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
     * Tests disabling plugin processors via configuration.
     */
    public function testDisablePluginProcessors()
    {
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => 'setLogger'));

        $connection = $this->getMockConnection();

        $logger = $this->getMockLogger();
        $client = $this->getMockClient();
        $loop = $this->getMockLoop();
        Phake::when($client)->getLogger()->thenReturn($logger);
        Phake::when($client)->getLoop()->thenReturn($loop);

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
        Phake::verify($plugin, Phake::never())->setLoop($loop);
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
        $loop = $this->getMockLoop();
        Phake::when($client)->getLogger()->thenReturn($logger);
        Phake::when($client)->getLoop()->thenReturn($loop);

        $config = array(
            'plugins' => array($plugin),
            'connections' => array($connection),
        );

        $this->bot->setClient($client);
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($plugin)->setEventEmitter($client);
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

        $queue = $this->getMockEventQueue();
        $this->bot->setEventQueue($queue);

        $client = new \Phergie\Irc\Client\React\Client;
        $this->bot->setClient($client);

        $write = $params[] = $this->getMockWriteStream();
        $connection = $params[] = $this->getMockConnection();
        $logger = $params[] = $this->getMockLogger();

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
        $this->bot->setEventQueue($queue);

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
        $client->emit('irc.tick', $params);

        Phake::verify($eventObject)->setConnection($connection);
        call_user_func_array(array(Phake::verify($write), 'ircPrivmsg'), $eventParams);
        $this->assertTrue($allCalled);
        $this->assertTrue($eachCalled);
        $this->assertTrue($typeCalled);
    }

    /**
     * Tests that listeners for connection-specific plugins are only called for
     * those connections.
     */
    public function testConnectionSpecificPlugins()
    {
        $event = 'irc.received.privmsg';
        $write = $this->getMockWriteStream();
        $logger = $this->getMockLogger();
        $message = array('foo' => 'bar');

        $queue = $this->getMockEventQueue();
        $this->bot->setEventQueue($queue);

        $eventObject = Phake::mock('\Phergie\Irc\Event\UserEvent');
        Phake::when($eventObject)->getCommand()->thenReturn('PRIVMSG');

        $converter = $this->getMockConverter();
        Phake::when($converter)->convert($message)->thenReturn($eventObject);
        $this->bot->setConverter($converter);

        $connectionCalled = array();
        $connectionCallback = array();
        $connectionPlugin = array();
        $connections = array();
        foreach (range(1, 2) as $index) {
            $connectionCalled[$index] = null;
            $connections[$index] = $this->getMockConnection();
            $connectionCallback[$index] = function() use (&$connectionCalled, $index) { $connectionCalled[$index] = true; };
            $connectionPlugin[$index] = $this->getMockTestPlugin();
            Phake::when($connectionPlugin[$index])
                ->handleEvent($eventObject, $queue)
                ->thenGetReturnByLambda($connectionCallback[$index]);
            Phake::when($connectionPlugin[$index])
                ->getSubscribedEvents()
                ->thenReturn(array($event => 'handleEvent', 'custom' => 'handleEvent'));
            Phake::when($connections[$index])->getPlugins()->thenReturn(array($connectionPlugin[$index]));
        }
        Phake::when($connectionPlugin[2])
            ->handleEvent($connections[2])
            ->thenGetReturnByLambda($connectionCallback[2]);

        $globalCalled = null;
        $globalCallback = function() use (&$globalCalled) { $globalCalled = true; };
        $globalPlugin = $this->getMockTestPlugin();
        Phake::when($globalPlugin)
            ->handleEvent($eventObject, $queue)
            ->thenGetReturnByLambda($globalCallback);
        Phake::when($globalPlugin)
            ->handleEvent($connections[2])
            ->thenGetReturnByLambda($globalCallback);
        Phake::when($globalPlugin)
            ->getSubscribedEvents()
            ->thenReturn(array($event => 'handleEvent', 'custom' => 'handleEvent'));

        $config = array(
            'plugins' => array($globalPlugin),
            'connections' => $connections,
        );
        $this->bot->setConfig($config);

        $client = Phake::partialMock('\Phergie\Irc\Client\React\Client');
        Phake::when($client)->run($connections)->thenReturn(null);
        $this->bot->setClient($client);

        $this->bot->run();

        Phake::inOrder(
            Phake::verify($client)->emit('plugin.each', array($globalPlugin)),
            Phake::verify($client)->emit('plugin.global', array($globalPlugin)),
            Phake::verify($client)->emit('plugin.each', array($connectionPlugin[1])),
            Phake::verify($client)->emit('plugin.connection', array($connectionPlugin[1], $connections[1])),
            Phake::verify($client)->emit('plugin.each', array($connectionPlugin[2])),
            Phake::verify($client)->emit('plugin.connection', array($connectionPlugin[2], $connections[2])),
            Phake::verify($client)->emit('plugin.all', array(array($globalPlugin), $connections))
        );

        $globalCalled = $connectionCalled[1] = $connectionCalled[2] = false;
        Phake::when($eventObject)->getConnection()->thenReturn($connections[1]);
        $client->emit('irc.received', array($message, $write, $connections[1], $logger));
        $this->assertTrue($globalCalled, 'Global callback was not called');
        $this->assertTrue($connectionCalled[1], 'Connection #1 callback was not called');
        $this->assertFalse($connectionCalled[2], 'Connection #2 callback was called');

        $globalCalled = $connectionCalled[1] = $connectionCalled[2] = false;
        Phake::when($eventObject)->getConnection()->thenReturn($connections[2]);
        $client->emit('irc.received', array($message, $write, $connections[2], $logger));
        $this->assertTrue($globalCalled, 'Global callback was not called');
        $this->assertFalse($connectionCalled[1], 'Connection #1 callback was called');
        $this->assertTrue($connectionCalled[2], 'Connection #2 callback was not called');

        $globalCalled = $connectionCalled[1] = $connectionCalled[2] = false;
        $client->emit('custom', array($connections[2]));
        $this->assertTrue($globalCalled, 'Global callback was not called');
        $this->assertFalse($connectionCalled[1], 'Connection #1 callback was called');
        $this->assertTrue($connectionCalled[2], 'Connection #2 callback was not called');
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
        $this->bot->setEventQueue($queue);

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
            ->thenGetReturnByLambda($callback);

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
        $eventQueue = $this->getMockEventQueue();

        $config = array(
            'client' => $client,
            'logger' => $logger,
            'parser' => $parser,
            'converter' => $converter,
            'eventQueue' => $eventQueue,
            'plugins' => array(),
            'connections' => array($this->getMockConnection()),
        );

        $this->bot->setConfig($config);
        $this->bot->run();

        $this->assertSame($client, $this->bot->getClient());
        $this->assertSame($logger, $this->bot->getLogger());
        $this->assertSame($parser, $this->bot->getParser());
        $this->assertSame($converter, $this->bot->getConverter());
        $this->assertSame($eventQueue, $this->bot->getEventQueue());
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
        return Phake::mock('\Phergie\Irc\Bot\React\TestPlugin');
    }

    /**
     * Returns a specialized mock connection.
     *
     * @return \Phergie\Irc\Bot\React\Connection
     */
    protected function getMockConnection()
    {
        $connection = Phake::mock('\Phergie\Irc\Bot\React\Connection');
        Phake::when($connection)->getPlugins()->thenReturn(array());
        return $connection;
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

    /**
     * Returns a mock event loop.
     *
     * @return \React\EventLoop\LoopInterface
     */
    protected function getMockLoop()
    {
        return Phake::mock('\React\EventLoop\LoopInterface');
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
            'custom' => 'handleEvent',
        );
    }

    public function handleEvent()
    {
        // left empty for stubbing
    }
}

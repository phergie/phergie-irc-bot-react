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
            'Configuration "plugins" key must reference a non-empty array',
        );

        // Empty array "plugins" value
        $data[] = array(
            array('plugins' => array()),
            'Configuration "plugins" key must reference a non-empty array',
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
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => function(){}));
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
     * Tests run() with a subclass of AbstractPlugin included in the plugins
     * list to verify that its event emitter and logger dependencies are
     * properly set.
     */
    public function testRunWithAbstractPlugin()
    {
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        Phake::when($plugin)->getSubscribedEvents()->thenReturn(array('foo' => function(){}));

        $connection = $this->getMockConnection();
        Phake::when($connection)->getPlugins()->thenReturn(array());

        $logger = $this->getMockLogger();
        $client = $this->getMockClient();
        Phake::when($client)->getLogger()->thenReturn($logger);

        $config = array('plugins' => array($plugin), 'connections' => array($connection));

        $this->bot->setClient($client);
        $this->bot->setConfig($config);
        $this->bot->run();

        Phake::verify($plugin)->setEventEmitter($client);
        Phake::verify($plugin)->setLogger($logger);
    }

    /*** INTEGRATION TESTS ***/

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
        $message = array('foo' => 'bar');
        $converter = $this->getMockConverter();
        Phake::when($converter)->convert($message)->thenReturn($eventObject);
        $this->bot->setConverter($converter);

        $client = Phake::partialMock('\Phergie\Irc\Client\React\Client');
        Phake::when($client)->run()->thenReturn(null);
        $this->bot->setClient($client);

        $connection = $this->getMockConnection();
        $logger = $this->getMockLogger();
        $write = Phake::mock('\Phergie\Irc\Client\React\WriteStream');

        $test = $this;
        $allCalled = false;
        $typeCalled = false;
        $client->on('irc.' . $eventType . '.all', function($param) use (&$allCalled, $test, $eventObject) {
            $allCalled = true;
            $test->assertSame($eventObject, $param);
        });
        $client->on('irc.' . $eventType . '.' . $eventSubtype, function($param) use (&$typeCalled, $test, $eventObject) {
            $typeCalled = true;
            $test->assertSame($eventObject, $param);
        });

        $client->emit(
            'irc.' . $eventType,
            array(
                $message,
                $write,
                $connection,
                $logger
            )
        );

        $this->assertTrue($allCalled);
        $this->assertTrue($typeCalled);
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
     * Returns a mock converter for event data from the client's IRC parser.
     *
     * @return \Phergie\Irc\Event\ParserConverter
     */
    protected function getMockConverter()
    {
        return Phake::mock('\Phergie\Irc\Event\ParserConverter');
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
     * Returns a specialized mock connection.
     *
     * @return \Phergie\Irc\Bot\React\Connection
     */
    protected function getMockConnection()
    {
        return Phake::mock('\Phergie\Irc\Bot\React\Connection');
    }
}

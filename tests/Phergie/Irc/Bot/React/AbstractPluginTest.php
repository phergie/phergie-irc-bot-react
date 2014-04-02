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

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use Phake;

/**
 * Tests for AbstractPlugin.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class AbstractPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Bot\React\AbstractPlugin
     */
    protected $plugin;

    /**
     * Instantiates the class under test.
     */
    protected function setUp()
    {
        $this->plugin = $this->getMockForAbstractClass('\Phergie\Irc\Bot\React\AbstractPlugin');
    }

    /**
     * Tests setEventEmitter().
     */
    public function testSetEventEmitter()
    {
        $emitter = Phake::mock('\Evenement\EventEmitterInterface');
        $this->plugin->setEventEmitter($emitter);
        $this->assertSame($emitter, $this->plugin->getEventEmitter());
    }

    /**
     * Tests getEventEmitter().
     */
    public function testGetEventEmitter()
    {
        $emitter = $this->plugin->getEventEmitter();
        $this->assertNull($emitter);
    }

    /**
     * Tests setLogger().
     */
    public function testSetLogger()
    {
        $logger = Phake::mock('\Psr\Log\LoggerInterface');
        $this->plugin->setLogger($logger);
        $this->assertSame($logger, $this->plugin->getLogger());
    }

    /**
     * Tests getLogger().
     */
    public function testGetLogger()
    {
        $logger = $this->plugin->getLogger();
        $this->assertNull($logger);
    }

    /**
     * Tests that the class under test implements PluginInterface.
     */
    public function testImplementsPluginInterface()
    {
        $class = new \ReflectionClass(get_class($this->plugin));
        $this->assertArrayHasKey('Phergie\Irc\Bot\React\PluginInterface', $class->getInterfaces());
    }
}

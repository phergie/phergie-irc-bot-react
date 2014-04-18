# phergie/phergie-irc-bot-react

A PHP-based IRC bot built on React.

[![Build Status](https://secure.travis-ci.org/phergie/phergie-irc-bot-react.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-bot-react)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "minimum-stability": "dev",
    "require": {
        "phergie/phergie-irc-bot-react": "dev-master"
    }
}
```

## Design goals

* Easy installation with minimal configuration
* Low-friction plugin development
* Informative logging of events for debugging
* Use of third-party libraries where feasible
* Simple easy-to-understand API

## Usage

To run from a project using this repository via composer, which is the
recommended approach for end-users and plugin developers:

```
./vendor/bin/phergie
```

To run from a clone of this repository, which is only recommended for core
developers:

```
./bin/phergie
```

In both cases, a configuration file path can be specified as a command line
argument.  If none is, `config.php` in the current working directory will be
assumed by default.

## Configuration

Configuration is stored in a PHP file that returns an associative array. This
file contains settings for IRC server connections and plugins. See
`config.sample.php` for an example.

## Installing Plugins

The bot provides enough functionality to connect to an IRC server and listen
for events, but what really makes it useful to end-users is functionality
provided through plugins.

Like the bot itself, plugins are installed via composer. Add any plugins you
want to install to the `require` section of your `composer.json` file and run 
`composer install` or `composer update` as appropriate.

In order for the bot to actually use an installed plugin, it must be enabled
and configured in the bot's configuration file. See `config.sample.php` for an
example.

To get an idea of what plugins are available, check out the [Plugins
page](https://github.com/phergie/phergie-irc-bot-react/wiki/Plugins) in this
repository's wiki.

## Developing Plugins

### Event Subscriptions

Plugins are classes that implement the
[`PluginInterface`](https://github.com/phergie/phergie-irc-bot-react/blob/master/src/PluginInterface.php)
interface. This interface contains a single method, `getSubscribedEvents()`,
which returns an associative array in which the keys are event names and the
values are names of instance methods in the plugin class to handle those
events.

```php
<?php

use Phergie\Irc\Event\EventInterface;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Bot\React\PluginInterface;

class ExamplePlugin implements PluginInterface
{
    public function getSubscribedEvents()
    {
        return array(
            'irc.received.privmsg' => 'onPrivmsg'
        );
    }

    public function onPrivmsg(EventInterface $event, EventQueueInterface $queue)
    {
        // ...
    }
}
```

In the above example, `'irc.received.privmsg'` is an event name and
`'onPrivmsg'` is the name of a method in the `ExamplePlugin` class to handle
that event.

### Supported Events

Event handler methods typically accept two parameters:
* `$event`, an object that contains data about the event and implements the
[`EventInterface`](Even://github.com/phergie/phergie-irc-event/blob/master/src/EventInterface.php)
interface or subinterfaces of it such as
[`UserEventInterface`](https://github.com/phergie/phergie-irc-event/blob/master/src/UserEventInterface.php),
[`ServerEventInterface`](https://github.com/phergie/phergie-irc-event/blob/master/src/ServerEventInterface.php),
and [`CtcpEventInterface`](https://github.com/phergie/phergie-irc-event/blob/master/src/CtcpEventInterface.php));
and
* `$queue`, an object used to send events back to the server that sent the
original event and implements
[`EventQueueInterface`](https://github.com/phergie/phergie-irc-bot-react/blob/master/src/EventQueueInterface.php),
a subinterface of
[`GeneratorInterface`](https://github.com/phergie/phergie-irc-generator/blob/master/src/GeneratorInterface.php).

#### irc.received.each

Occurs when any type of event is received from a server.

#### irc.received.TYPE

Occurs when an event of type `TYPE` (e.g. `privmsg`) is received from a server.

#### irc.sending.all

Occurs after an event (referenced by the `$event` parameter) has been processed
by all plugins, at which point any events to be sent in response are contained
by the `$queue` parameter.

#### irc.sending.each

Occurs before any type of event is sent to a server.

#### irc.sending.TYPE

Occurs before an event of type `TYPE` (e.g. `privmsg`) is sent to a server.

#### irc.sent.each

Occurs after any type of event is sent to a server.

#### irc.sent.TYPE

Occurs when an event of type `TYPE` (e.g. `privmsg`) is sent to a server.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.

## Community

Check out #phergie on irc.freenode.net.

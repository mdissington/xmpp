Library for XMPP protocol connections (Jabber) for PHP.

## SYSTEM REQUIREMENTS

- PHP >= 5.6
- psr/log
- (optional) psr/log-implementation - like monolog/monolog for logging

## INSTALLATION

Install [Composer](https://getcomposer.org/download/) and then

```bash
composer require zorn-v/xmpp
```

## DOCUMENTATION

This library uses an object to hold options:

```php
use Fabiang\Xmpp\Options;
$options = new Options($address);
$options->setUsername($username)
    ->setPassword($password);
```

The server address must be in the format `myjabber.com:5222`.
If the server supports TLS the connection will automatically be encrypted.

If you want to use SOCKS proxy you can set it by

```php
$options->setSocksProxyAddress('localhost:9050');
```

or

```php
$options->setSocksProxyAddress('username:password@localhost:9050');
```

You can also pass a PSR-2-compatible object to the options object:

```php
$options->setLogger($logger);
```

The client manages the connection to the Jabber server and requires the options object:

```php
use Fabiang\Xmpp\Client;
$client = new Client($options);
// optional connect manually
$client->connect();
```

You can use `getMessages()` for get all incoming messages

```php
print_r($client->getMessages());
```

```
Array
(
    [0] => Array
        (
            [from] => user@myjabber.com/resource
            [message] => Message text
        )

)
```

For sending data you just need to pass a object that implements `Fabiang\Xmpp\Protocol\ProtocolImplementationInterface`:

```php
use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Message;

// fetch roster list; users and their groups
$client->send(new Roster);
// set status to online
$client->send(new Presence);

// send a message to another user
$message = new Message;
$message->setMessage('test')
    ->setTo('nickname@myjabber.com');
$client->send($message);

// join a channel
$channel = new Presence;
$channel->setTo('channelname@conference.myjabber.com')
    ->setPassword('channelpassword')
    ->setNickName('mynick');
$client->send($channel);

// send a message to the above channel
$message = new Message;
$message->setMessage('test')
    ->setTo('channelname@conference.myjabber.com')
    ->setType(Message::TYPE_GROUPCHAT);
$client->send($message);
```

After all you should disconnect:

```php
$client->disconnect();
```

## DEVELOPING

If you like this library and you want to contribute, make sure the unit-tests and integration tests are running.
Composer will help you to install the right version of PHPUnit and [Behat](http://behat.org/).

    composer install

After that:

    ./vendor/bin/phpunit
    ./vendor/bin/behat

New features should always tested with Behat.

## LICENSE

BSD-2-Clause. See the [LICENSE](LICENSE.md).

## TODO

- Better integration of channels
- Factory method for server addresses
- improve documentation

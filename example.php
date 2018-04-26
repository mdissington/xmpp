<?php

require 'vendor/autoload.php';
error_reporting(-1);
set_time_limit(0);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;

use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Message;

$logger = new Logger('xmpp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$address    = "myjabber.com:5222";
$socksProxy = 'localhost:9050';

$username = 'xmpp';
$password = 'test';

$options = new Options($address);
$options->setLogger($logger)
    ->setUsername($username)
    ->setPassword($password)
    ->setAutoSubscribe(true)
;
if ($socksProxy) {
    $options->setSocksProxyAddress($socksProxy);
}

$client = new Client($options);

$client->connect();
$client->send(new Roster());
$client->send(new Presence());

while (true) {
    $messages = $client->getMessages(true); //blocking mode for get messages
    foreach ($messages as $msg) {
        $client->send(new Message($msg['message'], $msg['from']));
    }
}

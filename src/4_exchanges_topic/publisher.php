<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('my_topic', 'topic', false, true, false);

$routingKey = $argv[1];
$data = implode(' ', array_slice($argv, 2));

$msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
$channel->basic_publish($msg, 'my_topic', $routingKey);

echo " [x] Sent '{$data}'\n";

$channel->close();
$connection->close();
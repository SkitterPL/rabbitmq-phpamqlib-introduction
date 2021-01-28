<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('my_direct', 'direct', false, true, false);

$data = implode(' ', array_slice($argv, 1));

$msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'application_headers' => new \PhpAmqpLib\Wire\AMQPTable(['x-redelivered-count' => 0])]);
$channel->basic_publish($msg, 'my_direct');
echo " [x] Sent '{$data}'\n";

$channel->close();
$connection->close();
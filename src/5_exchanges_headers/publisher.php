<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('my_headers', 'headers', false, true, false);

$data = $argv[1];
$arguments = array_slice($argv, 2);
$headers = [];
foreach ($arguments as $argument) {
    list ($key, $value) = explode('=', $argument, 2);
    $headers[$key] = $value;
}

$msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
$msg->set('application_headers', new AMQPTable($headers));
$channel->basic_publish($msg, 'my_headers');

echo " [x] Sent '{$data}'\n";

$channel->close();
$connection->close();
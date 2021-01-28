<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->queue_declare('basics', false, true, false, false);

$counter = $argv[1];
$data = implode(' ', array_slice($argv, 2));


foreach (range(1, $counter) as $iteration) {
    $message = $data . ' #' . $iteration;
    $msg = new AMQPMessage($message,  ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    $channel->basic_publish($msg, '', 'basics');
    echo " [x] Sent '{$message}'\n";
}

$channel->close();
$connection->close();
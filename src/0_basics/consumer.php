<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->queue_declare('basics', false, true, false, false);

$callback = function ($msg) {
    sleep(1);
    echo ' [x] Received ', $msg->body, "\n";
};

$channel->basic_consume('basics', '', false, true, false, false, $callback);

echo " [*] Waiting for messages\n";

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
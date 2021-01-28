<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('basics', false, true, false, false);
$channel->basic_qos(null, 1, null);

$callback = function ($msg) {
    sleep(3);
    echo ' [x] Done ', $msg->body, "\n";
    $msg->ack();
};

$channel->basic_consume('basics', '', false, false, false, false, $callback);

echo " [*] Waiting for messages\n";


while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
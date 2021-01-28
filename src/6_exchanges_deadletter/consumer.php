<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->exchange_declare('my_direct', 'direct', false, true, false);
$channel->exchange_declare('my_dead_letter', 'direct', false, true, false);
$channel->basic_qos(null, 1, null);


$channel->queue_declare("direct", false, false, true, false, false, new AMQPTable(["x-dead-letter-exchange" => "my_dead_letter"]));
$channel->queue_declare("ttl", false, false, true, false, false, new AMQPTable(["x-message-ttl" => 5000, "x-dead-letter-exchange" => "my_direct"]));
$channel->queue_bind("direct", 'my_direct');
$channel->queue_bind("ttl", 'my_dead_letter');

/** @var \PhpAmqpLib\Message\AMQPMessage  $msg */
$callback = function ($msg) use ($channel) {

    if($msg->body === 'error') {
        echo " [x] Error!  \n";
        $msg->nack();
        return;
    }

    echo ' [x] Done ', $msg->body, "\n";
    $msg->ack();
    return;
};

$channel->basic_consume("direct", '', false, false, false, false, $callback);

echo " [*] Waiting for messages\n";


while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
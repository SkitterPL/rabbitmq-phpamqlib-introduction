<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('my_headers', 'headers', false, true, false);
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$arguments = array_slice($argv, 1);
$headers = [];
foreach ($arguments as $argument) {
    list ($key, $value) = explode('=', $argument, 2);
    $headers[$key] = $value;
}

$channel->queue_bind($queue_name, 'my_headers', '', false, new AMQPTable($headers));

$channel->basic_qos(null, 1, null);

$callback = function ($msg) {
    sleep(1);
    echo ' [x] Nowy zasób ', $msg->body, "\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

echo " [*] Waiting for messages\n";


while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
<?php
//Establish connection AMQP
$connection = new AMQPConnection();
$connection->setHost('192.168.0.15');
$connection->setLogin('fulc927');
$connection->setPassword('fulc927');
$connection->connect();
//Create and declare channel
$channel = new AMQPChannel($connection);
$routing_key = 'hello';
$callback_func = function(AMQPEnvelope $message, AMQPQueue $q) use (&$max_jobs) {
//	echo " [x] Received: ", $message->getBody(), PHP_EOL;
//	sleep(sleep(substr_count($message->getBody(), '.')));
	//echo " [X] Done", PHP_EOL;
	$q->ack($message->getDeliveryTag());
	global $i;
        echo "Message $i: " . $message->getBody() . "\n";
        $i++;
        if ($i = 1) {
            // Bail after 1 message
            return false;
        }
	
};
try{
	$queue = new AMQPQueue($channel);
	$queue->setName($routing_key);
	$queue->setFlags(AMQP_DURABLE);
	$queue->declareQueue();
	//echo ' [*] Waiting for logs. To exit press CTRL+C', PHP_EOL;
	$queue->consume($callback_func);
}catch(AMQPQueueException $ex){
	print_r($ex);
}catch(Exception $ex){
	print_r($ex);
}
$connection->disconnect();

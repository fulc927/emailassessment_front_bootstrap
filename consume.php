<?php
session_start();
$connection = new AMQPConnection();
$connection->setHost('185.246.84.157');
$connection->setLogin('fulc927');
$connection->setPassword('fulc927');
$connection->connect();
//Create and declare channel
$channel = new AMQPChannel($connection);
$callback_func = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
	//$message = $queue->get(AMQP_AUTOACK);
	$queue->ack($message->getDeliveryTag());
	global $i;
	if(isset($_POST['action']) && !empty($_POST['action'])) {
        //echo json_encode($i);
		echo "Message $i: ";
         }

        //echo "Message $i: " . $message->getBody() . "\n";
        $i++;
        if ($i = 1) {
            // Bail after 1 message
            return false;
        }
	};
try{
	$queue = new AMQPQueue($channel);
	$queue->setName($_SESSION['key']);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->declareQueue();
	$queue->consume($callback_func);
}catch(AMQPQueueException $ex){
	print_r($ex);
}catch(Exception $ex){
	print_r($ex);
}
$connection->disconnect();

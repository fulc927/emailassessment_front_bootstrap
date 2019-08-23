<?php
session_start();
$connection = new AMQPConnection();
$config = parse_ini_file('./amqpconnect.ini'); 
$connection->setHost($config['servername']);
$connection->setLogin($config['username']);
$connection->setPassword($config['password']);
$connection->connect();
//Create and declare channel
$channel = new AMQPChannel($connection);
$callback_func = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
	//$message = $queue->get(AMQP_AUTOACK);
	$queue->ack($message->getDeliveryTag());
	global $i;
	if(isset($_POST['action']) && !empty($_POST['action'])) {
        //echo json_encode($message->getBody()). "\n";
        echo "Message $i: " . $message->getBody() . "\n";

         }

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
}catch(AMQPQueueException $queue){
	print_r($queue);
}catch(Exception $queue){
	print_r($queue);
}
$connection->disconnect();

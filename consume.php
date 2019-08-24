<?php
session_start();
if (isset($_SESSION['key']) && !empty($_SESSION['key'])) {
       	$connection = new AMQPConnection();
	$config = parse_ini_file('./amqpconnect.ini'); 
	$connection->setHost($config['servername']);
	$connection->setLogin($config['username']);
	$connection->setPassword($config['password']);
	$connection->connect();
	//Create and declare channel
	$channel = new AMQPChannel($connection);
	//
	
	$exchange = new AMQPExchange($channel);
	$exchange_name = 'topic_spamass';
	$exchange->setName($exchange_name);
	$exchange->setType(AMQP_EX_TYPE_TOPIC);
	$exchange->declareExchange();
	$exchange->getArgument($_SESSION['key']);
	////////
		$callback_func = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
		global $i;
        	//echo json_encode($message->getBody()). "\n";
        	echo "Message $i: " . $message->getBody() . "\n";
        	$i++;
        	if ($i = 1) {
            	// Bail after 1 message
			$_SESSION['key'] = '';
                	return false;
        	}
		};
	//LE IF DE LA MORT
	if($exchange=false) {
	try{
	//$channel->setPrefetchCount(1);	
	$queue = new AMQPQueue($channel);
	$queue->setName($_SESSION['key']);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->declareQueue();
	$queue->consume($callback_func);
	}catch(AMQPQueueException $queue){
	print_r($queue);
	}catch(Exception $queue){
	print_r($queue);
	$connection->disconnect();
	}

	} else {  
    	echo "N0, keà is not set";
	echo $_SESSION['key'];
}
} else {
	echo "ben à pas de queue";
	                	return false;
		$connection->disconnect();
}
		

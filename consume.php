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
	$o=$_SESSION['key'];
	echo $o;
	$exchange->getArgument($o);

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
	
	//$channel->setPrefetchCount(1);	
	$queue = new AMQPQueue($channel);
	$queue->setName($_SESSION['key']);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->declareQueue();
	//LE IF DE LA MORT
if(!isset(var_export($queue->declareQueue()))) {
	$queue->consume($callback_func);
	$connection->disconnect();
	} else {  
    	echo "la queue est vide";}
} else {
	echo "score deaja counsumé";
	                	return false;
		$connection->disconnect();
		echo $_SESSION['key'];

}
		

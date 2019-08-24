<?php
session_start();
if (isset($_SESSION['key']) && !empty($_SESSION['key'])) {
       echo $_SESSION['key'];  
	   
	$connection = new AMQPConnection();
	$config = parse_ini_file('./amqpconnect.ini'); 
	$connection->setHost($config['servername']);
	$connection->setLogin($config['username']);
	$connection->setPassword($config['password']);
	$connection->connect();
	//Create and declare channel
	$channel = new AMQPChannel($connection);
	$callback_func = function(AMQPEnvelope $message, AMQPQueue $queue) use (&$max_jobs) {
	//$queue->ack($message->getDeliveryTag());
	global $i;
	//if(isset($_POST['action']) && !empty($_POST['action'])) {
        //echo json_encode($message->getBody()). "\n";
        echo "Message $i: " . $message->getBody() . "\n";

         //}

        $i++;
        if ($i = 1) {
            // Bail after 1 message
		$_SESSION['key'] = '';
                return false;
        }
	};
	try{
	$channel->setPrefetchCount(1);	
	$queue = new AMQPQueue($channel);
	$queue->setName($_SESSION['key']);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->declareQueue();
		//faireencore un if pour checker si queue existe, le cas échéant disconnect et éventuellement reload
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

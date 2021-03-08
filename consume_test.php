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
	$exchange = new AMQPExchange($channel);
	$exchange_name = 'topic_spamass';
	$exchange->setName($exchange_name);
	$exchange->setType(AMQP_EX_TYPE_TOPIC);
	$exchange->declareExchange();
	$o=$_SESSION['key'];
	//$o = 'ZS7nyhyT9v@otp.fr.eu.org';
	//echo $o;
	$exchange->getArgument($o);

	////////
		$i = 0;
		$callback_func = function(AMQPEnvelope $message, AMQPQueue $queue) {
			$starttime = time();
			global $i;
        		//echo "Message $i: " . $message->getBody() . "\n";
        		echo $message->getBody() . "\n";
        		$i++;
        		//if ($i > 1 || ((time() - $starttime) < 14000)) {
        		if ($i > 1 ) {
            			// Bail after 2 messages (rabbit count from 0
				$_SESSION['key'] = '';
                		return false; //false indique à la méthode AMQPqueue:consume de stopper la procédure d'attente
        		}

			//	while ((time() - $starttime)<10); //stop with 10 seconds
                	//	return false; //false indique à la méthode AMQPqueue:consume de stopper la procédure d'attente
			};	
	
	$queue = new AMQPQueue($channel);
	//$queue->setName($o);
	$queue->setName($_SESSION['key']);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->setArgument('x-expires', 399000);
	$queue->declareQueue();
	//LE IF DE LA MORT (il permet de détecter si des messages amqp sont déja arrivés sur la queue)
	if($queue->declareQueue()) {
        $queue->consume($callback_func);
        //$connection->disconnect();
	} 
	else {
		echo "Commencez par envoyer un email à l'adresse ci-dessus ;-)";
	}
}

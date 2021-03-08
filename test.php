<?php
session_start();
function test(){
$connection = new AMQPConnection();
$config = parse_ini_file('./amqpconnect.ini'); 
//$connection->setHost($config['servername']);
$connection->setHost('127.0.0.1');
$connection->setLogin($config['username']);
$connection->setPassword($config['password']);
if ($connection->connect()) {
    $connection->connect();
}
else {
    echo "Cannot connect to the broker";
}
$channel = new AMQPChannel($connection);
try {
	$exchange = new AMQPExchange($channel);
	$exchange_name = 'email-in';
	$exchange->setType(AMQP_EX_TYPE_TOPIC);
	$exchange->setName($exchange_name);
	$exchange->setFlags(AMQP_DURABLE);
	$exchange->declareExchange();
	$queue = new AMQPQueue($channel);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->setName('incoming_messagetoscore');
	$queue->declareQueue();
	$bdkey = generateRandomString();
	$_SESSION['key'] = $bdkey;
	echo $bdkey;
	$queue->bind($exchange_name,$bdkey);
	} catch(Exception $exchange) {
		print_r($exchange);
	}	
try     {
	$channel->setPrefetchCount(1);	
	$exchange_name2 = 'topic_spamass';
	$exchange2 = new AMQPExchange($channel);
	$exchange2->setType(AMQP_EX_TYPE_TOPIC);
	$exchange2->setName($exchange_name2);
	$exchange2->declareExchange();
	$queue = new AMQPQueue($channel);
	$queue->setFlags(AMQP_AUTODELETE);
	$queue->setName($bdkey);
	//$queue->setArgument(expiration => 5000);
		//$queue->setArgument('x-message-ttl', 42);
	$queue->setArgument('x-expires', 399000); //ce qui fait 99 secondes
	$queue->declareQueue();
	$queue->bind($exchange_name2, $bdkey);
	} 

	catch(AMQPQueueException $queue)

	{print_r($queue);}
       
	//catch(Exception $queue)
    	//{print_r($queue);$connection->disconnect(); }
//NEW QUEUE POUR DKIM ONLY		
try     {
	$channel->setPrefetchCount(1);	
	$exchange_name3 = 'topic_spamass_dkim';
	$exchange3 = new AMQPExchange($channel);
	$exchange3->setType(AMQP_EX_TYPE_TOPIC);
	$exchange3->setName($exchange_name3);
	$exchange3->declareExchange();
	$queue3 = new AMQPQueue($channel);
	$queue3->setFlags(AMQP_AUTODELETE);
	$queue3->setName($bdkey."_dkim");
	//$queue->setArgument(expiration => 5000);
		//$queue->setArgument('x-message-ttl', 42);
	$queue3->setArgument('x-expires', 99000); //ce qui fait 99 secondes
	$queue3->declareQueue();
	$queue3->bind($exchange_name3, $bdkey."_dkim");
	} 

	catch(AMQPQueueException $queue3)

	{print_r($queue3);}
       
	catch(Exception $queue3)

    	{print_r($queue3);$connection->disconnect(); }

}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString."@otp.fr.eu.org";
}
test();
?>

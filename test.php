<?php
session_start();
function test(){
$connection = new AMQPConnection();
$config = parse_ini_file('./amqpconnect.ini'); 
$connection->setHost($config['servername']);
$connection->setLogin($config['username']);
$connection->setPassword($config['password']);
$connection->connect();
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
	$queue->setName('incoming_message');
	$queue->declareQueue();
	$bdkey = generateRandomString();
	$_SESSION['key'] = $bdkey;
	echo $bdkey;
	$queue->bind($exchange_name,$bdkey);
	} catch(Exception $exchange) {
		print_r($exchange);
	}	
	try {
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
	$queue->setArgument('x-expires', 99000);
	$queue->declareQueue();
	$queue->bind($exchange_name2, $bdkey);
    } catch(AMQPQueueException $queue) {
	print_r($queue);
    } catch(Exception $queue) {
	print_r($queue);	
$connection->disconnect();
	}	
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

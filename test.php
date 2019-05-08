<?php
//CELUI AVEC LE BITOCUL

function test(){
//Establish connection to AMQP
$connection = new AMQPConnection();
$connection->setHost('192.168.0.15');
$connection->setLogin('fulc927');
$connection->setPassword('fulc927');
$connection->connect();
//Create and declare channel
$channel = new AMQPChannel($connection);
try {
	//Declare Exchange
	$exchange = new AMQPExchange($channel);
	$exchange_name = 'email-in';
	$exchange->setType(AMQP_EX_TYPE_TOPIC);
	$exchange->setName($exchange_name);
	$exchange->setFlags(AMQP_DURABLE);
	$exchange->declareExchange();
	//Do not declasre the queue name by setting AMQPQueue::setName()
	$queue = new AMQPQueue($channel);
	$queue->setFlags(AMQP_AUTODELETE);
//	$queue->setFlags(AMQP_PASSIVE);
	//$bdkey = generateRandomString();
	//$queue->setName($bdkey);
	$queue->setName('bitocul');
	$queue->declareQueue();
	$bdkey = "contact@patatedouce.fr";
	echo $bdkey;
	//echo "\r";
	//$queue->declareQueue($bdkey);
	$queue->bind($exchange_name,$bdkey);
	//echo sprintf("Queue Name: %s", $queue->getName()), PHP_EOL;
	} catch(Exception $exchange) {
		print_r($exchange);
	}

$connection->disconnect();
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString."@patatedouce.fr";
}
test();
?>

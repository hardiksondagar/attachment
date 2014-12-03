<?php
// remove first line above if you're not running these examples through PHP CLI

include_once("class.contextio.php");

// see https://console.context.io/#settings to get your consumer key and consumer secret.
$contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
$accountId = null;
// list your accounts
$r = $contextIO->listAccounts();
foreach ($r->getData() as $account) {
	 echo $account['id'] . "\t" . join(", ", $account['email_addresses']) . "<br/>";
	if (is_null($accountId)) {
		$accountId = $account['id'];
	}
	$accountId = $account['id'];
}

if (is_null($accountId)) {
	die;
}



// EXAMPLE 1
// Print the subject line of the last 20 emails sent to with bill@widgets.com
$args = array(
	// 'to'=>'hardikmsondagar@gmail.com',
	 'limit'=>200
	 );
// echo "\nGetting last 20 messages exchanged with {$args['to']}\n";
$r = $contextIO->listMessages($accountId);
// header('Content-Type: application/json');
// echo json_encode($r->getData());
foreach ($r->getData() as $message) {
	echo '<br>';
	echo "Subject: ".$message['subject']."\n";
}



echo "\nall examples finished\n";
?>

<?php
session_start();
include_once("class.contextio.php");

// see https://console.context.io/#settings to get your consumer key and consumer secret.
$contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
$accountId = null;
$email=$_SESSION['email'];


// list your accounts
$r = $contextIO->listAccounts();
foreach ($r->getData() as $account) {
	if (is_null($accountId) && join(", ", $account['email_addresses'])==$email) {
		$accountId = $account['id'];
	}
}

if (is_null($accountId)) {
	header('Content-Type: application/json');
	echo json_encode(null);
	die;
}

$r = $contextIO->syncSource($accountId);
$args = array(
	'limit'=>100,
	'offset'=>0,
	'sort_order'=>'desc',
	);
$r = $contextIO->listMessages($accountId,$args);
header('Content-Type: application/json');
echo json_encode($r->getData());




?>

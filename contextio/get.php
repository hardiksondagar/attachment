<?php
session_start();
include_once("class.contextio.php");
header('Content-Type: application/json');
// see https://console.context.io/#settings to get your consumer key and consumer secret.
$contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
$accountId = null;


// list your accounts
$r = $contextIO->listAccounts();
foreach ($r->getData() as $account) {
	if (is_null($accountId) && join(", ", $account['email_addresses'])==$_SESSION['email']) {
		$accountId = $account['id'];
	}
}

if (is_null($accountId)) {
	echo json_encode(null);
	die;
}

$r = $contextIO->syncSource($accountId);

if(isset($_GET['message_id']))
{
	$args = array(
	 'message_id'=>$_GET['message_id'],
	);
	$r = $contextIO->getMessageBody($accountId,$args);
}
else
{
	$args = array(
	// 'from'=>'hardikmsondagar@gmail.com',
	'limit'=>100,
	'offset'=>0,
	'sort_order'=>'desc',

	);
	$r = $contextIO->listMessages($accountId,$args);
}

echo json_encode($r->getData());
?>

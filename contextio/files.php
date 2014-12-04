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


if(isset($_GET['file_id']))
{
	$args = array(
		'file_id'=>$_GET['file_id'],
		);
	$r = $contextIO->getFileContent($accountId,$args);
}
else
{
	$r = $contextIO->listFiles($accountId);
}
echo json_encode($r->getData());
?>

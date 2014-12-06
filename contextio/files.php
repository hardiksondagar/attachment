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
	header('Accept: text/uri-list');
	$args = array(
		'file_id'=>$_GET['file_id'],
		);
	$r = $contextIO->getFileURL($accountId,$args);
	// $r = $contextIO->getFile($accountId,$args);
	echo json_encode($r->getRawResponse());
	// echo json_encode($r->getData());
	
}
else
{
	$r = $contextIO->listFiles($accountId);
	echo json_encode($r->getData());

}
?>

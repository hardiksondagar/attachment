<?php
session_start();
include_once("class.contextio.php");

// see https://console.context.io/#settings to get your consumer key and consumer secret.
$contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
$accountId = null;
$r = $contextIO->getConnectToken(null,array());
$status=false;

foreach ($r->getData() as $key => $value) {
	if(isset($value['account']['email_addresses']) && in_array($_SESSION['email'], $value['account']['email_addresses']))
	{
		$status=true;
	}
}
if($status)
{
	header("Location: /contextIO");
	return;
}
$r = $contextIO->addConnectToken(null,array(
	'email'=>$_SESSION['email'],
	// 'first_name'=>$_GET['first_name'],
	// 'last_name'=>$_GET['last_name'],
	'callback_url'=>'http://www.mywedstory.com/contextIO/index.php',
	// 'service_level' => 'pro'
	));
if ($r === false) {
	header("Location: /contextIO");
} 
else {
  	// redirect user to the connect token UI
	$token = $r->getData();
	$_SESSION['ContextIO-connectToken'] = $token['token'];
	header("Location: ". $token['browser_redirect_url']);
}

?>

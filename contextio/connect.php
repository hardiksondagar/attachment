<?php
// remove first line above if you're not running these examples through PHP CLI
ob_start();
include_once("class.contextio.php");


// see https://console.context.io/#settings to get your consumer key and consumer secret.
$contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
$accountId = null;
$r = $contextIO->getConnectToken(null,array());
$status=false;

if(!isset($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
		//echo '<script>window.close();</script>';
		// throw new Exception("Invalid Email address");

	$return['status']='error';
	$return['data']='Invalid Email Address';
	// echo json_encode($return);
	// return;
	header('Location: http://angular.absolutewebtech.net/close.php?message='.$return['data'].'&status='.$return['status']);


}


foreach ($r->getData() as $key => $value) {
	if(isset($value['account']['email_addresses']) && in_array($_GET['email'], $value['account']['email_addresses']))
	{
		$status=true;
	}
}

$return=array();
if($status)
{
	
	//echo '<script>window.close();</script>';
	$return['status']='success';
	$return['data']='already added';
	// echo json_encode($return);
	header('Location: http://angular.absolutewebtech.net/close.php?message='.$return['data'].'&status='.$return['status']);
	return;


}
$r = $contextIO->addConnectToken(null,array(
	'email'=>$_GET['email'],
	// 'first_name'=>$_GET['first_name'],
	// 'last_name'=>$_GET['last_name'],
	'callback_url'=>'http://angular.absolutewebtech.net/close.php',
	// 'service_level' => 'pro'
	));

if ($r === false) {
	$return['status']='error';
	$return['data']='Unable to get a connect token.';
	// echo json_encode($return);
	header('Location: http://angular.absolutewebtech.net/close.php?message='.$return['data'].'&status='.$return['status']);

	return;
	
} 
else {
  // redirect user to the connect token UI
	$token = $r->getData();
	$_SESSION['ContextIO-connectToken'] = $token['token'];
	$return['status']='redirect';
	$return['data']=$token['browser_redirect_url'];
	$data=$return['data'];
	$status=$return['status'];
	// echo json_encode($return);
	$url="Location: ".$token['browser_redirect_url'];
	// echo $url;
	header($url);
	exit;

	// echo json_encode($token['browser_redirect_url']);
	// header("Location: ". $token['browser_redirect_url']);
}

?>
<?php
session_start();

require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';
include_once 'contextio/class.contextio.php';


$client = new Google_Client();
$client->setApplicationName("Google UserInfo PHP Starter Application");

$oauth2 = new Google_Oauth2Service($client);
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
  return;
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['token']);
  $client->revokeToken();
}

if ($client->getAccessToken()) {
  $user = $oauth2->userinfo->get();


  $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
  $img = filter_var($user['picture'], FILTER_VALIDATE_URL);
  $personMarkup = "$email<div><img src='$img?sz=50'></div>";

  $_SESSION['token'] = $client->getAccessToken();
  $_SESSION['email'] = $email;


  /*contextIO part */

		// see https://console.context.io/#settings to get your consumer key and consumer secret.
  $contextIO = new ContextIO('d7j0pfid','O0mcOIfLQAUGeXAd');
  $accountId = null;


		// list your accounts
  $r = $contextIO->listAccounts();
  foreach ($r->getData() as $account) {
   if (is_null($accountId) && join(", ", $account['email_addresses'])==$email) {
    $accountId = $account['id'];
  }
}






} else {
  $authUrl = $client->createAuthUrl();
}

?>
<!DOCTYPE html>
<html lang="en" ng-app="contextIO">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>MyApple</title>

	
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">


	<!-- Custom styles for this template -->
	<link href="assets/css/main.css" rel="stylesheet">

	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular-sanitize.js"></script>
	
	<style>
	/*img.desaturate{
-webkit-filter: grayscale(100%);
filter: grayscale(100%);
filter: gray;
filter: url("data:image/svg+xml;utf8,<svg version='1.1' xmlns='http://www.w3.org/2000/svg' height='0'><filter id='greyscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0' /></filter></svg>#greyscale");
}*/
</style>


<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
      <![endif]-->
    </head>

    <body ng-controller="contextIOController">


     <!-- Fixed navbar -->
     <div class="navbar navbar-default navbar-fixed-top">
      <div class="container">
       <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
         <span class="icon-bar"></span>
         <span class="icon-bar"></span>
         <span class="icon-bar"></span>
       </button>
       <a class="navbar-brand" href="./"><i class="fa fa-envelope"></i> MyApple</a>
     </div>
     <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
       <li id="profile-pic"></li>
       <li><a id="name" href="#" style="display:none;"></a></li>

       <?php
       if(isset($authUrl)) {


        echo "<li class='active'><a class='login' href='$authUrl'>Login with Gmail</a></li>";
      } else {
        echo "<li id='profile-pic'><img src='$img?sz=50' class='desaturate'/></li>";
        ?>

        <li class="dropdown">
         <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?=$user['name']?> <span class="caret"></span></a>
       </li>
       <li ng-click="getMessages()"><a href="#"><i class="fa fa-refresh fa-2x"></i></a></li>
       <?php
       echo "<li class='active'><a class='logout' href='?logout'>Logout</a></li>";
     }?>
   </ul>
 </div><!--/.nav-collapse -->
</div>
</div>


<div id="hello">
  <div class="container">
   <div class="row">
     <div class="col-lg-offset-9 col-lg-3">
      <input class="form-control" ng-model="searchFile" placeholder="Search File">  
      <br>
    </div>

    <div class="col-lg-12 centered" ng-hide="!loading">
     <button id="loading" class="btn btn-info" ng-bind-html="loading"></button>
     <br>
   </div>
   <?php
   if(!$authUrl)
   { 
     if(is_null($accountId))
     {	
      echo '<div class="col-lg-12 centered" ng-init="connected=false">';
      echo '<p>No account found with your email <strong>'.$email.'</strong></p>';
      echo '<a href="contextio/add.php" class="btn btn-info">Add Mailbox</a>';
      echo '</div>';
    }
    else
    {
      ?>
      
      <div class="col-lg-12" id="files" ng-init="getFiles()">

       <!--  <table class="table" ng-show="files.length" style="max-height=400px">
          <thead>
           <tr>
            <th>#</th>
            <th>
              <a href="" ng-click="reverse=!reverse;orderFile('name', reverse)">Name</a>
            </th>
            <th>
              <a href="" ng-click="reverse=!reverse;orderFile('addresses.from.name', reverse)">Sender</a>
            </th>
            <th>
              <a href="" ng-click="reverse=!reverse;orderFile('addresses.from.email', reverse)">Email</a>
            </th>
            <th>
              <a href="" ng-click="reverse=!reverse;orderFile('subject',reverse)">Subject</a>
            </th>
            <th>
              <a href="" ng-click="reverse=!reverse;orderFile('type',reverse)">Type</a>
            </th>
          </tr>
        </thead>
        <tbody>
        <tr ng-repeat="file in files | filter:searchFile" ng-click="getFile(file)" style="cursor:pointer">
           <td>{{$index+1}}</td>
           <td ng-bind="file.file_name"></td>
           <td ng-bind="file.addresses.from.name"></td>
           <td ng-bind="file.addresses.from.email"></td>
           <td ng-bind="file.subject"></td>
           <td ng-bind="file.type"></td>
         </tr>

       </tbody>
     </table> -->


     <div class="media col-lg-4" ng-repeat="file in files | filter:searchFile">
      <a class="media-left" href="#">
        <img data-src="holder.js/64x64" alt="64x64" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+PGRlZnMvPjxyZWN0IHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgZmlsbD0iI0VFRUVFRSIvPjxnPjx0ZXh0IHg9IjEzLjQ2ODc1IiB5PSIzMiIgc3R5bGU9ImZpbGw6I0FBQUFBQTtmb250LXdlaWdodDpib2xkO2ZvbnQtZmFtaWx5OkFyaWFsLCBIZWx2ZXRpY2EsIE9wZW4gU2Fucywgc2Fucy1zZXJpZiwgbW9ub3NwYWNlO2ZvbnQtc2l6ZToxMHB0O2RvbWluYW50LWJhc2VsaW5lOmNlbnRyYWwiPjY0eDY0PC90ZXh0PjwvZz48L3N2Zz4=" data-holder-rendered="true" style="width: 64px; height: 64px;">
      </a>
      <div class="media-body">
        <strong><h5 class="media-heading" >{{file.file_name | cut:true:30:'...'}} <i class="fa fa-cloud-download"  style="cursor:pointer" ng-click="getFile(file)"></i></h5></strong>
        <small ng-bind="file.addresses.from.email"></small>
        <br/>
        <small>{{file.date * 1000 | date:'longDate'}}</small>
      </div>
    </div>
    <a href="#" id="download" download style="display:none;">Data</a>


     <!-- <h2>FREE BOOTSTRAP THEMES</h2> -->


   </div><!-- /col-lg-8 -->
   <?php
 }
}
?>
</div><!-- /row -->
</div> <!-- /container -->
</div><!-- /hello -->

<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
  <script src="script.js"></script>

</body>
</html>

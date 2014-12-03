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
      
      <div class="col-lg-10 col-lg-offset-1" id="mails" ng-init="getMessages()">
        <div class="well col-lg-12" ng-show="selectedMessage" style="word-wrap: break-word;">
          <i class="fa fa-2x fa-times-circle pull-right" ng-click="selectedMessage=null"  style="cursor:pointer"></i>
          <strong>From: {{selectedEmail.addresses.from.name}}({{selectedEmail.addresses.from.email}})</strong>
          <br>
          <strong><span ng-repeat="toAddress in selectedEmail.addresses.to">To: {{toAddress.email}},</span></strong>
          <hr>
          <div ng-show="message.type=='text/html'" ng-bind-html="message.content"></div>
          <div ng-show="message.type=='text/plain'" ng-bind="message.content"></div>

        </div>

        <div class="col-lg-12"  ng-show="emails.length && !selectedMessage" >
          <div class="col-lg-4">
            <label>Please add Email  <i class="fa fa-plus-circle" ng-init="counter=1" ng-click="counter=counter+1"></i> <i class="fa fa-minus-circle" ng-show="counter>1" ng-click="counter=counter-1"></i></label>
            <div class="form-group"  ng-repeat="i in getNumber(counter) track by $index" >
              <input class="form-control"ng-model='searchFrom[$index]' placeholder="Email Address">
            </div>
          </div>
          <br>
        </div>

        <table class="table" ng-show="emails.length  && !selectedMessage" style="max-height=400px">
          <thead>
           <tr>
            <th>#</th>
  								<!-- <th>Sender</th>
  								<th>Email</th>
  								<th>Subject</th>
  							-->
  							<th><a href="" ng-click="reverse=!reverse;order('addresses.from.name', reverse)">Sender</a>

  							</th>
  							<th>
  								<a href="" ng-click="reverse=!reverse;order('addresses.from.email', reverse)">Email</a>
  							</th>
  							<th>
  								<a href="" ng-click="reverse=!reverse;order('subject',reverse)">Subject</a>
  							</th>
  						</tr>
  					</thead>
  					<tbody>
  						<tr ng-repeat="email in emails | EmailSearch:searchFrom" ng-click="getMessageBody(email)" ng-class="{active:email.message_id==selectedMessage}" style="cursor:pointer">
  							<td>{{$index+1}}</td>
  							<td>{{email.addresses.from.name}}</td>
  							<td>{{email.addresses.from.email}}</td>
  							<td>{{email.subject}}</td>
  						</tr>

  					</tbody>
  				</table>


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


	<script>

    /* Seach in Object */

    var comparator = function(obj, text) {
      if (obj && text && typeof obj === 'object' && typeof text === 'object') {
        for (var objKey in obj) {
          if (objKey.charAt(0) !== '$' && hasOwnProperty.call(obj, objKey) &&
            comparator(obj[objKey], text[objKey])) {
            return true;
        }
      }
      return false;
    }
    text = ('' + text).toLowerCase();
    return ('' + obj).toLowerCase().indexOf(text) > -1;
  };
  var search = function(obj, text) {
    if (typeof text == 'string' && text.charAt(0) === '!') {
      return !search(obj, text.substr(1));
    }
    switch (typeof obj) {
      case "boolean":
      case "number":
      case "string":
      return comparator(obj, text);
      case "object":
      switch (typeof text) {
        case "object":
        return comparator(obj, text);
        default:
        for (var objKey in obj) {
          if (objKey.charAt(0) !== '$' && search(obj[objKey], text)) {
            return true;
          }
        }
        break;
      }
      return false;
      case "array":
      for (var i = 0; i < obj.length; i++) {
        if (search(obj[i], text)) {
          return true;
        }
      }
      return false;
      default:
      return false;
    }
  };

  var win;


  app=angular.module('contextIO', ['ngSanitize']);

  app.controller('contextIOController', ['$scope','$http', '$filter', '$sce','$interval',function($scope,$http,$filter,$sce,$interval) {

   $scope.emails=[];
   $scope.loading=null;
   $scope.searchFrom=[];

   $scope.connected=false;

   $scope.selectedMessage=null;
   $scope.message={};
   $scope.selectedEmail={};


   $scope.getMessageBody=function(email)
   {
    $scope.selectedEmail=email;
    $('body').animate({scrollTop:0}, '500', 'swing');
    $scope.message={
      content:'Message Loading  <i class="fa fa-spinner fa-spin"></i>',
      type:'text/html'
    };
    
    $scope.selectedMessage=email.message_id;
    
    $http({
     method: 'GET',
     url: "contextio/get.php?message_id="+$scope.selectedMessage
   }).success(function (data, header) {

     if(typeof data=="object")
     {

      if(data.length==2)
      {
        data[0]=data[1];
      }

      $scope.message={
        content:$sce.trustAsHtml(data[0].content),
        type:data[0].type
      };
    }
    else
    {

    }
    if (typeof callback === "function") {
      callback();
    }

  }).error(function (data, header) {

   $scope.message={

    content:'Message Loading Failed <i class="fa fa-meh-o"></i>',
    type:'text/html'
  };

}).then(function()
{
})



}

$scope.getMessages=function(Loadingflag)
{	
  $('#loading').fadeIn();
				// $('#loading').html();
				
        if(!Loadingflag)
        {
          $scope.loading='Loading <i class="fa fa-spinner fa-spin"></i>';
        }
        $http({
         method: 'GET',
         url: "contextio/get.php"
       }).success(function (data, header) {


         if(typeof data=="object")
         {
          $scope.emails = data;
          $scope.loading=null;
          if(data.length==0)
          {
           $scope.getMessages();
         }
         $scope.connected=true;
       }
       else
       {
        $scope.loading='Some error accoured <i class="fa fa-meh-o"></i>';
      }
      if (typeof callback === "function") {
        callback();
      }

    }).error(function (data, header) {
     $scope.loading='Some error accoured <i class="fa fa-meh-o"></i>';


   }).then(function()
   {
   })



 }



 $scope.getNumber = function(num) {
  return new Array(num);   
}

$interval(function(){
  $scope.getMessages(true);
},10000);

var orderBy = $filter('orderBy');

$scope.order = function(predicate, reverse) {
  $scope.emails = orderBy($scope.emails, predicate, reverse);
};





}
]
);

app.filter('EmailSearch', function() {
  return function(items, searchText) {

    if (items == null) {
      return false;
    }
    if (!searchText.length)
    {
      return items;
    }

    // searchText = searchText.toLowerCase();
    angular.forEach(searchText,function(value,key)
    {
      if(value)
      {
        searchText[key]=searchText[key].toLowerCase();
      }
      else
      {
        searchText.splice(key, 1);
      }

    });
    
    var returnArray = [];
    returnArray.splice(0, returnArray.length);
    angular.forEach(items, function(value, key) {

      var added=true;
      angular.forEach(searchText,function(searchValue,searchKey)
      { 

        if (added && search(value.addresses.from.email, searchValue))
        {
          returnArray.push(items[key]);
          added=false;
          return;
        }        
      });

      // if (search(value.addresses.from.email, searchText))
      // {
      //   returnArray.push(items[key]);
      // }

    });
    return returnArray;
  }



});


function receiver()
{
	window.setTimeout(function()
	{
		win.close();
	},1000);
	window.setTimeout(function()
	{
		angular.element('body').scope().getMessages();
		angular.element('body').scope().$apply();
	},1000);
	$('#connect').html('Connect to MailBox');
	$('#connect').fadeOut(0);



}

</script>
</body>
</html>

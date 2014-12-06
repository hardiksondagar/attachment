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

  app=angular.module('contextIO', ['ngSanitize']);

  app.controller('contextIOController', ['$scope','$http', '$filter', '$sce','$interval',function($scope,$http,$filter,$sce,$interval) {

   $scope.emails=[];
   $scope.files=[];
   $scope.loading=null;
   $scope.searchFrom=[];

   $scope.connected=false;

   $scope.selectedMessage=null;
   $scope.message={};
   $scope.selectedEmail={};

   $scope.selectedFile={};


   $scope.getMessages=function(Loadingflag)
   {	
    $('#loading').fadeIn();
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
  });

}


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

});



}


$scope.getFiles=function(Loadingflag)
{ 
  $('#loading').fadeIn();
  if(!Loadingflag)
  {
    $scope.loading='Loading <i class="fa fa-spinner fa-spin"></i>';
  }

  $http({
   method: 'GET',
   url: "contextio/files.php"
 }).success(function (data, header) {

  if(typeof data=="object")
  {
    $scope.files = data;
    console.log($scope.files);
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
});

}


$scope.getFile=function(file)
{

  $scope.selectedFile=file;

  $http({
   method: 'GET',
   url: "contextio/files.php?file_id="+$scope.selectedFile.file_id
 }).success(function (data, header) {

   if(typeof data=="object")
   {

    console.log(data);

  }
  else
  {

  }
  if (typeof callback === "function") {
    callback();
  }

}).error(function (data, header) {


});



}



$scope.getNumber = function(num) {
  return new Array(num);   
}

// $interval(function(){
//   $scope.getMessages(true);
// },10000);

    var orderBy = $filter('orderBy');

    $scope.order = function(predicate, reverse) {
      $scope.emails = orderBy($scope.emails, predicate, reverse);
    };

    $scope.orderFile = function(predicate, reverse) {
      $scope.files = orderBy($scope.files, predicate, reverse);
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
        });
        return returnArray;
      }



    });




app.controller('login',['$scope','$route','textData','$http','$mdDialog',
function($scope,$route,textData,$http,$mdDialog){

  var text = textData.data;

  $('.spinner').addClass('out');
  $scope.textData = text;
  $scope.rememberMe = true;
  $('input[name="username"]').focus();

  function showErrorMessage(){
    $mdDialog.show(
        $mdDialog.alert()
          .title(text.errorTitle)
          .content(text.errorText)
          .ok(text.okButton)
      );
  }

  $scope.login = function(){

    $('.login-dialog').addClass('pending');
    $('.spinner').removeClass('out');

    $http.post('api',{
      request: 'login',
      username: $scope.username,
      password: $scope.password
    }).success(function(response){

      $('.login-dialog').removeClass('pending');
      $('.spinner').addClass('out');

      if(!response.success) showErrorMessage();

    }).error(function(){

      $('.login-dialog').addClass('pending');
      $('.spinner').removeClass('out');

      showErrorMessage();

    });

  }

}]);
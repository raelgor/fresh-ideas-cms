app.controller('login',['$scope','$route','textData','$http','$mdDialog','$location',
function($scope,$route,textData,$http,$mdDialog,$location){

  var text = textData.data;

  $('.spinner').addClass('out');
  $scope.textData = text;
  $scope.rememberMe = true;
  $scope.showTooltip = false;
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

    if(!$scope.username || !$scope.password) return $scope.showTooltip = true;

    $scope.showTooltip = false;

    $('.login-dialog').addClass('pending');
    $('.spinner').removeClass('out');

    $http.post('api',{
      request: 'login',
      username: $scope.username,
      password: $scope.password
    }).success(function(response){

      if(response.message != "success"){

        $('.login-dialog').removeClass('pending');
        $('.spinner').addClass('out');
        showErrorMessage();

      } else {

        window.session_token = response.session_token;
        $scope.rememberMe &&
        localStorage.setItem('session_token',response.session_token);

        $location.url('/');

      }

    }).error(function(){

      $('.login-dialog').addClass('pending');
      $('.spinner').removeClass('out');

      showErrorMessage();

    });

  }

  $('img:not(.loaded)').load(function(){ $(this).addClass('loaded'); });

}]);
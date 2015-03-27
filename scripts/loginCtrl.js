app.controller('login',['$scope','$route','textData','$http','$mdDialog','$location','$rootScope',
function($scope,$route,textData,$http,$mdDialog,$location,$rootScope){

  if(window.session_token || localStorage.getItem('session_token')) return $location.url('/');
  
  $('.login-dialog').css('opacity','1');
  var text = textData.data;

  $('body > .spinner').addClass('out');
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
    $('body > .spinner').removeClass('out');

    $http.post('api',{
      request: 'login',
      username: $scope.username,
      password: $scope.password
    }).success(function(response){

      if(response.message != "success"){

        $('.login-dialog').removeClass('pending');
        $('body > .spinner').addClass('out');
        showErrorMessage();

      } else {

        window.session_token = response.session_token;
        $scope.rememberMe &&
        localStorage.setItem('session_token',response.session_token);
        
        window.ROUTE_FLAG = true;
        $location.url($rootScope.redirAfterLogin || '/');

      }

    }).error(function(){

      $('.login-dialog').addClass('pending');
      $('body > .spinner').removeClass('out');

      showErrorMessage();

    });

  }

  $('img:not(.loaded)').load(function(){ $(this).addClass('loaded'); });

}]);
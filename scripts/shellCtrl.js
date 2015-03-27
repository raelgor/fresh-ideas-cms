app.controller('init',['$location','$scope','dataFactory','$mdDialog','$rootScope','$templateCache','$http','$routeParams','$timeout','$route',
function($location,$scope,dataFactory,$mdDialog,$rootScope,$templateCache,$http,$routeParams,$timeout,$route){  
  
  var path = [];
  
  $location.$$path.split('/').forEach(function(l){ l && path.push(l); });
  
  $rootScope.initialized = 1;
  $rootScope.initialPath = path[0] == "settings" ? '/' : $location.path();  
  
  function init(response){
    
    $scope.shellText = $rootScope.shellText = response.data;
    $('.spinner').addClass('out');
    $('.main-menu,.module-view').removeClass('unborn');
    
    $rootScope.customRoute(path,path,true);
    
  }
  
  $scope.tooltipToggle = false;

  $scope.logout = function(ev){
    
    $scope.tooltipLogoutToggle = false;
    var confirm = $mdDialog.confirm()
      .title($scope.shellText.logout)
      .content($scope.shellText.logoutQuestion)
      .ariaLabel('Logout')
      .ok($scope.shellText.yes) 
      .cancel($scope.shellText.cancel)
      .targetEvent(ev);
      
    $mdDialog.show(confirm).then(function(){
      
      setTimeout(dataFactory.logout,0);
      session_token = undefined;
      localStorage.removeItem('session_token');
      $location.url('/login');
      
    });
    
  }

  $rootScope.showSettings = function(ev) {
    $rootScope.locationBeforeSettings = $location.url();
    ev && (window.NO_ROUTE = true) && $location.path("/settings");
    
    $scope.tooltipToggle = false;
    $mdDialog.show({
      controller: 'settingsCtrl',
      template: $templateCache.get('templates/settings.html'),
      targetEvent: ev || undefined,
    });
    
    return true;
    
  };

  $scope.showSettings = $rootScope.showSettings;

  window.session_token = window.session_token ||
                         localStorage.getItem('session_token');

  if(!session_token){ 
    $rootScope.redirAfterLogin = $location.$$url;
    return $location.url('/login');
  }
  $('img:not(.loaded)').load(function(){ $(this).addClass('loaded'); });

  dataFactory.auth().then(function(response){

    if(response.data.message == "valid_token"){

      window.session_lang = response.data.lang;
      $rootScope.settings = response.data.settings;
      dataFactory.getShellText().then(init);

    } else {
      window.session_token = false;
      localStorage.removeItem('session_token');
      $rootScope.redirAfterLogin = $location.$$url;
      $location.url('/login');
    }

  });

}]);
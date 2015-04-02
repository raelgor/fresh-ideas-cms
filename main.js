var app = angular.module('FreshCMS',['ngRoute','ngAnimate','ngMaterial']); 

app.config(['$routeProvider','$locationProvider','$mdThemingProvider',
function ($routeProvider,$locationProvider,$mdThemingProvider) {

  $mdThemingProvider.theme('default')
    .primaryPalette('blue-grey')
    .accentPalette('blue');

  $locationProvider.html5Mode(true);

  $routeProvider
    .when('/login', {
      controller: 'login',
      templateUrl: 'templates/login.html',
      resolve: {
        textData: function(dataFactory){
          return dataFactory.getLoginText();
        }
      }
    })
    .otherwise({
      controller: 'init',
      templateUrl: 'templates/appshell.html'
    });

}]);

app.run(['$location','$rootScope','$route','$templateCache','$http','$timeout',
function($location,$rootScope,$route,$templateCache,$http,$timeout){ 
  
  $(window).click(function(e){
    
    if($(e.target).is('.md-dialog-container')){
      $location.path($rootScope.initialPath);
    }
    
  });
  
  $rootScope.$on('$locationChangeSuccess', function(event,newUrl,oldUrl) {
    
    var base = $('base').attr('href'),
        newPath = [], 
        oldPath = [],
        oldPathStr, newPathStr;
        
    newPathStr = newUrl.split(base)[1];
    oldPathStr = oldUrl.split(base)[1];
    
    newPathStr.split('/').forEach(function(l){ if(l) newPath.push(l); });
    oldPathStr.split('/').forEach(function(l){ if(l) oldPath.push(l); });
    
    if( $rootScope.initialized && ($location.path()!="/login" || window.session_token) && !window.ROUTE_FLAG){
      
      $route.current = $rootScope.initialPath; 
      if(!window.NO_ROUTE) $rootScope.customRoute(newPath,oldPath);
      
    } 
    
    window.ROUTE_FLAG = false; 
    window.NO_ROUTE = false;
    
  });
  
  function cache(src){
    $http.get(src).then(function(r){ $templateCache.put(src, r.data); });
  }
  
  cache('templates/settings.html');
  cache('templates/general.html');
  cache('templates/users.html');
  cache('templates/modules.html');
  cache('templates/list.html');
  cache('templates/edituser.html');
  
  $rootScope.customRoute = function(newPath,oldPath,justLoaded){
    
    if(!window.session_token) return;
    
    if(justLoaded){ 
      switch(newPath[0]){
        case 'settings': $timeout(function(){ $rootScope.showSettings(); },0); break;
      }
    }
    
    if(newPath[0] == "settings"){
      switch(newPath[1]){
        case undefined : $rootScope.settingsTabIndex = 0; break; 
        case 'users': $rootScope.settingsTabIndex = 1; break; 
        case 'modules': $rootScope.settingsTabIndex = 2; break;
      }
    }
    
    if(!justLoaded){ 
      if(oldPath[0] == "settings" && newPath[0] != "settings") $rootScope.hideSettings(); 
      if(oldPath[0] != "settings" && newPath[0] == "settings") $rootScope.showSettings();
    } 
    
  };
  
}]);

// Checkbox touch double trigger bug
$(window).bind('touchend',function(e){ return false;  });
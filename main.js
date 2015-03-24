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

app.run(['$location','$rootScope','$route','$templateCache','$http',
function($location,$rootScope,$route,$templateCache,$http){
  
  $(window).click(function(e){
    
    if($(e.target).is('.md-dialog-container')){
      $location.path($rootScope.initialPath);
    }
    
  });
  
  $rootScope.$on('$locationChangeSuccess', function(event,newUrl,oldUrl) {
    
    if( $rootScope.initialized && $location.path()!="/login" && !window.ROUTE_FLAG){ 
      $route.current = $rootScope.initialPath;
    } 
    
    window.ROUTE_FLAG = false; 
    
  });
  
  function cache(src){
    $http.get(src).then(function(r){ $templateCache.put(src, r.data); });
  }
  
  cache('templates/settings.html');
  cache('templates/general.html');
  cache('templates/users.html');
  cache('templates/modules.html');
  
}]);

// Checkbox touch double trigger bug
$(window).bind('touchend',function(e){ return false;  });
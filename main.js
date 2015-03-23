var app = angular.module('FreshCMS',['ngRoute','ngAnimate','ngMaterial']);

app.config(['$routeProvider','$locationProvider',
function ($routeProvider,$locationProvider) {

  $locationProvider.html5Mode(true);

  $routeProvider
    .when('/', {
      controller: 'init',
      templateUrl: 'templates/appshell.html'
    })
    .when('/login', {
      controller: 'login',
      templateUrl: 'templates/login.html',
      resolve: {
        textData: function(dataFactory){
          return dataFactory.getLoginText();
        }
      }
    })
    .otherwise({ redirectTo: '/' });

}]);

app.controller('init',['$location','$scope',function($location,$scope){

  !localStorage.getItem('session_token') &&
  !window.session_token &&
  $location.url('/login');

}]);

app.factory('dataFactory',['$http',function($http){

  var factory = {

    getLoginText: function(){

      var promise = $http({
         method: 'POST',
         url: 'api',
         data: { request: 'login-text' }
      });

      promise.success(function(response){ return response.data; });

      return promise;

    }

  }

  return factory;

}])

$(window).bind('touchend',function(e){return false;  })
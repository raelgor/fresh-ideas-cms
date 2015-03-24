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

    },

    auth: function(){

      var promise = $http({
         method: 'POST',
         url: 'api',
         data: { request: 'auth', session_token: window.session_token }
      });

      promise.success(function(response){ return response.data; });

      return promise;

    },

    getShellText: function(){

      var promise = $http({
         method: 'POST',
         url: 'api',
         data: { request: 'shell-text', lang: window.session_lang }
      });

      promise.success(function(response){ return response.data; });

      return promise;

    },
    
    logout: function(){
      
      var promise = $http({
         method: 'POST',
         url: 'api',
         data: { request: 'logout', session_token: window.session_token }
      });

      promise.success(function(response){ return response.data; });

      return promise;
      
    }

  };

  return factory;

}]);
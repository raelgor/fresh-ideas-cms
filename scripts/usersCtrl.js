app.controller('usersCtrl',['$scope','$http','$rootScope',
function($scope,$http,$rootScope){
  
  var text = $rootScope.shellText;
  $scope.listFields = [text.name,text.username,text.email];
    
  $http({
    method: 'POST',
    url: 'api',
    data: { request: "cms-users", session_token: window.session_token }
  }).then(function(response){
    
    $rootScope.userData = jQuery.extend(true,{},response.data.users);
    $scope.fieldData = response.data.users;
    
  });
  
}]);
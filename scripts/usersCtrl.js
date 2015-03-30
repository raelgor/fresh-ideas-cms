app.controller('usersCtrl',['$scope','$http','$rootScope','$mdDialog',
function($scope,$http,$rootScope,$mdDialog){
  
  var text = $rootScope.shellText;
  
  $scope.userLevels = $rootScope.userLevels;
  $scope.listFields = [text.fullName,text.username,text.email,text.role];
  $scope.v = {
    selectAll:false,
    template:'templates/list.html',
    changes: false
  };
  
  $scope.selectionCount = function(){
    var fieldData = $scope.fieldData || [];
    return fieldData.filter(function(e){ return e._selected; }).length ? false : true;
  };
  
  $scope.selectionChange = function(){ console.log('lilchange');
    
    var trues = $scope.fieldData.filter(function(e){ return e._selected; }).length,
        total = $scope.fieldData.length;
        
    $scope.v.selectAll = trues == total ? true : false;
    
  };
  
  $scope.editUser = function(index){
    $scope.v.targetedUser = jQuery.extend(true,{},$scope.fieldData[index]);
    $scope.v.tarUserIndex = index;
    $scope.v.template = 'templates/edituser.html';
    $scope.v.changes = isNaN(index) ? true : false;
  };
  
  $scope.back = function(){
    $scope.v.template = 'templates/list.html';
  };
  
  $scope.selectAllChange = function(){ console.log('change');
    
    $scope.fieldData.forEach(function(e){
      e._selected = $scope.v.selectAll;
    });
    
  };
    
  $http({
    method: 'POST',
    url: 'api',
    data: { 
      request: "cms-users", 
      session_token: window.session_token,
      from: 0
    }
  }).then(function(response){
    
    $rootScope.userData = jQuery.extend(true,{},response.data.users);
    $scope.fieldData = response.data.users;
    
  });
  
}]);
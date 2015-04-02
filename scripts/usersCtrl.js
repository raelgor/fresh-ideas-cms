app.controller('usersCtrl',['$scope','$http','$rootScope','$mdToast',
function($scope,$http,$rootScope,$mdToast){
  
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
  
  $scope.massDelete = function(){
    
     var fieldData = $scope.fieldData || [],
         indexes   = [];
    
    for(var i = 0; i < fieldData.length; i++){
      if(fieldData[i]._selected) indexes.push(i);
    }
    
    $scope.deleteUser(indexes);
    
  };
  
  $scope.isNaN = window.isNaN;
  $scope.selectionChange = function(){
    
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
  
  $scope.selectAllChange = function(){
    
    $scope.fieldData.forEach(function(e){
      e._selected = $scope.v.selectAll;
    });
    
  };
  
  $scope.toastPosition = {
    bottom: false,
    top: true,
    left: false,
    right: true
  };
  
  $scope.getToastPosition = function() {
    return Object.keys($scope.toastPosition)
      .filter(function(pos) { return $scope.toastPosition[pos]; })
      .join(' ');
  };
  
  $scope.saveUser = function(){
    
    $('.settings-dialog md-tabs').addClass('dark iron');
    $('.settings-dialog .spinner').removeClass('out');
    
    $http({
      method: 'POST',
      url: 'api',
      data: { 
          request: "update-users", 
          session_token: window.session_token,
          user: $scope.v.targetedUser
        }
    }).then(function(response){
      
      $('.settings-dialog md-tabs').removeClass('dark iron');
      $('.settings-dialog .spinner').addClass('out');
      
      var user  = jQuery.extend(true,{},response.data.user),
          index = $scope.v.tarUserIndex;
          
      delete $scope.v.targetedUser.password;
      
      if(!isNaN(index)){ 
        
        $rootScope.userData[index] = jQuery.extend(true,{},$scope.v.targetedUser); 
        $scope.fieldData[index]    = jQuery.extend(true,{},$scope.v.targetedUser); 
        
      } else { 
        
        $rootScope.userData.push(user); 
        $scope.fieldData.push(jQuery.extend(true,{},user));
        $scope.back();
      
      }
      
      $scope.v.changes = false;
      
      var toast = $mdToast.simple()
            .content($rootScope.shellText.savedSuccess)
            .action($rootScope.shellText.ok)
            .highlightAction(false)
            .position($scope.getToastPosition());
            
      $mdToast.show(toast);
      
    });
    
  };
  
  $scope.deleteUser = function(index,deleteByEdit){
    
    var ids = [];
    
    if(index.constructor === Array){
      index.forEach(function(i){
        ids.push($rootScope.userData[i].id);
      });
    } else {
      ids.push($rootScope.userData[index].id);
    }
    
    $rootScope.settingsConfirm({
      title: text.userDeleteConfirmQuestion,
      message: text.userDeleteConfirmMessage,
      callback: function(){
        
        $('.settings-dialog md-tabs').addClass('dark iron');
        $('.settings-dialog .spinner').removeClass('out');
        
        $http({
          method: 'POST',
          url: 'api',
          data: { 
            request: "delete-cms-users", 
            session_token: window.session_token,
            ids: ids
          }
        }).then(function(response){
          
          $('.settings-dialog md-tabs').removeClass('dark iron');
          $('.settings-dialog .spinner').addClass('out');
          
          ids.forEach(function(id){
            
            for(var i = 0; i < $rootScope.userData.length; i++){
              
              if($rootScope.userData[i].id == id){
                $rootScope.userData.splice(i,1);
                $scope.fieldData.splice(i,1); 
                break;
              }
              
            }
            
            if(deleteByEdit) $scope.back();
            
            var r = $scope.fieldData.filter(function(e){ return e._selected; });
            if(!r.length) $scope.v.selectAll = false;
            
            var toast = $mdToast.simple()
                  .content($rootScope.shellText.savedSuccess)
                  .action($rootScope.shellText.ok)
                  .highlightAction(false)
                  .position($scope.getToastPosition());
                  
            $mdToast.show(toast);
            
          });
          
        });
        
      }
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
    
    $rootScope.userData = jQuery.extend(true,[],response.data.users);
    $scope.fieldData = response.data.users;
    
  });
  
}]);
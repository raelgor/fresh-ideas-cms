app.controller('generalCtrl',['$scope','$rootScope','dataFactory','$mdToast',
function($scope,$rootScope,dataFactory,$mdToast){
    
  $('.settings-dialog .spinner').addClass('out');
  
  $scope.generalChanges = false;
  $scope.enableSave = function(){
    
    $scope.generalChanges = true;
    
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
    
  $scope.settings = jQuery.extend(true,{},$rootScope.settings);
  
  $scope.settings[0].value = +$scope.settings[0].value ? true : false;
  
  $scope.saveChanges = function(){
    
    $scope.settings[0].value = $scope.settings[0].value ? 1 : 0;
    $('[ng-controller="generalCtrl"]').addClass('dark iron');
    $('.settings-dialog .spinner').removeClass('out');
    dataFactory.saveGeneralSettings($scope.settings).then(function(){
      
      $('[ng-controller="generalCtrl"]').removeClass('dark iron');
      $('.settings-dialog .spinner').addClass('out');
      
      $scope.generalChanges = false;
      $rootScope.settings = jQuery.extend(true,{},$scope.settings); 
      $scope.settings[0].value = +$scope.settings[0].value ? true : false;
      
      var toast = $mdToast.simple()
            .content($rootScope.shellText.savedSuccess)
            .action($rootScope.shellText.ok)
            .highlightAction(false)
            .position($scope.getToastPosition());
            
      $mdToast.show(toast);
      
    });
    
  };
    
}]);
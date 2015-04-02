app.controller('settingsCtrl',['$scope','$mdDialog','$rootScope','$templateCache','$location',
function($scope,$mdDialog,$rootScope,$templateCache,$location){

  var text = $scope.text = $rootScope.shellText,
      tabs = [

    { title: text.general , content: 'templates/general.html' },
    { title: text.users   , content: 'templates/users.html' },
    { title: text.modules , content: 'templates/modules.html' }

  ];

  $scope.tabs = tabs;
  $scope.selectedIndex = $rootScope.settingsTabIndex;
  
  $scope.$watch(function(scope) { return scope.selectedIndex; },handleTabs);
  
  $scope.$watch(function(){ return $rootScope.settingsTabIndex; },handleTabs);
  
  function handleTabs(newValue,oldValue){
    $rootScope.settingsTabIndex = newValue;
    $scope.selectedIndex = newValue;
    switch(newValue){
      case 0: $location.url('/settings'); break;
      case 1: $location.url('/settings/users'); break;
      case 2: $location.url('/settings/modules'); break;
    }
  }
  
  $rootScope.hideSettings = function(){
    $mdDialog.hide();
    return true;
  }; 

  $scope.confirm = {};

  $rootScope.settingsConfirm = function(options){
    
    $scope.confirm = options;
    $('.settings-confirm-dialog').addClass('visible').find('button:first').focus();
    
  };
  
  $scope.settingsConfirmCallback = function(){
    
    $('.settings-confirm-dialog').removeClass('visible');
    $scope.confirm.callback();
    
  };
  
  $rootScope.settingsConfirmClose = $scope.settingsConfirmClose = function(){
    $('.settings-confirm-dialog').removeClass('visible');
  };

}]);
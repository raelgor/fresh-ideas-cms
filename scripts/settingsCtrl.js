app.controller('settingsCtrl',['$scope','$mdDialog','$rootScope','$templateCache',
function($scope,$mdDialog,$rootScope,$templateCache){

  var text = $scope.text = $rootScope.shellText;

  var tabs = [

    { title: text.general , content: 'templates/general.html' },
    { title: text.users   , content: 'templates/users.html' },
    { title: text.modules , content: 'templates/modules.html' }

  ];

  $scope.tabs = tabs;
  $scope.settings = {
    SITE_NAME: 'fi',
    SITE_URL: 'fi.eu'
  };
  
  $scope.generalChanges = true;

}]);
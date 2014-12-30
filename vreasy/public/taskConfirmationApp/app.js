angular.module('taskConfirmationApp',  ['ui.router', 'ngResource'])
.config(function($stateProvider, $urlRouterProvider, $locationProvider) {
    // Use hashtags in URL
    $locationProvider.html5Mode(false);

    $urlRouterProvider.otherwise("/");
    $stateProvider
    .state('index', {
      url: "/",
      templateUrl: "/taskConfirmationApp/templates/index.html",
      controller: 'TaskCtrl'
    });
})
.factory('Task', function($resource) {
    return $resource('/task/:id?format=json',
        {id:'@id'},
        {
            'get': {method:'GET'},
            'save': {method: 'PUT'},
            'create': {method: 'POST'},
            'query':  {method:'GET', isArray:true},
            'remove': {method:'DELETE'},
            'delete': {method:'DELETE'}
        }
    );
})
.controller('TaskCtrl', function($scope, Task) {
    $scope.tasks = Task.query();
});

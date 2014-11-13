'use strict';

var controllers = angular.module('app.controllers', []);

controllers.controller('MainCtrl', ['$scope', '$location', function($scope, $location) {
  return $scope.isSpecificPage = function () {
    var path;
    //noinspection CommaExpressionJS
    return path = $location.path(), _.contains(["/login"], path)
  };
}]);

controllers.controller('navigation', ['$scope', '$location', 'loginService','$idle', '$keepalive', '$modal', function($scope, $location, loginService, $idle, $keepalive, $modal) {

    $scope.active = function(path) {
        return path === $location.path();
    };
    
    $scope.logout = function() {
        loginService.logout();
        $location.path('/login');
    };

    /*
    * code for automatic logout after 5 minutes
    */

      closeModals();
      $idle.watch();
      $scope.started = true;

      function closeModals() {
        if ($scope.warning) {
          $scope.warning.close();
          $scope.warning = null;
        }

        if ($scope.timedout) {
          $scope.timedout.close();
          $scope.timedout = null;
        }
      }

      $scope.$on('$idleStart', function() {
        closeModals();

        $scope.warning = $modal.open({
          templateUrl: 'warning-dialog.html',
          windowClass: 'modal-danger'
        });
      });

      $scope.$on('$idleEnd', function() {
        closeModals();
      });

      $scope.$on('$idleTimeout', function() {
        closeModals();
        loginService.logout();
        $location.path('/login');
        $scope.timedout = $modal.open({
          templateUrl: 'timedout-dialog.html',
          windowClass: 'modal-danger'
        });
      });

}]);

controllers.controller('login', ['$scope', '$location', '$http','loginService', function($scope, $location, $http, loginService) {
    $scope.input = {};
    $scope.login = function() {
      loginService.login($scope.input,$scope);
    };

}]);

controllers.controller('about', ['$scope', '$location', '$http', function($scope, $location, $http) {


}]);

controllers.controller('clientDetails', ['$scope','$routeParams', '$location', '$http', function($scope, $routeParams, $location, $http) {
  var data = {};
  data.client_id = $routeParams.id;
  $http.post("api/client/clientsDetailsById", data).success(function(data) {
    $scope.client_details = data.data;
    console.log(data.data);
  });

  $scope.deleteClient = function(client_id) {
    console.log(client_id);
    var r = confirm("Are You sure You!");
    if (r == true) {
      // var client_to_delete = $scope.client_details[idx];
      // var post_data = {};
      // post_data.method = 'delete_client_by_id';
      // post_data.client_id = client_to_delete.client_id
      // $http.post('api/account/deleteClient', post_data)
      //   .success(function(data) { 

      //     });
      $scope.hideClient = true;
    } else {}
  };
}]);

controllers.controller('fullClientList', ['$scope', '$location', '$http', function($scope, $location, $http) {

  $http.get("api/client/clients").success(function(data) {
    $scope.client_details = data.data;
    console.log(data.data);
  });

  $scope.deleteClient = function(idx) {
    console.log(idx);
    var r = confirm("Are You sure You!");
    if (r == true) {
      var client_to_delete = $scope.client_details[idx];
      var post_data = {};
      post_data.method = 'delete_client_by_id';
      post_data.service_id = client_to_delete.client_id
      $http.post('api/account/deleteClient', post_data)
        .success(function(data) { 

          });
      $scope.client_details.splice(idx, 1);
    } else {}
  };


}]);

controllers.controller('dashboard', ['$scope', '$location', '$http', function($scope, $location, $http) {
    $http.get("api/client/clients").success(function(data) {
        $scope.clients = data.data;
        $scope.selectedDateAsNumber = new Date();
    });

    $scope.search = function (searchForm) {
      var date = new Date();
      var fromDate = new Date(searchForm.fromDate);
      fromDate.setMinutes(date.getMinutes() - date.getTimezoneOffset());
      console.log(fromDate.toJSON().slice(0, 10));

      var untillDate = new Date(searchForm.untilDate);
      untillDate.setMinutes(date.getMinutes() - date.getTimezoneOffset());
      console.log(untillDate.toJSON().slice(0, 10));      
    }
}]);

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

controllers.controller('clientDetails', ['$scope','$routeParams', '$location', '$http', 'clientService', function($scope, $routeParams, $location, $http, clientService) {
  var data = {};
  data.client_id = $routeParams.id;
  $http.post("api/client/clientsDetailsById", data).success(function(data) {
    $scope.client_details = data.data;
  });

  $scope.disableClient = function(client_id) {
    this.client_details.active = 0;
    var data = {};
    data.client_id = client_id;
    clientService.disableClient(data).then(function(response){
      console.log(response.data);
    });
  }

  $scope.enableClient = function(client_id) {
    this.client_details.active = 1;
    var data = {};
    data.client_id = client_id;
    clientService.enableClient(data).then(function(response){
      console.log(response.data);
    });
  }


  $scope.deleteClient = function(client_id, first_name, last_name) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var post_data = {};
        post_data.client_id = client_id;
        post_data.client_firstname = first_name;
        post_data.client_lastname = last_name;
        $http.post('api/client/deleteClient', post_data)
          .success(function(data) { 
            if (data.status) {
              $scope.hideClient = true;
            }
          });
      } else {}
    });
  };

  $scope.extendDays = function(client_id, days) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var post_data = {};
        post_data.client_id = client_id
        post_data.days = days;
        $http.post('api/client/extendTrial', post_data)
          .success(function(data) {
            if (data.status == true) {
              toastr.success(data.message);
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }
            } else {
              toastr.error('Some Error Occured');
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }               
            }
          });
      } else {}
    });
  };
}]);

controllers.controller('searchByDate', ['$scope','$routeParams', '$location', '$http', 'searchService', function($scope, $routeParams, $location, $http, searchService) {
  $scope.client_details = searchService.get();
  $scope.deleteClient = function(idx) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var client_to_delete = $scope.client_details[idx];
        var post_data = {};
        post_data.client_id = client_to_delete.client_id;
        post_data.client_firstname = client_to_delete.first_name;
        post_data.client_lastname = client_to_delete.last_name;
        $http.post('api/client/deleteClient', post_data)
          .success(function(data) { 

          });
        $scope.client_details.splice(idx, 1);
      } else {}
    }); 
  };

  $scope.extendDays = function(client_id, days) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var post_data = {};
        post_data.client_id = client_id
        post_data.days = days;
        $http.post('api/client/extendTrial', post_data)
          .success(function(data) {
            if (data.status == true) {
              toastr.success(data.message);
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }
            } else {
              toastr.error('Some Error Occured');
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }               
            }
          });
      } else {}
    });
  };
}]);

controllers.controller('fullClientList', ['$scope', '$location', '$http', 'clientService', function($scope, $location, $http, clientService) {
$scope.visible = true;
  $http.get("api/client/clients").success(function(data) {
    $scope.client_details = data.data;
  });

  $scope.disableClient = function(client_id) {
    this.client.active = 0;
    var data = {};
    data.client_id = client_id;
    clientService.disableClient(data).then(function(response){
      if (response.data) {
        $scope.client_details.active == 0;
      };
    });
  }

  $scope.enableClient = function(client_id) {
    this.client.active = 1;
    var data = {};
    data.client_id = client_id;
    clientService.enableClient(data).then(function(response){
      console.log(response.data);
    });
  }

  $scope.deleteClient = function(idx) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var client_to_delete = $scope.client_details[idx];
        var post_data = {};
        post_data.client_id = client_to_delete.client_id
        post_data.client_firstname = client_to_delete.first_name;
        post_data.client_lastname = client_to_delete.last_name;
        $http.post('api/client/deleteClient', post_data)
          .success(function(data) { 

          });
        $scope.client_details.splice(idx, 1);
      } else {}
    }); 
  };

  $scope.extendDays = function(client_id, days) {
    bootbox.confirm("Are you sure?", function(result) {
      if (result) {
        var post_data = {};
        post_data.client_id = client_id
        post_data.days = days;
        $http.post('api/client/extendTrial', post_data)
          .success(function(data) {
            if (data.status == true) {
              toastr.success(data.message);
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }
            } else {
              toastr.error('Some Error Occured');
              toastr.options = {
                "closeButton": false,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }               
            }
          });
      } else {}
    });
  };
}]);

controllers.controller('dashboard', ['$scope', '$location', '$http', 'searchService', function($scope, $location, $http, searchService) {
  $http.get("api/client/clients").success(function(data) {
      $scope.clients = data.data;
      $scope.selectedDateAsNumber = new Date();
  });

  $scope.search = function (searchForm) {
    var date = new Date();
    var fromDate = new Date(searchForm.fromDate);
    var untillDate = new Date(searchForm.untilDate);

    fromDate.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    untillDate.setMinutes(date.getMinutes() - date.getTimezoneOffset());

    var post_data = {};
    post_data.fromDate = fromDate.toJSON().slice(0, 10);
    post_data.untilDate = untillDate.toJSON().slice(0, 10);

    $http.post('api/client/inactiveClientsByDate', post_data)
      .success(function(data) {
        searchService.set(data.data); 
        $location.path('/search-by-date');
    });
  }
}]);

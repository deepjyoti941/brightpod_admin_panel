'use strict';

angular.module('app.services', [])
		.factory("loginService", ["$http", "$location", "sessionService",function ($http, $location, sessionService) {
	  return{
	    login:function(data,scope){
	      var $promise = $http.post('api/account/login',data); //send data to user.php
	      $promise.then(function(msg){
	        var uid = msg.data.session_id;
	        if(uid){
              toastr.success(msg.data.message);
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
	          //scope.msgtxt='Correct information';
	          sessionService.set('uid',uid);
	          $location.path('/dashboard');
	        }        
	        
	        else  {
              toastr.error(msg.data.message);
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
	          scope.msgtxt='incorrect information';
	          $location.path('/login');
	        }          
	      });
	    },
	    logout:function(){
	      sessionService.destroy('uid');
	      $location.path('/');
	    },
	    islogged:function(){
	      var $checkSessionServer = $http.post('api/account/checkLoggedIn');
	      return $checkSessionServer;
	      /*
	      if(sessionService.get('user')) return true;
	      else return false;
	      */
	    }
	  }
	}]).factory("sessionService", ["$http",function ($http) {
	  return{
	    set:function(key,value){
	      return sessionStorage.setItem(key,value);
	    },
	    get:function(key){
	      return sessionStorage.getItem(key);
	    },
	    destroy:function(key){
	      $http.post('api/account/logout');
	      return sessionStorage.removeItem(key);
	    }
	  };
	}]);

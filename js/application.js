'use strict';

angular.module('app', ['app.filters', 'app.services', 'app.directives', 'app.controllers', 'ngRoute','ngIdle', 'ui.bootstrap', 'reCAPTCHA', 'mgcrea.ngStrap.datepicker', 'angular-loading-bar']).
config(['$routeProvider', '$httpProvider', 'reCAPTCHAProvider','$idleProvider', '$keepaliveProvider', function($routeProvider, $httpProvider, reCAPTCHAProvider, $idleProvider, $keepaliveProvider) {

    reCAPTCHAProvider.setPublicKey('6Lfjqv0SAAAAAJonUK1DqryGpkrbeOfaUMYlpWhb');

    reCAPTCHAProvider.setOptions({
        theme: 'clean',
        custom_theme_widget: 'recaptcha_widget'
    });

    $idleProvider.idleDuration(5000);
    $idleProvider.warningDuration(5);
    $keepaliveProvider.interval(10);

    $routeProvider
    .when('/dashboard', {
        controller: 'dashboard',
        templateUrl: 'partials/dashboard.html'
    }).when('/about', {
        controller: 'about',
        templateUrl: 'partials/about.html'
    }).when('/full-client-list', {
        controller: 'fullClientList',
        templateUrl: 'partials/full-client-list.html'
    }).when('/search-by-date', {
        controller: 'searchByDate',
        templateUrl: 'partials/full-client-list.html'
    }).when('/clients/:id', {
        controller: 'clientDetails',
        templateUrl: 'partials/client-details.html'
    }).when('/login', {
        controller: 'login',
        templateUrl: 'partials/login.html'
    }).otherwise({
        redirectTo: '/login'
    });

    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    var param = function(obj) {
        var query = '',
            name, value, fullSubName, subName, subValue, innerObj, i;

        for (name in obj) {
            value = obj[name];

            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null) query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
    
}]).run(function($rootScope, $location, loginService){
  var routespermission=['/dashboard', '/full-client-list', '/clients/' ];  //route that require login
  $rootScope.$on('$routeChangeStart', function(){
    if( routespermission.indexOf($location.path()) !=-1) {
      var connected = loginService.islogged();
      connected.then(function(msg){
        if(!msg.data) $location.path('/login');
      });
    }
  });
});

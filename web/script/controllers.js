'use strict';

/**
 * Retrieves the corpus data. 
 */
var bdictApp = angular.module('bdictApp', []);

bdictApp.controller('CorpusListCtrl', function($scope, $http) {

  $http.get('script/corpus.json').success(function(data) {
    $scope.docs = data;
  });

  $scope.orderProp = 'id';
});

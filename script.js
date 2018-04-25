var dominoApp = angular.module('dominoApp', []);

dominoApp.controller('dominoController' , function ($scope, $http) {
	$scope.home = "Domino game";

	$scope.getRequest = function () {
		console.log("I've been pressed!");
		$http.get("http://test.luciflor.ro/main.php")
			.then(function successCallback(response){
				$scope.response = response;
			}, function errorCallback(response){
				console.log("Unable to perform get request");
			});
	};

});
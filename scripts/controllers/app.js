var app = angular.module('cityApp', [
	'ngRoute',
	'CityCtrls',
	'ui.router',
	'ui.bootstrap'
]);

app.config(function($routeProvider, $stateProvider) {

	$stateProvider

	// setup an abstract state for the tabs directive
	.state('example2', {
		url: '/',
		views: {
			'tabContent': {
				templateUrl: '/city-suggestion/templates/city-suggestion.html',
				controller: 'CityCtrl'
			}
		}
	})
});

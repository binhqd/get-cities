var CityCtrls = angular.module('CityCtrls', []);

CityCtrls.controller('CityCtrl', function($scope, $rootScope, $http,
		$stateParams) {
	$scope.part = '';
	if (!!$stateParams.part) {
		$scope.part = $stateParams.part;
	}

	$scope.global = {
		countries : [],
		states : [],
		errors : [],
		cities : [],
		country : null,
		state : null,
		city : null,
		useSuggestion : false
	};
	$scope.model = {
		country : null,
		state : null,
		city : null
	}
	$scope.state = {
		loadingStates : false
	};

	var req = {
		method : 'GET',
		url : './countries.php'
	}

	$http(req).success(function(res) {
		$scope.global.countries = res;

	}).error(function(xhr) {
		console.log(xhr);
	});

	$scope.selectCountry = function(country) {
		$scope.global.country = country;
		$scope.model.country = country.countryName;
		
		$scope.model.state = '';
		$scope.model.city = '';
		$scope.loadStates(country);
	}

	

	$scope.loadStates = function(country) {
		$scope.state.loadingStates = true;
		var req = {
			method : 'GET',
			url : './states.php?countryID=' + country.countryID
		}

		$http(req).success(function(res) {
			$scope.state.loadingStates = false;
			$scope.global.states = res;

			$scope.global.states.push({
				stateID : -1,
				stateName : "Other"
			});
		}).error(function(xhr) {
			console.log(xhr);
		});
	}
	
	$scope.useGoogleSuggest = function() {
		// Load Google Suggestion
			$scope.global.useSuggestion = true;
			
			$("#geocomplete").geocomplete().bind(
					"geocode:result",
					function(event, result) {
						$.ajax({
							url : './request.php?q=' + encodeURIComponent($('#geocomplete').val()),
							dataType : 'json',
							success : function(res) {
								var country = res.country.name;
								var state = !!res.state ? res.state.name : '';
								var city = '';
								if (!!res.city) {
									city = res.city.name;
								} else if (!!res.province) {
									city = res.province.name;
								} else if (!!res.community) {
									city = res.community.name;
								} else if (!!res.town) {
									city = res.town.name;
								} else if (!!res.district) {
									city = res.district.name;
								}
								
								$scope.$apply(function() {
									$scope.model.country = country;
									$scope.model.state = state;
									$scope.model.city = city;
									
									// load states correspond with current country
									
									// load 
									
									$scope.global.useSuggestion = false;
									$("#geocomplete").val('');
								});
								
								
							},
							error : function(xhr) {
								console.log(xhr);
							}
						});
					});
	}
	$scope.selectState = function(state) {
		$scope.model.city = '';
		
		if (state.stateID != -1) {
			$scope.global.state = state;
    		$scope.model.state = state.stateName;
    
    		$scope.loadCities(state);
		} else {
			$scope.useGoogleSuggest();
		}
		
	}

	$scope.loadCities = function(state) {
		$scope.state.loadingStates = true;
		var req = {
			method : 'GET',
			url : './cities.php?stateID=' + state.stateID
		}

		$http(req).success(function(res) {
			// $scope.state.loadingStates = false;
			$scope.global.cities = res;

			$scope.global.cities.push({
				cityID : -1,
				cityName : "Other"
			});
		}).error(function(xhr) {
			console.log(xhr);
		});
	}

	$scope.selectCity = function(city) {
		if (city.cityID != -1) {
			$scope.global.city = city;
			$scope.model.city = city.cityName;
		} else {
			// Load Google Suggestion
			$scope.useGoogleSuggest();
		}

		// $scope.loadCities(state);
	}
});
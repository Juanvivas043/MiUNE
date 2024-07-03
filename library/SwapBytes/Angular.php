<?php

class SwapBytes_Angular {

	/**
     * Asigna a un div con intención de notfificar al usuario cuando hay
     * comunicación mediante AJAX.
     *
     * @param string $Id
     * @return string
     */
    public function getLoading() {

        $js = '	app.config(function ($httpProvider, $provide) {
				    $provide.factory(\'httpInterceptor\', function ($q, $rootScope) {
				        return {
				            \'request\': function (config) {
				            	$("#loading").show();
				                $rootScope.$broadcast(\'httpRequest\', config);
				                return config || $q.when(config);
				            },
				            \'response\': function (response) {
				            	$("#loading").hide();
				                $rootScope.$broadcast(\'httpResponse\', response);
				                return response || $q.when(response);
				            },
				            \'requestError\': function (rejection) {
				            	$("#loading").hide();
				                $rootScope.$broadcast(\'httpRequestError\', rejection);
				                return $q.reject(rejection);
				            },
				            \'responseError\': function (rejection) {
				            	$("#loading").hide();
				                $rootScope.$broadcast(\'httpResponseError\', rejection);
				                return $q.reject(rejection);
				            }
				        };
				    });
				    $httpProvider.interceptors.push(\'httpInterceptor\');
				});';

        return $js;
    }

}

?>
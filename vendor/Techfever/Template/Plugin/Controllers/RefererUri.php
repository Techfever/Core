<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class RefererUri extends AbstractPlugin {
	public function __invoke() {
		$http = '/' . $_SERVER ['SERVER_NAME'];
		if (isset ( $_SERVER ['SCRIPT_NAME'] )) {
			$httpraw = explode ( '/', $_SERVER ['SCRIPT_NAME'] );
			unset ( $httpraw [(sizeof ( $httpraw ) - 1)] );
			if (is_array ( $httpraw ) && count ( $httpraw ) > 0) {
				foreach ( $httpraw as $httpvalue ) {
					if (! empty ( $httpvalue )) {
						$http .= '/' . $httpvalue;
					}
				}
			}
		}
		
		$uri = null;
		if (isset ( $_SERVER ['HTTP_REFERER'] )) {
			$uriraw = explode ( '/', $_SERVER ['HTTP_REFERER'] );
			unset ( $uriraw [0] );
			if (is_array ( $uriraw ) && count ( $uriraw ) > 0) {
				foreach ( $uriraw as $urivalue ) {
					if (! empty ( $urivalue )) {
						$uri .= '/' . $urivalue;
					}
				}
			}
		}
		$path = str_replace ( $http, '', $uri );
		if (substr ( $path, - 1 ) === '/') {
			$path = substr ( $path, 0, (strlen ( $path ) - 1) );
		}
		if (substr ( $path, 0, 1 ) === '/') {
			$path = substr ( $path, 1 );
		}
		return $path;
	}
}

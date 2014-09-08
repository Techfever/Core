<?php

namespace Techfever\Log;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;

/**
 * Logger.
 */
class LoggerServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		// Configure the logger
		$config = $serviceLocator->get ( 'Config' );
		$logConfig = isset ( $config ['log'] ) ? $config ['log'] : array ();
		if (array_key_exists ( 'writers', $logConfig )) {
			foreach ( $logConfig ['writers'] as $writers_key => $writers_value ) {
				if (array_key_exists ( 'options', $writers_value ) && array_key_exists ( 'stream', $writers_value ['options'] )) {
					$logConfig ['writers'] [$writers_key] ['options'] ['stream'] = sprintf ( $writers_value ['options'] ['stream'], date ( "Ymd", time () ) );
				}
			}
		}
		$logger = new Logger ( $logConfig );
		return $logger;
	}
}

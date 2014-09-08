<?php

namespace Techfever\Session;

use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Techfever\Exception;
use Techfever\Functions\DirConvert;

class Session {
	
	/**
	 *
	 * @var Manager
	 *
	 */
	private $_manager = null;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$DirConvert = new DirConvert ( CORE_PATH . '/config/autoload/session.global.php' );
		$configfile = $DirConvert->__toString ();
		if (! file_exists ( $configfile )) {
			throw new Exception\RuntimeException ( sprintf ( 'Session "%s" file not exist', $configfile ) );
		}
		$config = include $configfile;
		if (! is_array ( $config )) {
			throw new Exception\RuntimeException ( sprintf ( 'Session "%s" file configuration invalid', $configfile ) );
		}
		
		$sessionConfig = null;
		$sessionStorage = null;
		$session = null;
		$sessionSaveHandler = null;
		
		if (array_key_exists ( 'session', $config ) && isset ( $config ['session'] )) {
			$session = $config ['session'];
		}
		
		if (array_key_exists ( 'config', $session ) && isset ( $session ['config'] )) {
			$class = isset ( $session ['config'] ['class'] ) ? $session ['config'] ['class'] : 'Zend\Session\Config\SessionConfig';
			$options = isset ( $session ['config'] ['options'] ) ? $session ['config'] ['options'] : array ();
			$sessionConfig = new $class ();
			$sessionConfig->setOptions ( $options );
		}
		
		if (array_key_exists ( 'storage', $session ) && isset ( $session ['storage'] )) {
			$class = $session ['storage'];
			$sessionStorage = new $class ();
		}
		
		if (array_key_exists ( 'save_handler', $session ) && array_key_exists ( 'name', $session ['save_handler'] )) {
			if ($session ['save_handler'] ['name'] == 'db') {
				$DirConvert = new DirConvert ( CORE_PATH . '/config/autoload/db.global.php' );
				$configfile = $DirConvert->__toString ();
				if (! file_exists ( $configfile )) {
					throw new Exception\RuntimeException ( sprintf ( 'Session "%s" file not exist', $configfile ) );
				}
				$config = include $configfile;
				if (! is_array ( $config )) {
					throw new Exception\RuntimeException ( sprintf ( 'Session "%s" file configuration invalid', $configfile ) );
				}
				
				$sessionAdapter = new Adapter ( $config ['db'] );
				$tableGateway = new TableGateway ( 'session', $sessionAdapter );
				$sessionSaveHandler = new DbTableGateway ( $tableGateway, new DbTableGatewayOptions () );
			}
		}
		$sessionManager = new SessionManager ( $sessionConfig, $sessionStorage, $sessionSaveHandler );
		if (array_key_exists ( 'validator', $session ) && ($session ['validator'])) {
			$chain = $sessionManager->getValidatorChain ();
			foreach ( $session ['validator'] as $validator ) {
				$validator = new $validator ();
				$chain->attach ( 'session.validate', array (
						$validator,
						'isValid' 
				) );
			}
		}
		
		Container::setDefaultManager ( $sessionManager );
		
		$sessionManager->start ();
		
		$this->_manager = $sessionManager;
	}
	public function getContainer($key) {
		return new Container ( $key );
	}
	public function getManager() {
		return $this->_manager;
	}
	public function initialize() {
		$container = new Container ( 'initialized' );
		if (! isset ( $container->init )) {
			$this->getManager ()->regenerateId ( true );
			$container->init = 1;
		}
	}
}

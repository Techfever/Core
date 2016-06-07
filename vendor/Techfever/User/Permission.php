<?php

namespace Techfever\User;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;
use Techfever\Parameter\Parameter;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class Permission {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array (
			'servicelocator' => null,
			'user_id' => null,
			'user_rank_id' => null,
			'permission_as' => null,
			'action' => null,
			'controller' => null,
			'route' => null,
			'request' => null,
			'response' => null 
	);
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	
	/**
	 *
	 * @var Session\Session\Container
	 */
	private $container = null;
	
	/**
	 *
	 * @var Data
	 *
	 */
	private $_user_permission_data = null;
	
	/**
	 *
	 * @var controller
	 *
	 */
	private $_controller_data = null;
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		
		$options = array_merge ( $this->options, $options );
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$this->setOptions ( $options );
		
		$Session = $this->getSession ();
		$this->container = $Session->getContainer ( 'UserPermission' );
	}
	
	/**
	 * function call handler
	 *
	 * @param string $function
	 *        	Function name to call
	 * @param array $args
	 *        	Function arguments
	 * @return mixed
	 * @throws Exception\RuntimeException
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		if (is_object ( $this->generalobject )) {
			$obj = $this->generalobject;
			if (method_exists ( $obj, $name )) {
				if (is_array ( $arguments ) && count ( $arguments ) > 0) {
					return call_user_func_array ( array (
							$obj,
							$name 
					), $arguments );
				} else {
					return call_user_func ( array (
							$obj,
							$name 
					) );
				}
			}
		}
		return null;
	}
	
	/**
	 * getContainer()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session\Container
	 */
	public function getContainer() {
		if ($this->container == null) {
			throw new Exception\RuntimeException ( 'Container has not been set or configured.' );
		}
		return $this->container;
	}
	
	/**
	 * Get User Permission
	 *
	 * @return void
	 *
	 */
	public function getUserPermission() {
		if (! is_array ( $this->_user_permission_data ) || count ( $this->_user_permission_data ) < 1) {
			$data = array ();
			if ($this->getContainer ()->offsetExists ( 'Initialized' ) && $this->getContainer ()->offsetGet ( 'Initialized' ) == True) {
				$data = $this->getContainer ()->offsetGet ( 'Data' );
			} else {
				$user_id = $this->getOption ( 'user_id' );
				$user_rank_id = $this->getOption ( 'user_rank_id' );
				$permission_as = $this->getOption ( 'permission_as' );
				$this->getContainer ()->offsetSet ( 'Initialized', False );
				$data = array ();
				$visitormoduledata = array ();
				$visitordata = array ();
				$rankdata = array ();
				$userdata = array ();
				
				$QVisitorModule = $this->getDatabase ();
				$QVisitorModule->select ();
				$QVisitorModule->columns ( array (
						'mcid' => 'module_controllers_id',
						'controller' => 'module_controllers_alias' 
				) );
				$QVisitorModule->from ( array (
						'mc' => 'module_controllers' 
				) );
				$QVisitorModule->where ( array (
						'mc.module_controllers_visitor = 1' 
				) );
				$QVisitorModule->order ( array (
						'mc.module_controllers_alias ASC' 
				) );
				$QVisitorModule->execute ();
				if ($QVisitorModule->hasResult ()) {
					while ( $QVisitorModule->valid () ) {
						$rawdata = $QVisitorModule->current ();
						$rawdata ['upid'] = 0;
						$rawdata ['uprid'] = 0;
						$visitormoduledata [$rawdata ['mcid']] = $rawdata;
						$QVisitorModule->next ();
					}
				}
				
				$QVisitor = $this->getDatabase ();
				$QVisitor->select ();
				$QVisitor->columns ( array (
						'upid' => 'user_permission_id',
						'uprid' => 'user_rank_id' 
				) );
				$QVisitor->from ( array (
						'up' => 'user_permission' 
				) );
				$QVisitor->join ( array (
						'mc' => 'module_controllers' 
				), 'up.module_controllers_id = mc.module_controllers_id', array (
						'mcid' => 'module_controllers_id',
						'controller' => 'module_controllers_alias' 
				) );
				/*
				 * $QVisitor->join ( array ( 'mca' => 'module_controllers_action' ), 'up.module_controllers_action_id = mca.module_controllers_action_id', array ( 'mcaid' => 'module_controllers_action_id', 'action' => 'module_controllers_action_param' ) );
				 */
				$QVisitor->where ( array (
						'up.user_rank_id = ' . $user_rank_id 
				) );
				$QVisitor->order ( array (
						'mc.module_controllers_alias ASC' 
				// 'mca.module_controllers_action_param ASC'
								) );
				$QVisitor->execute ();
				if ($QVisitor->hasResult ()) {
					while ( $QVisitor->valid () ) {
						$rawdata = $QVisitor->current ();
						$visitordata [$rawdata ['mcid']] = $rawdata;
						$QVisitor->next ();
					}
				}
				
				if ($permission_as == "2" && $user_rank_id > 1) {
					$QRank = $this->getDatabase ();
					$QRank->select ();
					$QRank->columns ( array (
							'upid' => 'user_permission_id',
							'uprid' => 'user_rank_id' 
					) );
					$QRank->from ( array (
							'up' => 'user_permission' 
					) );
					$QRank->join ( array (
							'mc' => 'module_controllers' 
					), 'up.module_controllers_id = mc.module_controllers_id', array (
							'mcid' => 'module_controllers_id',
							'controller' => 'module_controllers_alias' 
					) );
					/*
					 * $QRank->join ( array ( 'mca' => 'module_controllers_action' ), 'up.module_controllers_action_id = mca.module_controllers_action_id', array ( 'mcaid' => 'module_controllers_action_id', 'action' => 'module_controllers_action_param' ) );
					 */
					$QRank->where ( array (
							'up.user_rank_id = ' . $user_rank_id 
					) );
					$QRank->order ( array (
							'mc.module_controllers_alias ASC' 
					// 'mca.module_controllers_action_param ASC'
										) );
					$QRank->execute ();
					if ($QRank->hasResult ()) {
						while ( $QRank->valid () ) {
							$rawdata = $QRank->current ();
							$rankdata [$rawdata ['mcid']] = $rawdata;
							$QRank->next ();
						}
					}
				} elseif ($permission_as == "3" && $user_id > 0) {
					$QUser = $this->getDatabase ();
					$QUser->select ();
					$QUser->columns ( array (
							'upid' => 'user_permission_id',
							'uprid' => 'user_rank_id' 
					) );
					$QUser->from ( array (
							'up' => 'user_permission' 
					) );
					$QUser->join ( array (
							'mc' => 'module_controllers' 
					), 'up.module_controllers_id = mc.module_controllers_id', array (
							'mcid' => 'module_controllers_id',
							'controller' => 'module_controllers_alias' 
					) );
					/*
					 * $QUser->join ( array ( 'mca' => 'module_controllers_action' ), 'up.module_controllers_action_id = mca.module_controllers_action_id', array ( 'mcaid' => 'module_controllers_action_id', 'action' => 'module_controllers_action_param' ) );
					 */
					$QUser->where ( array (
							'up.user_access_id = ' . $user_id 
					) );
					$QUser->order ( array (
							'mc.module_controllers_alias ASC' 
					// 'mca.module_controllers_action_param ASC'
										) );
					$QUser->execute ();
					if ($QUser->hasResult ()) {
						while ( $QUser->valid () ) {
							$rawdata = $QUser->current ();
							$userdata [$rawdata ['mcid']] = $rawdata;
							$QUser->next ();
						}
					}
				}
				
				$data = array_merge ( $data, $visitormoduledata );
				$data = array_merge ( $data, $visitordata );
				$data = array_merge ( $data, $rankdata );
				$data = array_merge ( $data, $userdata );
				if (is_array ( $data ) && count ( $data ) > 0) {
					$this->getContainer ()->offsetSet ( 'Data', $data );
					$this->getContainer ()->offsetSet ( 'Initialized', True );
				}
			}
			$this->_user_permission_data = $data;
		}
		return $this->_user_permission_data;
	}
	
	/**
	 * Get Controller
	 *
	 * @return void
	 *
	 */
	public function getControllerPermission() {
		if (! is_array ( $this->_controller_data ) || count ( $this->_controller_data ) < 1) {
			$data = array ();
			$QControllers = $this->getDatabase ();
			$QControllers->select ();
			$QControllers->columns ( array (
					'mcid' => 'module_controllers_id',
					'controller' => 'module_controllers_alias' 
			) );
			$QControllers->from ( array (
					'mc' => 'module_controllers' 
			) );
			$QControllers->join ( array (
					'mca' => 'module_controllers_action' 
			), 'mca.module_controllers_id = mc.module_controllers_id', array (
					'mcaid' => 'module_controllers_action_id',
					'action' => 'module_controllers_action_param' 
			) );
			$QControllers->where ( array (
					'mc.module_controllers_visitor = 0' 
			) );
			$QControllers->order ( array (
					'mc.module_controllers_alias ASC',
					'mca.module_controllers_action_param ASC' 
			) );
			$QControllers->execute ();
			if ($QControllers->hasResult ()) {
				while ( $QControllers->valid () ) {
					$rawdata = $QControllers->current ();
					$controller = strtolower ( $rawdata ['controller'] );
					$controller = $this->convertToUnderscore ( $controller, "\\" );
					$data [$controller] = $rawdata ['mcid'];
					$QControllers->next ();
				}
			}
			$this->_controller_data = $data;
		}
		return $this->_controller_data;
	}
	
	/**
	 * Valid User Permission
	 *
	 * @return void
	 *
	 */
	public function isAllow($controller, $action) {
		$status = false;
		$data = $this->getUserPermission ();
		$permission_as = $this->getOption ( 'permission_as' );
		if ($permission_as == "1") {
			$status = true;
		} elseif (is_array ( $data ) && count ( $data ) > 0) {
			
			$ToUnderscore = new ToUnderscore ( '\\' );
			$controller = $ToUnderscore->filter ( $controller );
			foreach ( $data as $permission ) {
				$permission_controller = $ToUnderscore->filter ( strtolower ( $permission ['controller'] ) );
				//$permission_action = $ToUnderscore->filter ( strtolower ( $permission ['action'] ) );
				if (strtolower ( $controller ) === $permission_controller) {
					$status = true;
					break;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Get ID by Key
	 *
	 * @return void
	 *
	 */
	public function getControllerIDByKey($key) {
		$data = $this->getControllerPermission ();
		$id = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_key => $data_value ) {
				if ($data_key == $key) {
					$id = $data_value;
				}
			}
		}
		return $id;
	}
	
	/**
	 * Update User Permission
	 *
	 * @return void
	 *
	 */
	public function updateUserPermission($id = null, $data = null) {
		if (! empty ( $id ) && count ( $data ) > 0) {
			$PermissionAsParameter = new Parameter ( array (
					'key' => 'user_access_permission_as',
					'servicelocator' => $this->getServiceLocator () 
			) );
			$permissionas = $PermissionAsParameter->getValueByKey ( $data ['user_access_permission_as'] );
			
			$DPermission = $this->getDatabase ();
			$DPermission->delete ();
			$DPermission->from ( 'user_permission' );
			$DPermission->where ( array (
					'user_access_id' => $id 
			) );
			$DPermission->execute ();
			
			$UAccess = $this->getDatabase ();
			$UAccess->update ();
			$UAccess->table ( 'user_access' );
			$UAccess->set ( array (
					'user_permission_as' => $permissionas 
			) );
			$UAccess->where ( array (
					'user_access_id = "' . $id . '"' 
			) );
			$UAccess->execute ();
			
			if ($permissionas == "1" || $permissionas == "2") {
				return true;
			} elseif ($permissionas == "3") {
				$permissionraw = $data ['user_access_permission'];
				if (count ( $permissionraw ) > 0) {
					$permission = array ();
					foreach ( $permissionraw as $permission_key => $permission_data ) {
						if ($permission_data == 'allow') {
							$permission [] = array (
									'user_rank_id' => 0,
									'user_access_id' => ( int ) $id,
									'module_controllers_id' => $this->getControllerIDByKey ( $permission_key ),
									'module_controllers_action_id' => "0",
									'user_permission_created_date' => $data ['log_created_date'],
									'user_permission_modified_date' => $data ['log_modified_date'],
									'user_permission_created_by' => $data ['log_created_by'],
									'user_permission_modified_by' => $data ['log_modified_by'] 
							);
						}
					}
					if (count ( $permission ) > 0) {
						$IPermission = $this->getDatabase ();
						$IPermission->insert ();
						$IPermission->into ( 'user_permission' );
						$IPermission->columns ( array (
								'user_rank_id',
								'user_access_id',
								'module_controllers_id',
								'module_controllers_action_id',
								'user_permission_created_date',
								'user_permission_modified_date',
								'user_permission_created_by',
								'user_permission_modified_by' 
						) );
						$IPermission->values ( $permission, 'multiple' );
						$IPermission->execute ();
						if ($IPermission->affectedRows ()) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}
}

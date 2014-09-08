<?php

namespace Techfever\User;

use Zend\Crypt\Password\Bcrypt;
use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Access {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array ();
	
	/**
	 *
	 * @var Variables
	 */
	private $variables = array ();
	
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
	private $data = null;
	
	/**
	 *
	 * @var Valid
	 *
	 */
	private $isLogin = false;
	
	/**
	 *
	 * @var Valid
	 *
	 */
	private $isLoginWallet = false;
	
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
		
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$this->setOptions ( $options );
		
		$Session = $this->getSession ();
		$this->container = $Session->getContainer ( 'UserLogin' );
		
		$data = null;
		if ($this->getContainer ()->offsetExists ( 'Initialized' ) && $this->getContainer ()->offsetGet ( 'Initialized' ) == True) {
			$data = $this->getContainer ()->offsetGet ( 'Data' );
		}
		$this->setData ( $data );
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
	 * Set User Logout
	 *
	 * @return void
	 *
	 */
	public function setLogout() {
		$this->getContainer ()->offsetSet ( 'Data', null );
		$this->getContainer ()->offsetSet ( 'Initialized', False );
	}
	
	/**
	 * Set User Login
	 *
	 * @return void
	 *
	 */
	public function setLogin($id = null) {
		if (! empty ( $id ) && is_numeric ( $id ) && $id > 0) {
			$Session = $this->getSession ();
			$PermissionContainer = $Session->getContainer ( 'UserPermission' );
			$PermissionContainer->offsetSet ( 'Data', null );
			$PermissionContainer->offsetSet ( 'Initialized', False );
			
			$this->getContainer ()->offsetSet ( 'Initialized', False );
			
			$DBUser = $this->getDatabase ();
			$DBUser->select ();
			$DBUser->columns ( array (
					'id' => 'user_access_id',
					'profile_id' => 'user_profile_id',
					'security_retrieve' => 'user_access_security_retrieve',
					'password_retrieve' => 'user_access_password_retrieve',
					'last_login_date' => 'user_access_last_login_date',
					'last_login_ip' => 'user_access_last_login_ip',
					'no_login' => 'user_access_no_of_login',
					'created_date' => 'user_access_created_date',
					'modified_date' => 'user_access_modified_date',
					'activated_date' => 'user_access_activated_date',
					'expired_date' => 'user_access_expired_date',
					'created_by' => 'user_access_created_by',
					'username' => 'user_access_username',
					'rank_id' => 'user_rank_id',
					'permission_as' => 'user_permission_as',
					'profile_update' => 'user_profile_update_status',
					'address_update' => 'user_address_update_status',
					'bank_update' => 'user_bank_update_status',
					'password_update' => 'user_password_update_status',
					'security_update' => 'user_security_update_status' 
			) );
			$DBUser->from ( array (
					'ua' => 'user_access' 
			) );
			$DBUser->join ( array (
					'up' => 'user_profile' 
			), 'up.user_profile_id = ua.user_profile_id', array (
					'firstname' => 'user_profile_firstname',
					'lastname' => 'user_profile_lastname',
					'gender' => 'user_profile_gender',
					'address_id' => 'user_address_id',
					'bank_id' => 'user_bank_id',
					'profile_created_date' => 'user_profile_created_date',
					'profile_modified_date' => 'user_profile_modified_date',
					'profile_dob' => 'user_profile_dob' 
			), 'right' );
			$DBUser->join ( array (
					'ur' => 'user_rank' 
			), 'ur.user_rank_id = ua.user_rank_id', array (
					'rank_key' => 'user_rank_key',
					'is_admin' => 'user_rank_is_admin' 
			), 'right' );
			$DBUser->join ( array (
					'urg' => 'user_rank_group' 
			), 'urg.user_rank_group_id = ur.user_rank_group_id', array (
					'rank_group_key' => 'user_rank_group_key',
					'rank_group_id' => 'user_rank_group_id' 
			), 'right' );
			$DBUser->where ( array (
					'ua.user_access_id = ' . $id,
					'ua.user_access_status = 1',
					'ua.user_access_delete_status = 0' 
			) );
			$DBUser->limit ( 1 );
			$DBUser->setCacheName ( 'user_access_data' );
			$DBUser->execute ();
			if ($DBUser->hasResult ()) {
				$data = $DBUser->current ();
				$data ['is_admin'] = ($data ['is_admin'] == 1 ? True : False);
				
				$data ['fullname'] = $data ['firstname'] . (! empty ( $data ['lastname'] ) ? ' ' . $data ['lastname'] : null);
				$data ['rank_key'] = 'text_rank_' . $data ['rank_key'];
				$data ['rank_group_key'] = 'text_rank_group_' . $data ['rank_group_key'];
				
				$datetime = new \DateTime ( $data ['activated_date'] );
				$data ['activated_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['created_date'] );
				$data ['created_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['expired_date'] );
				$data ['expired_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['last_login_date'] );
				$data ['last_login_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['modified_date'] );
				$data ['modified_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['profile_created_date'] );
				$data ['profile_created_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['profile_modified_date'] );
				$data ['profile_modified_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				
				$datetime = new \DateTime ( $data ['profile_dob'] );
				$data ['profile_dob_format'] = $datetime->format ( 'd-m-Y' );
				
				$this->getContainer ()->offsetSet ( 'Data', $data );
				$this->setData ( $data );
			}
		}
	}
	
	/**
	 * Set User Logout Wallet
	 *
	 * @return void
	 *
	 */
	public function setLogoutWallet() {
		$data = $this->getContainer ()->offsetGet ( 'Data' );
		$data ['is_login_wallet'] = False;
		$this->getContainer ()->offsetSet ( 'Data', $data );
		$this->isLoginWallet = False;
	}
	
	/**
	 * Set User Login Wallet
	 *
	 * @return void
	 *
	 */
	public function setLoginWallet($id = null) {
		$data = $this->getContainer ()->offsetGet ( 'Data' );
		$data ['is_login_wallet'] = True;
		$this->getContainer ()->offsetSet ( 'Data', $data );
		$this->isLoginWallet = True;
	}
	
	/**
	 * Set User Data
	 *
	 * @return void
	 *
	 */
	public function setData($data) {
		$this->isLogin = False;
		$this->isLoginWallet = False;
		$this->getContainer ()->offsetSet ( 'Initialized', False );
		if (is_array ( $data )) {
			$this->data = $data;
			$this->isLogin = True;
			if (isset ( $this->data ['is_login_wallet'] ) && $this->data ['is_login_wallet']) {
				$this->isLoginWallet = True;
			}
			$this->getContainer ()->offsetSet ( 'Initialized', True );
		}
	}
	
	/**
	 * Get User Data
	 *
	 * @return int
	 *
	 */
	public function getData($key) {
		if (array_key_exists ( $key, $this->data )) {
			return $this->data [$key];
		}
		return false;
	}
	
	/**
	 * Is Valid?
	 *
	 * @return boolean
	 *
	 */
	public function isLogin() {
		if ($this->isLogin) {
			return true;
		}
		return false;
	}
	
	/**
	 * Is Valid?
	 *
	 * @return boolean
	 *
	 */
	public function isLoginWallet() {
		if ($this->isLoginWallet) {
			return true;
		}
		return false;
	}
	
	/**
	 * isAdminUser
	 *
	 * @return boolean
	 *
	 */
	public function isAdminUser() {
		if (isset ( $this->data ['is_admin'] )) {
			return ( boolean ) $this->data ['is_admin'];
		}
		return False;
	}
	
	/**
	 * isPermissionAs
	 *
	 * @return boolean
	 *
	 */
	public function isPermissionAs() {
		if (isset ( $this->data ['permission_as'] )) {
			return ( int ) $this->data ['permission_as'];
		}
		return False;
	}
	
	/**
	 * Get User ID
	 *
	 * @return int
	 *
	 */
	public function getID() {
		if (isset ( $this->data ['id'] )) {
			return ( int ) $this->data ['id'];
		}
		return False;
	}
	
	/**
	 * Get User Profile ID
	 *
	 * @return int
	 *
	 */
	public function getProfileID() {
		if (isset ( $this->data ['profile_id'] )) {
			return ( int ) $this->data ['profile_id'];
		}
		return False;
	}
	
	/**
	 * Get User Address ID
	 *
	 * @return int
	 *
	 */
	public function getAddressID() {
		if (isset ( $this->data ['address_id'] )) {
			return ( int ) $this->data ['address_id'];
		}
		return False;
	}
	
	/**
	 * Get User Bank ID
	 *
	 * @return int
	 *
	 */
	public function getBankID() {
		if (isset ( $this->data ['bank_id'] )) {
			return ( int ) $this->data ['bank_id'];
		}
		return False;
	}
	
	/**
	 * Get User Password Retrieve
	 *
	 * @return string
	 *
	 */
	public function getPasswordRetrieve() {
		if (isset ( $this->data ['password_retrieve'] )) {
			return ( string ) $this->data ['password_retrieve'];
		}
		return False;
	}
	
	/**
	 * Get User Security Retrieve
	 *
	 * @return string
	 *
	 */
	public function getSecurityRetrieve() {
		if (isset ( $this->data ['security_retrieve'] )) {
			return ( string ) $this->data ['security_retrieve'];
		}
		return False;
	}
	
	/**
	 * Get Last Login Date
	 *
	 * @return string
	 *
	 */
	public function getLastLogin() {
		if (isset ( $this->data ['last_login'] )) {
			return ( string ) $this->data ['last_login'];
		}
		return False;
	}
	
	/**
	 * Get Last Login IP
	 *
	 * @return string
	 *
	 */
	public function getLastLoginIP() {
		if (isset ( $this->data ['last_login_ip'] )) {
			return ( string ) $this->data ['last_login_ip'];
		}
		return False;
	}
	
	/**
	 * Get Last Login Date
	 *
	 * @return int
	 *
	 */
	public function getNoLogin() {
		if (isset ( $this->data ['no_login'] )) {
			return ( int ) $this->data ['no_login'];
		}
		return False;
	}
	
	/**
	 * Get Created Date
	 *
	 * @return string
	 *
	 */
	public function getCreatedDate() {
		if (isset ( $this->data ['created_date_format'] )) {
			return ( string ) $this->data ['created_date_format'];
		}
		return False;
	}
	
	/**
	 * Get Created By
	 *
	 * @return string
	 *
	 */
	public function getCreatedBy() {
		if (isset ( $this->data ['created_by'] )) {
			return ( string ) $this->data ['created_by'];
		}
		return False;
	}
	
	/**
	 * Get Activated Date
	 *
	 * @return string
	 *
	 */
	public function getActivatedDate() {
		if (isset ( $this->data ['activated_date_format'] )) {
			return ( string ) $this->data ['activated_date_format'];
		}
		return False;
	}
	
	/**
	 * Get Expired Date
	 *
	 * @return string
	 *
	 */
	public function getExpiredDate() {
		if (isset ( $this->data ['expired_date_format'] )) {
			return ( string ) $this->data ['expired_date_format'];
		}
		return False;
	}
	
	/**
	 * Get Username
	 *
	 * @return string
	 *
	 */
	public function getUsername() {
		if (isset ( $this->data ['username'] )) {
			return ( string ) $this->data ['username'];
		}
		return ( string ) 'Unknown';
	}
	
	/**
	 * Get Fullname
	 *
	 * @return string
	 *
	 */
	public function getFullname() {
		if (isset ( $this->data ['fullname'] )) {
			return ( string ) $this->data ['fullname'];
		}
		return False;
	}
	
	/**
	 * Get Firstname
	 *
	 * @return string
	 *
	 */
	public function getFirstname() {
		if (isset ( $this->data ['firstname'] )) {
			return ( string ) $this->data ['firstname'];
		}
		return False;
	}
	
	/**
	 * Get Lastname
	 *
	 * @return string
	 *
	 */
	public function getLastname() {
		if (isset ( $this->data ['lastname'] )) {
			return ( string ) $this->data ['lastname'];
		}
		return False;
	}
	
	/**
	 * Get Gender
	 *
	 * @return string
	 *
	 */
	public function getGender() {
		if (isset ( $this->data ['gender'] )) {
			return ( string ) $this->data ['gender'];
		}
		return False;
	}
	
	/**
	 * Get Rank Group
	 *
	 * @return string
	 *
	 */
	public function getRankGroup() {
		if (isset ( $this->data ['rank_group_key'] )) {
			return ( string ) $this->data ['rank_group_key'];
		}
		return False;
	}
	
	/**
	 * Get Rank Group ID
	 *
	 * @return int
	 *
	 */
	public function getRankGroupID() {
		if (isset ( $this->data ['rank_group_id'] )) {
			return ( int ) $this->data ['rank_group_id'];
		}
		return False;
	}
	
	/**
	 * Get Rank
	 *
	 * @return string
	 *
	 */
	public function getRank() {
		if (isset ( $this->data ['rank_key'] )) {
			return ( string ) $this->data ['rank_key'];
		}
		return False;
	}
	
	/**
	 * Get Rank ID
	 *
	 * @return int
	 *
	 */
	public function getRankID() {
		if (isset ( $this->data ['rank_id'] )) {
			return ( int ) $this->data ['rank_id'];
		}
		return False;
	}
	
	/**
	 * Get Profile Update
	 *
	 * @return Boolean
	 *
	 */
	public function getProfileUpdate() {
		if (isset ( $this->data ['profile_update'] )) {
			return (( int ) $this->data ['profile_update'] == 1 ? True : False);
		}
		return False;
	}
	
	/**
	 * Get Address Update
	 *
	 * @return Boolean
	 *
	 */
	public function getAddressUpdate() {
		if (isset ( $this->data ['address_update'] )) {
			return (( int ) $this->data ['address_update'] == 1 ? True : False);
		}
		return False;
	}
	
	/**
	 * Get Bank Update
	 *
	 * @return Boolean
	 *
	 */
	public function getBankUpdate() {
		if (isset ( $this->data ['bank_update'] )) {
			return (( int ) $this->data ['bank_update'] == 1 ? True : False);
		}
		return False;
	}
	
	/**
	 * Get Password Update
	 *
	 * @return Boolean
	 *
	 */
	public function getPasswordUpdate() {
		if (isset ( $this->data ['password_update'] )) {
			return (( int ) $this->data ['password_update'] == 1 ? True : False);
		}
		return False;
	}
	
	/**
	 * Get Security Update
	 *
	 * @return Boolean
	 *
	 */
	public function getSecurityUpdate() {
		if (isset ( $this->data ['security_update'] )) {
			return (( int ) $this->data ['security_update'] ? True : False);
		}
		return False;
	}
	
	/**
	 * Verify Password
	 *
	 * @return Boolean
	 *
	 */
	public function verifyPassword($username, $password) {
		$status = false;
		
		$Bcrypt = new Bcrypt ( array (
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST 
		) );
		$password = $Bcrypt->create ( $password );
		
		$DBVerify = $this->getDatabase ();
		$DBVerify->select ();
		$DBVerify->columns ( array (
				'id' => 'user_access_id' 
		) );
		$DBVerify->from ( array (
				'ua' => 'user_access' 
		) );
		$DBVerify->where ( array (
				'UPPER(ua.user_access_username) = "' . strtoupper ( $username ) . '"',
				'ua.user_access_password = "' . $password . '"',
				'ua.user_access_status = 1',
				'ua.user_access_delete_status = 0' 
		) );
		$DBVerify->limit ( 1 );
		$DBVerify->execute ();
		if ($DBVerify->hasResult ()) {
			$status = true;
		}
		return $status;
	}
	
	/**
	 * Verify Security
	 *
	 * @return Boolean
	 *
	 */
	public function verifySecurity($username, $password) {
		$status = false;
		
		$Bcrypt = new Bcrypt ( array (
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST 
		) );
		$password = $Bcrypt->create ( $password );
		
		$DBVerify = $this->getDatabase ();
		$DBVerify->select ();
		$DBVerify->columns ( array (
				'id' => 'user_access_id' 
		) );
		$DBVerify->from ( array (
				'ua' => 'user_access' 
		) );
		$DBVerify->where ( array (
				'UPPER(ua.user_access_username) = "' . strtoupper ( $username ) . '"',
				'ua.user_access_password = "' . $password . '"',
				'ua.user_access_status = 1',
				'ua.user_access_delete_status = 0' 
		) );
		$DBVerify->limit ( 1 );
		$DBVerify->execute ();
		if ($DBVerify->hasResult ()) {
			$status = true;
		}
		return $status;
	}
}

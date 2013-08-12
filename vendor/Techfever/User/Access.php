<?php
namespace Techfever\User;

use Zend\Crypt\Password\Bcrypt;
use Techfever\Database\Database;
use Techfever\Session\Session;
use Techfever\Exception;

class Access {
	/**
	 * @var Database\Database
	 */
	private $database = null;

	/**
	 * @var Session\Session
	 */
	private $session = null;

	/**
	 * @var Session\Session\Container
	 */
	private $container = null;

	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array();

	/**
	 * @var Data
	 **/
	private $data = null;

	/**
	 * @var Valid
	 **/
	private $isLogin = false;

	/**
	 * Constructor
	 */
	public function __construct(Database $database, Session $session) {
		$this->database = $database;
		$this->session = $session;

		$Session = $this->getSession();
		$this->container = $Session->getContainer('UserLogin');

		$data = null;
		if ($this->getContainer()->offsetExists('Initialized') && $this->getContainer()->offsetGet('Initialized') == True) {
			$data = $this->getContainer()->offsetGet('Data');
		}
		$this->setData($data);
	}

	/**
	 * getDatabase()
	 *
	 * @throws Exception\RuntimeException
	 * @return Database\Database
	 */
	public function getDatabase() {
		if ($this->database == null) {
			throw new Exception\RuntimeException('Database has not been set or configured.');
		}
		return clone $this->database;
	}

	/**
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session
	 */
	public function getSession() {
		if ($this->session == null) {
			throw new Exception\RuntimeException('Session has not been set or configured.');
		}
		return $this->session;
	}

	/**
	 * getContainer()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session\Container
	 */
	public function getContainer() {
		if ($this->container == null) {
			throw new Exception\RuntimeException('Container has not been set or configured.');
		}
		return $this->container;
	}

	/**
	 * Set User Logout
	 * 
	 * @return void
	 **/
	public function setLogout() {
		$this->getContainer()->offsetSet('Data', null);
		$this->getContainer()->offsetSet('Initialized', False);
	}

	/**
	 * Set User Login
	 * 
	 * @return void
	 **/
	public function setLogin($id = null) {
		if (!empty($id) && is_numeric($id) && $id > 0) {
			$this->getContainer()->offsetSet('Initialized', False);
			$DBUser = $this->getDatabase();
			$DBUser->select();
			$DBUser
					->columns(
							array(
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
									'profile_update' => 'user_profile_update_status',
									'address_update' => 'user_address_update_status',
									'bank_update' => 'user_bank_update_status',
									'password_update' => 'user_password_update_status',
									'security_update' => 'user_security_update_status'
							));
			$DBUser->from(array(
							'ua' => 'user_access'
					));
			$DBUser
					->join(array(
									'up' => 'user_profile'
							), 'up.user_profile_id = ua.user_profile_id',
							array(
									'firstname' => 'user_profile_firstname',
									'lastname' => 'user_profile_lastname',
									'gender' => 'user_profile_gender',
									'address_id' => 'user_address_id',
									'bank_id' => 'user_bank_id',
									'profile_created_date' => 'user_profile_created_date',
									'profile_modified_date' => 'user_profile_modified_date',
									'profile_dob' => 'user_profile_dob',
									
							), Database::JOIN_RIGHT);
			$DBUser->join(array(
							'ur' => 'user_rank'
					), 'ur.user_rank_id = ua.user_rank_id', array(
							'rank_key' => 'user_rank_key',
					), Database::JOIN_RIGHT);
			$DBUser->join(array(
							'urg' => 'user_rank_group'
					), 'urg.user_rank_group_id = ur.user_rank_group_id', array(
							'rank_group_key' => 'user_rank_group_key',
							'rank_group_id' => 'user_rank_group_id',
					), Database::JOIN_RIGHT);
			$DBUser->where(array(
							'ua.user_access_id = ' . $id,
							'ua.user_access_status = 1',
							'ua.user_access_delete_status = 0',
					));
			$DBUser->limit(1);
			$DBUser->setCacheName('user_access_data');
			$DBUser->execute();
			if ($DBUser->hasResult()) {
				$data = $DBUser->current();
				$data['fullname'] = $data['firstname'] . (!empty($data['lastname']) ? ' ' . $data['lastname'] : null);
				$data['rank_key'] = 'text_rank_' . $data['rank_key'];
				$data['rank_group_key'] = 'text_rank_group_' . $data['rank_group_key'];

				$datetime = new \DateTime($data['activated_date']);
				$data['activated_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['created_date']);
				$data['created_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['expired_date']);
				$data['expired_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['last_login_date']);
				$data['last_login_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['modified_date']);
				$data['modified_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['profile_created_date']);
				$data['profile_created_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['profile_modified_date']);
				$data['profile_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($data['profile_dob']);
				$data['profile_dob_format'] = $datetime->format('d-m-Y');

				$this->getContainer()->offsetSet('Data', $data);
				$this->setData($data);
			}
		}
	}

	/**
	 * Set User Data
	 * 
	 * @return void
	 **/
	public function setData($data) {
		$this->isLogin = false;
		$this->getContainer()->offsetSet('Initialized', False);
		if (is_array($data)) {
			$this->data = $data;
			$this->isLogin = true;
			$this->getContainer()->offsetSet('Initialized', True);
		}
	}

	/**
	 * Get User Data
	 * 
	 * @return int
	 **/
	public function getData($key) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}
		return false;
	}

	/**
	 * Is Valid?
	 * 
	 * @return boolean
	 **/
	public function isLogin() {
		if ($this->isLogin) {
			return true;
		}
		return false;
	}

	/**
	 * Get User ID
	 * 
	 * @return int
	 **/
	public function getID() {
		if (isset($this->data['id'])) {
			return (int) $this->data['id'];
		}
		return False;
	}

	/**
	 * Get User Profile ID
	 * 
	 * @return int
	 **/
	public function getProfileID() {
		if (isset($this->data['profile_id'])) {
			return (int) $this->data['profile_id'];
		}
		return False;
	}

	/**
	 * Get User Address ID
	 * 
	 * @return int
	 **/
	public function getAddressID() {
		if (isset($this->data['address_id'])) {
			return (int) $this->data['address_id'];
		}
		return False;
	}

	/**
	 * Get User Bank ID
	 * 
	 * @return int
	 **/
	public function getBankID() {
		if (isset($this->data['bank_id'])) {
			return (int) $this->data['bank_id'];
		}
		return False;
	}

	/**
	 * Get User Password Retrieve
	 * 
	 * @return string
	 **/
	public function getPasswordRetrieve() {
		if (isset($this->data['password_retrieve'])) {
			return (string) $this->data['password_retrieve'];
		}
		return False;
	}

	/**
	 * Get User Security Retrieve
	 * 
	 * @return string
	 **/
	public function getSecurityRetrieve() {
		if (isset($this->data['security_retrieve'])) {
			return (string) $this->data['security_retrieve'];
		}
		return False;
	}

	/**
	 * Get Last Login Date
	 * 
	 * @return string
	 **/
	public function getLastLogin() {
		if (isset($this->data['last_login'])) {
			return (string) $this->data['last_login'];
		}
		return False;
	}

	/**
	 * Get Last Login IP
	 * 
	 * @return string
	 **/
	public function getLastLoginIP() {
		if (isset($this->data['last_login_ip'])) {
			return (string) $this->data['last_login_ip'];
		}
		return False;
	}

	/**
	 * Get Last Login Date
	 * 
	 * @return int
	 **/
	public function getNoLogin() {
		if (isset($this->data['no_login'])) {
			return (int) $this->data['no_login'];
		}
		return False;
	}

	/**
	 * Get Created Date
	 * 
	 * @return string
	 **/
	public function getCreatedDate() {
		if (isset($this->data['created_date_format'])) {
			return (string) $this->data['created_date_format'];
		}
		return False;
	}

	/**
	 * Get Created By
	 * 
	 * @return string
	 **/
	public function getCreatedBy() {
		if (isset($this->data['created_by'])) {
			return (string) $this->data['created_by'];
		}
		return False;
	}

	/**
	 * Get Activated Date
	 * 
	 * @return string
	 **/
	public function getActivatedDate() {
		if (isset($this->data['activated_date_format'])) {
			return (string) $this->data['activated_date_format'];
		}
		return False;
	}

	/**
	 * Get Expired Date
	 * 
	 * @return string
	 **/
	public function getExpiredDate() {
		if (isset($this->data['expired_date_format'])) {
			return (string) $this->data['expired_date_format'];
		}
		return False;
	}

	/**
	 * Get Username
	 * 
	 * @return string
	 **/
	public function getUsername() {
		if (isset($this->data['username'])) {
			return (string) $this->data['username'];
		}
		return (string) 'Unknown';
	}

	/**
	 * Get Fullname
	 * 
	 * @return string
	 **/
	public function getFullname() {
		if (isset($this->data['fullname'])) {
			return (string) $this->data['fullname'];
		}
		return False;
	}

	/**
	 * Get Firstname
	 * 
	 * @return string
	 **/
	public function getFirstname() {
		if (isset($this->data['firstname'])) {
			return (string) $this->data['firstname'];
		}
		return False;
	}

	/**
	 * Get Lastname
	 * 
	 * @return string
	 **/
	public function getLastname() {
		if (isset($this->data['lastname'])) {
			return (string) $this->data['lastname'];
		}
		return False;
	}

	/**
	 * Get Gender
	 * 
	 * @return string
	 **/
	public function getGender() {
		if (isset($this->data['gender'])) {
			return (string) $this->data['gender'];
		}
		return False;
	}

	/**
	 * Get Rank Group
	 * 
	 * @return string
	 **/
	public function getRankGroup() {
		if (isset($this->data['rank_group_key'])) {
			return (string) $this->data['rank_group_key'];
		}
		return False;
	}

	/**
	 * Get Rank Group ID
	 * 
	 * @return int
	 **/
	public function getRankGroupID() {
		if (isset($this->data['rank_group_id'])) {
			return (int) $this->data['rank_group_id'];
		}
		return False;
	}

	/**
	 * Get Rank 
	 * 
	 * @return string
	 **/
	public function getRank() {
		if (isset($this->data['rank_key'])) {
			return (string) $this->data['rank_key'];
		}
		return False;
	}

	/**
	 * Get Rank  ID
	 * 
	 * @return int
	 **/
	public function getRankID() {
		if (isset($this->data['rank_id'])) {
			return (int) $this->data['rank_id'];
		}
		return False;
	}

	/**
	 * Get Profile Update
	 * 
	 * @return Boolean
	 **/
	public function getProfileUpdate() {
		if (isset($this->data['profile_update'])) {
			return ((int) $this->data['profile_update'] == 1 ? True : False);
		}
		return False;
	}

	/**
	 * Get Address Update
	 * 
	 * @return Boolean
	 **/
	public function getAddressUpdate() {
		if (isset($this->data['address_update'])) {
			return ((int) $this->data['address_update'] == 1 ? True : False);
		}
		return False;
	}

	/**
	 * Get Bank Update
	 * 
	 * @return Boolean
	 **/
	public function getBankUpdate() {
		if (isset($this->data['bank_update'])) {
			return ((int) $this->data['bank_update'] == 1 ? True : False);
		}
		return False;
	}

	/**
	 * Get Password Update
	 * 
	 * @return Boolean
	 **/
	public function getPasswordUpdate() {
		if (isset($this->data['password_update'])) {
			return ((int) $this->data['password_update'] == 1 ? True : False);
		}
		return False;
	}

	/**
	 * Get Security Update
	 * 
	 * @return Boolean
	 **/
	public function getSecurityUpdate() {
		if (isset($this->data['security_update'])) {
			return ((int) $this->data['security_update'] ? True : False);
		}
		return False;
	}

	/**
	 * Verify Password
	 * 
	 * @return Boolean
	 **/
	public function verifyPassword($username, $password) {
		$id = false;

		$Bcrypt = new Bcrypt(array(
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST
		));
		$password = $Bcrypt->create($password);

		$DBVerify = $this->getDatabase();
		$DBVerify->select();
		$DBVerify->columns(array(
						'id' => 'user_access_id'
				));
		$DBVerify->from(array(
						'ua' => 'user_access'
				));
		$DBVerify->where(array(
						'UPPER(ua.user_access_username) = "' . strtoupper($username) . '"',
						'UPPER(ua.user_access_password) = "' . strtoupper($password) . '"',
						'ua.user_access_status = 1',
						'ua.user_access_delete_status = 0',
				));
		$DBVerify->limit(1);
		$DBVerify->execute();
		if ($DBVerify->hasResult()) {
			$data = $DBVerify->current();
			$id = $data['id'];
		}
		return $id;
	}

	/**
	 * Verify Security
	 * 
	 * @return Boolean
	 **/
	public function verifySecurity($username, $password) {
		$id = false;

		$Bcrypt = new Bcrypt(array(
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST
		));
		$password = $Bcrypt->create($password);

		$DBVerify = $this->getDatabase();
		$DBVerify->select();
		$DBVerify->columns(array(
						'id' => 'user_access_id'
				));
		$DBVerify->from(array(
						'ua' => 'user_access'
				));
		$DBVerify->where(array(
						'UPPER(ua.user_access_username) = "' . strtoupper($username) . '"',
						'UPPER(ua.user_access_password) = "' . strtoupper($password) . '"',
						'ua.user_access_status = 1',
						'ua.user_access_delete_status = 0',
				));
		$DBVerify->limit(1);
		$DBVerify->execute();
		if ($DBVerify->hasResult()) {
			$data = $DBVerify->current();
			$id = $data['id'];
		}
		return $id;
	}
}

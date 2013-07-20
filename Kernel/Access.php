<?php
namespace Kernel;

use Zend\Session\Container as SessionContainer;
use Kernel\Database;

class Access {
	/**
	 * @var ID
	 **/
	private $_id = 0;

	/**
	 * @var Profile ID
	 **/
	private $_profile_id = 0;

	/**
	 * @var Address ID
	 **/
	private $_address_id = 0;

	/**
	 * @var Bank ID
	 **/
	private $_bank_id = 0;

	/**
	 * @var Password Retrieve
	 **/
	private $_password_retrieve = null;

	/**
	 * @var SecurityRetrieve
	 **/
	private $_security_retrieve = null;

	/**
	 * @var Last Login Date
	 **/
	private $_last_login = null;

	/**
	 * @var Last Login IP
	 **/
	private $_last_login_ip = null;

	/**
	 * @var No of Login
	 **/
	private $_no_login = null;

	/**
	 * @var Created Date
	 **/
	private $_created_date = null;

	/**
	 * @var Created By
	 **/
	private $_created_by = null;

	/**
	 * @var Activated Date
	 **/
	private $_activated_date = null;

	/**
	 * @var Expired Date
	 **/
	private $_expired_date = null;

	/**
	 * @var Username
	 **/
	private $_username = null;

	/**
	 * @var Fullname
	 **/
	private $_fullname = null;

	/**
	 * @var Firstname
	 **/
	private $_firstname = null;

	/**
	 * @var Lastname
	 **/
	private $_lastname = null;

	/**
	 * @var Gender
	 **/
	private $_gender = null;

	/**
	 * @var Rank Group Key
	 **/
	private $_rank_group_key = null;

	/**
	 * @var Rank Group ID
	 **/
	private $_rank_group_id = 0;

	/**
	 * @var Rank Key
	 **/
	private $_rank_key = null;

	/**
	 * @var Rank ID
	 **/
	private $_rank_id = 0;

	/**
	 * @var Profile Update
	 **/
	private $_profile_update = 0;

	/**
	 * @var Address Update
	 **/
	private $_address_update = 0;

	/**
	 * @var Bank Update
	 **/
	private $_bank_update = 0;

	/**
	 * @var Password Update
	 **/
	private $_password_update = 0;

	/**
	 * @var Security Update
	 **/
	private $_security_update = 0;

	/**
	 * @var Data
	 **/
	private $_data = null;

	/**
	 * @var Container
	 **/
	private $_container = null;

	/**
	 * @var Valid
	 **/
	private $_valid = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$data = null;
		$this->_container = new SessionContainer('UserLogin');
		if ($this->_container->offsetExists('Initialized') && $this->_container->offsetGet('Initialized') == True) {
			$data = $this->_container->offsetGet('Data');
		}
		$this->setData($data);
	}

	/**
	 * Set User Login
	 * 
	 * @return void
	 **/
	public function setLogin($id = null) {
		if (!empty($id) && is_numeric($id) && $id > 0) {
			$this->_container->offsetSet('Initialized', False);
			$DBUser = new Database('select');
			$DBUser
					->columns(
							array(
									'id' => 'user_access_id',
									'profile_id' => 'user_profile_id',
									'security_retrieve' => 'user_access_security_retrieve',
									'password_retrieve' => 'user_access_password_retrieve',
									'last_login' => 'user_access_last_login_date',
									'last_login_ip' => 'user_access_last_login_ip',
									'no_login' => 'user_access_no_of_login',
									'created_date' => 'user_access_created_date',
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
					));
			$DBUser->limit(1);
			$DBUser->setCacheName('user_access_data');
			$DBUser->execute();
			if ($DBUser->hasResult()) {
				$data = $DBUser->current();
				$data['fullname'] = $data['firstname'] . (!empty($data['lastname']) ? ' ' . $data['lastname'] : null);
				$data['rank_key'] = 'text_rank_' . $data['rank_key'];
				$data['rank_group_key'] = 'text_rank_group_' . $data['rank_group_key'];
				$this->_container->offsetSet('Data', $data);
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
		if (is_array($data)) {
			$this->_data = $data;
			$this->_valid = true;
			$this->_container->offsetSet('Initialized', True);
		}
		$this->_valid = false;
		$this->_container->offsetSet('Initialized', False);
	}

	/**
	 * Get User Data
	 * 
	 * @return int
	 **/
	public function getData($key) {
		if (array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		return false;
	}

	/**
	 * Is Valid?
	 * 
	 * @return boolean
	 **/
	public function isValid() {
		if ($this->_valid) {
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
		if (is_null($this->_id)) {
			if (isset($this->_data['id'])) {
				$this->_id = $this->_data['id'];
			}
		}
		return (int) $this->_id;
	}

	/**
	 * Get User Profile ID
	 * 
	 * @return int
	 **/
	public function getProfileID() {
		if (is_null($this->_profile_id)) {
			if (isset($this->_data['profile_id'])) {
				$this->_profile_id = $this->_data['profile_id'];
			}
		}
		return (int) $this->_profile_id;
	}

	/**
	 * Get User Address ID
	 * 
	 * @return int
	 **/
	public function getAddressID() {
		if (is_null($this->_address_id)) {
			if (isset($this->_data['address_id'])) {
				$this->_address_id = $this->_data['address_id'];
			}
		}
		return (int) $this->_address_id;
	}

	/**
	 * Get User Bank ID
	 * 
	 * @return int
	 **/
	public function getBankID() {
		if (is_null($this->_bank_id)) {
			if (isset($this->_data['bank_id'])) {
				$this->_bank_id = $this->_data['bank_id'];
			}
		}
		return (int) $this->_bank_id;
	}

	/**
	 * Get User Password Retrieve
	 * 
	 * @return string
	 **/
	public function getPasswordRetrieve() {
		if (is_null($this->_password_retrieve)) {
			if (isset($this->_data['password_retrieve'])) {
				$this->_password_retrieve = $this->_data['password_retrieve'];
			}
		}
		return (string) $this->_password_retrieve;
	}

	/**
	 * Get User Security Retrieve
	 * 
	 * @return string
	 **/
	public function getSecurityRetrieve() {
		if (is_null($this->_security_retrieve)) {
			if (isset($this->_data['security_retrieve'])) {
				$this->_security_retrieve = $this->_data['security_retrieve'];
			}
		}
		return (string) $this->_security_retrieve;
	}

	/**
	 * Get Last Login Date
	 * 
	 * @return string
	 **/
	public function getLastLogin() {
		if (is_null($this->_last_login)) {
			if (isset($this->_data['last_login'])) {
				$this->_last_login = $this->_data['last_login'];
			}
		}
		return (string) $this->_last_login;
	}

	/**
	 * Get Last Login IP
	 * 
	 * @return string
	 **/
	public function getLastLoginIP() {
		if (is_null($this->_last_login_ip)) {
			if (isset($this->_data['last_login_ip'])) {
				$this->_last_login_ip = $this->_data['last_login_ip'];
			}
		}
		return (string) $this->_last_login_ip;
	}

	/**
	 * Get Last Login Date
	 * 
	 * @return int
	 **/
	public function getNoLogin() {
		if (is_null($this->_no_login)) {
			if (isset($this->_data['no_login'])) {
				$this->_no_login = $this->_data['no_login'];
			}
		}
		return (int) $this->_no_login;
	}

	/**
	 * Get Created Date
	 * 
	 * @return string
	 **/
	public function getCreatedDate() {
		if (is_null($this->_created_date)) {
			if (isset($this->_data['created_date'])) {
				$this->_created_date = $this->_data['created_date'];
			}
		}
		return (string) $this->_created_date;
	}

	/**
	 * Get Created By
	 * 
	 * @return string
	 **/
	public function getCreatedBy() {
		if (is_null($this->_created_by)) {
			if (isset($this->_data['created_by'])) {
				$this->_created_by = $this->_data['created_by'];
			}
		}
		return (string) $this->_created_by;
	}

	/**
	 * Get Activated Date
	 * 
	 * @return string
	 **/
	public function getActivatedDate() {
		if (is_null($this->_activated_date)) {
			if (isset($this->_data['activated_date'])) {
				$this->_activated_date = $this->_data['activated_date'];
			}
		}
		return (string) $this->_activated_date;
	}

	/**
	 * Get Expired Date
	 * 
	 * @return string
	 **/
	public function getExpiredDate() {
		if (is_null($this->_expired_date)) {
			if (isset($this->_data['expired_date'])) {
				$this->_expired_date = $this->_data['expired_date'];
			}
		}
		return (string) $this->_expired_date;
	}

	/**
	 * Get Username
	 * 
	 * @return string
	 **/
	public function getUsername() {
		if (is_null($this->_username)) {
			if (isset($this->_data['username'])) {
				$this->_username = $this->_data['username'];
			}
		}
		return (string) $this->_username;
	}

	/**
	 * Get Fullname
	 * 
	 * @return string
	 **/
	public function getFullname() {
		if (is_null($this->_fullname)) {
			if (isset($this->_data['fullname'])) {
				$this->_fullname = $this->_data['fullname'];
			}
		}
		return (string) $this->_fullname;
	}

	/**
	 * Get Firstname
	 * 
	 * @return string
	 **/
	public function getFirstname() {
		if (is_null($this->_firstname)) {
			if (isset($this->_data['firstname'])) {
				$this->_firstname = $this->_data['firstname'];
			}
		}
		return (string) $this->_firstname;
	}

	/**
	 * Get Lastname
	 * 
	 * @return string
	 **/
	public function getLastname() {
		if (is_null($this->_lastname)) {
			if (isset($this->_data['lastname'])) {
				$this->_lastname = $this->_data['lastname'];
			}
		}
		return (string) $this->_lastname;
	}

	/**
	 * Get Gender
	 * 
	 * @return string
	 **/
	public function getGender() {
		if (is_null($this->_gender)) {
			if (isset($this->_data['gender'])) {
				$this->_gender = $this->_data['gender'];
			}
		}
		return (string) $this->_gender;
	}

	/**
	 * Get Rank Group
	 * 
	 * @return string
	 **/
	public function getRankGroup() {
		if (is_null($this->_rank_group_key)) {
			if (isset($this->_data['rank_group_key'])) {
				$this->_rank_group_key = $this->_data['rank_group_key'];
			}
		}
		return (string) $this->_rank_group_key;
	}

	/**
	 * Get Rank Group ID
	 * 
	 * @return int
	 **/
	public function getRankGroupID() {
		if (is_null($this->_rank_group_id)) {
			if (isset($this->_data['rank_group_id'])) {
				$this->_rank_group_id = $this->_data['rank_group_id'];
			}
		}
		return (int) $this->_rank_group_id;
	}

	/**
	 * Get Rank 
	 * 
	 * @return string
	 **/
	public function getRank() {
		if (is_null($this->_rank_key)) {
			if (isset($this->_data['rank_key'])) {
				$this->_rank_key = $this->_data['rank_key'];
			}
		}
		return (string) $this->_rank_key;
	}

	/**
	 * Get Rank  ID
	 * 
	 * @return int
	 **/
	public function getRankID() {
		if (is_null($this->_rank_id)) {
			if (isset($this->_data['rank_id'])) {
				$this->_rank_id = $this->_data['rank_id'];
			}
		}
		return (int) $this->_rank_id;
	}

	/**
	 * Get Profile Update
	 * 
	 * @return Boolean
	 **/
	public function getProfileUpdate() {
		if (is_null($this->_profile_update)) {
			if (isset($this->_data['profile_update'])) {
				$this->_profile_update = $this->_data['profile_update'];
			}
		}
		return ((int) $this->_profile_update == 1 ? True : False);
	}

	/**
	 * Get Address Update
	 * 
	 * @return Boolean
	 **/
	public function getAddressUpdate() {
		if (is_null($this->_address_update)) {
			if (isset($this->_data['address_update'])) {
				$this->_address_update = $this->_data['address_update'];
			}
		}
		return ((int) $this->_address_update == 1 ? True : False);
	}

	/**
	 * Get Bank Update
	 * 
	 * @return Boolean
	 **/
	public function getBankUpdate() {
		if (is_null($this->_bank_update)) {
			if (isset($this->_data['bank_update'])) {
				$this->_bank_update = $this->_data['bank_update'];
			}
		}
		return ((int) $this->_bank_update == 1 ? True : False);
	}

	/**
	 * Get Password Update
	 * 
	 * @return Boolean
	 **/
	public function getPasswordUpdate() {
		if (is_null($this->_password_update)) {
			if (isset($this->_data['password_update'])) {
				$this->_password_update = $this->_data['password_update'];
			}
		}
		return ((int) $this->_password_update == 1 ? True : False);
	}

	/**
	 * Get Security Update
	 * 
	 * @return Boolean
	 **/
	public function getSecurityUpdate() {
		if (is_null($this->_security_update)) {
			if (isset($this->_data['security_update'])) {
				$this->_security_update = $this->_data['security_update'];
			}
		}
		return ((int) $this->_security_update == 1 ? True : False);
	}
}

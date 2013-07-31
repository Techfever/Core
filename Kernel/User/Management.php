<?php
namespace Kernel\User;

use Kernel\Database\Database;
use Zend\Db\Sql\Expression;
use Zend\Crypt\Password\Bcrypt;

class Management {
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array(
			'access' => 0,
			'profile' => 0,
			'address' => 0,
			'bank' => 0,
	);

	public function __construct($options = array()) {
		if (!is_array($options)) {
			$options = func_get_args();
			$temp['access'] = array_shift($options);
			if (!empty($options)) {
				$temp['profile'] = array_shift($options);
			}
			if (!empty($options)) {
				$temp['address'] = array_shift($options);
			}
			if (!empty($options)) {
				$temp['bank'] = array_shift($options);
			}
			$options = $temp;
		} else {
			$options = array_merge($this->options, $options);
		}
		$this->options = $options;
	}

	/**
	 * Returns an option
	 *
	 * @param string $option Option to be returned
	 * @return mixed Returned option
	 * @throws Exception\InvalidArgumentException
	 */
	public function getOption($option) {
		if (isset($this->options) && array_key_exists($option, $this->options)) {
			return $this->options[$option];
		}

		throw new Exception\InvalidArgumentException("Invalid option '$option'");
	}

	/**
	 * Returns all available options
	 *
	 * @return array Array with all available options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets one or multiple options
	 *
	 * @param  array|Traversable $options Options to set
	 * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setOptions($options = array()) {
		if (!is_array($options) && !$options instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->options !== $options) {
			$this->options = $options;
		}
		return $this;
	}

	/**
	 * Set a single option
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options[(string) $name] = $value;
		return $this;
	}

	/**
	 * createUser
	 */
	public function createUser($data) {
		$status = true;
		$data['user_access']['user_access_username'] = $this->generateUsername();

		$password_retrieve = null;
		if (isset($data['user_access']) && isset($data['user_access']['user_access_password'])) {
			$password_retrieve = $data['user_access']['user_access_password'];
		} elseif (isset($data['user_access']) && isset($data['user_access']['user_access_password_retrieve'])) {
			$security_retrieve = $data['user_access']['user_access_password_retrieve'];
		} elseif (isset($data['user_profile']) && isset($data['user_profile']['user_profile_nric_passport'])) {
			$password_retrieve = $data['user_profile']['user_profile_nric_passport'];
		}
		$security_retrieve = null;
		if (isset($data['user_access']) && isset($data['user_access']['user_access_security'])) {
			$security_retrieve = $data['user_access']['user_access_security'];
		} elseif (isset($data['user_access']) && isset($data['user_access']['user_access_security_retrieve'])) {
			$security_retrieve = $data['user_access']['user_access_security_retrieve'];
		} elseif (isset($data['user_profile']) && isset($data['user_profile']['user_profile_nric_passport'])) {
			$security_retrieve = $data['user_profile']['user_profile_nric_passport'];
		}
		$Bcrypt = new Bcrypt(array(
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST
		));
		$data['user_access']['user_access_security'] = $Bcrypt->create($security_retrieve);
		$data['user_access']['user_access_password'] = $Bcrypt->create($password_retrieve);
		$data['user_access']['user_access_security_retrieve'] = $security_retrieve;
		$data['user_access']['user_access_password_retrieve'] = $password_retrieve;

		$IUser = new Database('insert');
		$IUser->into('user_access');
		$IUser
				->values(
						array(
								'user_access_username' => $data['user_access']['user_access_username'],
								'user_access_status' => 1,
								'user_rank_id' => $data['user_access']['user_rank_id'],
								'user_access_security' => $data['user_access']['user_access_security'],
								'user_access_security_retrieve' => $data['user_access']['user_access_security_retrieve'],
								'user_access_password' => $data['user_access']['user_access_password'],
								'user_access_password_retrieve' => $data['user_access']['user_access_password_retrieve'],
								'user_access_created_date' => $data['log']['created_date'],
								'user_access_modified_date' => $data['log']['modified_date'],
								'user_access_activated_date' => $data['log']['created_date'],
								'user_access_created_by' => $data['log']['created_by'],
								'user_access_modified_by' => $data['log']['modified_by'],
						));
		$IUser->execute();
		if ($IUser->affectedRows()) {
			$this->setOption('access', $IUser->getLastGeneratedValue());

			$IProfile = new Database('insert');
			$IProfile->into('user_profile');
			$IProfile
					->values(
							array(
									'user_profile_designation' => $data['user_profile']['user_profile_designation'],
									'user_profile_firstname' => $data['user_profile']['user_profile_firstname'],
									'user_profile_lastname' => $data['user_profile']['user_profile_lastname'],
									'user_profile_nric_passport' => $data['user_profile']['user_profile_nric_passport'],
									'user_profile_gender' => $data['user_profile']['user_profile_gender'],
									'user_profile_dob' => $data['user_profile']['user_profile_dob'],
									'user_profile_nationality' => $data['user_profile']['user_profile_nationality'],
									'user_profile_email_address' => $data['user_profile']['user_profile_email_address'],
									'user_profile_mobile_no' => $data['user_profile']['user_profile_mobile_no'],
									'user_profile_telephone_no' => $data['user_profile']['user_profile_telephone_no'],
									'user_profile_office_no' => $data['user_profile']['user_profile_office_no'],
									'user_profile_fax_no' => $data['user_profile']['user_profile_fax_no'],
									'user_profile_created_date' => $data['log']['created_date'],
									'user_profile_modified_date' => $data['log']['modified_date'],
									'user_profile_created_by' => $data['log']['created_by'],
									'user_profile_modified_by' => $data['log']['modified_by'],
							));
			$IProfile->execute();
			if ($IProfile->affectedRows()) {
				$this->setOption('profile', $IProfile->getLastGeneratedValue());

				$UAccess = new Database('update');
				$UAccess->table('user_access');
				$UAccess->set(array(
								'user_profile_id' => $this->getOption('profile'),
						));
				$UAccess->where(array(
								'user_access_id = "' . $this->getOption('access') . '"',
						));
				$UAccess->execute();
				if ($UAccess->affectedRows()) {

					$IAddress = new Database('insert');
					$IAddress->into('user_address');
					$IAddress
							->values(
									array(
											'user_profile_id' => $this->getOption('profile'),
											'user_address_street_1' => $data['user_address']['user_address_street_1'],
											'user_address_street_2' => $data['user_address']['user_address_street_2'],
											'user_address_city' => $data['user_address']['user_address_city'],
											'user_address_postcode' => $data['user_address']['user_address_postcode'],
											'user_address_state' => $data['user_address']['user_address_state'],
											'user_address_state_id' => $data['user_address']['user_address_state_id'],
											'user_address_country_id' => $data['user_address']['user_address_country_id'],
											'user_address_created_date' => $data['log']['created_date'],
											'user_address_modified_date' => $data['log']['modified_date'],
											'user_address_created_by' => $data['log']['created_by'],
											'user_address_modified_by' => $data['log']['modified_by'],
									));
					$IAddress->execute();
					if ($IAddress->affectedRows()) {
						$this->setOption('address', $IAddress->getLastGeneratedValue());
					} else {
						$status = false;
					}

					$IBank = new Database('insert');
					$IBank->into('user_bank');
					$IBank
							->values(
									array(
											'user_profile_id' => $this->getOption('profile'),
											'user_bank_holder_name' => $data['user_bank']['user_bank_holder_name'],
											'user_bank_holder_no' => $data['user_bank']['user_bank_holder_no'],
											'user_bank_name' => $data['user_bank']['user_bank_name'],
											'user_bank_name_id' => $data['user_bank']['user_bank_name_id'],
											'user_bank_branch' => $data['user_bank']['user_bank_branch'],
											'user_bank_branch_id' => $data['user_bank']['user_bank_branch_id'],
											'user_bank_state' => $data['user_bank']['user_bank_state'],
											'user_bank_state_id' => $data['user_bank']['user_bank_state_id'],
											'user_bank_country_id' => $data['user_bank']['user_bank_country_id'],
											'user_bank_created_date' => $data['log']['created_date'],
											'user_bank_modified_date' => $data['log']['modified_date'],
											'user_bank_created_by' => $data['log']['created_by'],
											'user_bank_modified_by' => $data['log']['modified_by'],
									));
					$IBank->execute();
					if ($IBank->affectedRows()) {
						$this->setOption('bank', $IBank->getLastGeneratedValue());
					} else {
						$status = false;
					}

					if ($this->getOption('bank') > 0 || $this->getOption('address') > 0) {
						$UProfile = new Database('update');
						$UProfile->table('user_profile');
						$UProfile->set(array(
										'user_bank_id' => $this->getOption('bank'),
										'user_address_id' => $this->getOption('address'),
								));
						$UProfile->where(array(
										'user_profile_id = "' . $this->getOption('profile') . '"',
								));
						$UProfile->execute();
						if (!$UProfile->affectedRows()) {
							$status = false;
						}
					}
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}

		if ($status) {
			return $this->getOption('access');
		} else {
			$this->deleteUser(true);
			return false;
		}
	}

	/**
	 * deleteUser
	 */
	public function deleteUser($forever = false) {
		if ($forever) {
			$DUser = new Database('delete');
			$DUser->from('user_access');
			$DUser->where(array(
							'user_access_id' => $this->getOption('access')
					));
			$DUser->execute();

			$DProfile = new Database('delete');
			$DProfile->from('user_profile');
			$DProfile->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DProfile->execute();

			$DAddress = new Database('delete');
			$DAddress->from('user_address');
			$DAddress->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DAddress->execute();

			$DBank = new Database('delete');
			$DBank->from('user_bank');
			$DBank->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DBank->execute();
		} else {
			$UUser = new Database('update');
			$UUser->table('user_access');
			$UUser->set(array(
							'user_access_delete_status' => '1'
					));
			$UUser->where(array(
							'user_access_id' => $this->getOption('access')
					));
			$UUser->execute();

			$UProfile = new Database('update');
			$UProfile->table('user_profile');
			$UProfile->set(array(
							'user_profile_delete_status' => '1'
					));
			$UProfile->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$UProfile->execute();

			$UAddress = new Database('update');
			$UAddress->table('user_address');
			$UAddress->set(array(
							'user_address_delete_status' => '1'
					));
			$UAddress->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$UAddress->execute();

			$UBank = new Database('update');
			$UBank->table('user_bank');
			$UBank->set(array(
							'user_bank_delete_status' => '1'
					));
			$UBank->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$UBank->execute();
		}
		return true;
	}

	/**
	 * getData
	 */
	public function getData($id = null) {
		$data = array();
		if ($id > 0) {
			$this->setOption('access', $id);
		}
		$QUser = new Database('select');
		$QUser->columns(array(
						'*'
				));
		$QUser->from(array(
						'ua' => 'user_access'
				));
		$QUser->where(array(
						'ua.user_access_id' => $this->getOption('access'),
						'ua.user_access_delete_status' => '0'
				));
		$QUser->setCacheName('user_access_' . $this->getOption('access'));
		$QUser->execute();
		if ($QUser->hasResult()) {
			$data['user_access'] = $QUser->current();
			$this->setOption('profile', $data['user_access']['user_profile_id']);

			$QProfile = new Database('select');
			$QProfile->columns(array(
							'*'
					));
			$QProfile->from(array(
							'up' => 'user_profile'
					));
			$QProfile->where(array(
							'up.user_profile_id' => $this->getOption('profile'),
							'up.user_profile_delete_status' => '0'
					));
			$QProfile->setCacheName('user_profile_' . $this->getOption('profile'));
			$QProfile->execute();
			if ($QProfile->hasResult()) {
				$data['user_profile'] = $QProfile->current();
				$this->setOption('address', $data['user_profile']['user_address_id']);
				$this->setOption('bank', $data['user_profile']['user_bank_id']);
			}

			if ($this->getOption('address') > 0) {
				$QAddress = new Database('select');
				$QAddress->columns(array(
								'*'
						));
				$QAddress->from(array(
								'ud' => 'user_address'
						));
				$QAddress->where(array(
								'ud.user_address_id' => $this->getOption('address'),
								'ud.user_address_delete_status' => '0'
						));
				$QAddress->setCacheName('user_address_' . $this->getOption('address'));
				$QAddress->execute();
				if ($QAddress->hasResult()) {
					$data['user_address'] = $QAddress->current();
				}
			}

			if ($this->getOption('bank') > 0) {
				$QBank = new Database('select');
				$QBank->columns(array(
								'*'
						));
				$QBank->from(array(
								'ub' => 'user_bank'
						));
				$QBank->where(array(
								'ub.user_bank_id' => $this->getOption('bank'),
								'ub.user_bank_delete_status' => '0'
						));
				$QBank->setCacheName('user_bank_' . $this->getOption('bank'));
				$QBank->execute();
				if ($QBank->hasResult()) {
					$data['user_bank'] = $QBank->current();
				}
			}
		}
		return $data;
	}

	/**
	 * Generate Username
	 */
	public function generateUsername($code = null, $min = null, $max = null) {
		$user_code = 'TNM';
		$length_min = 1;
		$length_max = 9999;
		if (!empty($code)) {
			$user_code = $code;
		} elseif (defined(USERNAME_CODE)) {
			$user_code = USERNAME_CODE;
		}
		if (!empty($min)) {
			$length_min = $min;
		} elseif (defined(USERNAME_LENGTH_MIN)) {
			$length_min = USERNAME_LENGTH_MIN;
		}
		if (!empty($max)) {
			$length_max = $max;
		} elseif (defined(USERNAME_LENGTH_MAX)) {
			$length_max = USERNAME_LENGTH_MAX;
		}

		$datetime = new \DateTime();

		$DBQUsername = new Database('select');
		$DBQUsername->columns(array(
						'id' => 'username_id'
				));
		$DBQUsername->from(array(
						'u' => 'username'
				));
		$DBQUsername->where(array(
						'u.user_access_username like "' . $user_code . $datetime->format('ym') . '%"',
				));
		$DBQUsername->limit(1);
		$DBQUsername->execute();
		if ($DBQUsername->count() < 1) {
			$username = array();
			for ($i = $length_min; $i <= $length_max; $i++) {
				$current_id = $i;
				for ($fix = strlen($i); $fix < 4; $fix++) {
					$current_id = '0' . $current_id;
				}
				$username[] = array(
						'user_access_username' => strtoupper($user_code . $datetime->format('ym') . $current_id)
				);
			}
			if (count($username) > 0) {
				$DBUsername = new Database('insert');
				$DBUsername->into('username');
				$DBUsername->columns(array(
								'user_access_username'
						));
				$DBUsername->values($username, Database::VALUES_MULTIPLE);
				$DBUsername->execute();
				if (!$DBUsername->affectedRows()) {
					return false;
				}
			}
		}
		$DBQUsername = new Database('select');
		$DBQUsername->columns(array(
						'username' => 'user_access_username'
				));
		$DBQUsername->from(array(
						'u' => 'username'
				));
		$DBQUsername->where(array(
						'u.user_access_username like "' . $user_code . $datetime->format('ym') . '%"',
						'u.username_status = 1',
				));
		$DBQUsername->group(array(
						'user_access_username'
				));
		$DBQUsername->order(array(
						new Expression('RAND()')
				));
		$DBQUsername->limit(1);
		$DBQUsername->execute();
		$username = null;
		if ($DBQUsername->count() > 0) {
			$username = $DBQUsername->current();
			$username = $username['username'];
		} else {
			$this->generateUsername($user_code, $length_max, ($length_max * 2 + $length_min));
		}
		return $username;
	}
}

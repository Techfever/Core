<?php

namespace Techfever\User;

use Zend\Db\Sql\Expression;
use Zend\Crypt\Password\Bcrypt;
use Techfever\Address\Address;
use Techfever\Bank\Bank;
use Techfever\Nationality\Nationality;
use Techfever\Parameter\Parameter;
use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;
use Techfever\Functions\Crypt\Encode as Encrypt;

class Management extends GeneralBase {

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
			'bank' => 0
	);

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		parent::__construct($options);
		unset($options['servicelocator']);
		$this->setOptions($options);
	}

	/**
	 * createUser
	 */
	public function createUser($data) {
		$status = true;
		$rawdata = $data;
		if (is_array($rawdata) && count($rawdata) > 0) {
			foreach ($rawdata as $data_key => $data_value) {
				if (preg_match('/val\{(.*)\}$/', $data_value)) {
					$variable = $data_value['value'];
					$variable = str_replace('val{', '', $variable);
					$variable = str_replace('}', '', $variable);
					$variable = strtolower($variable);
					if (array_key_exists($variable, $data)) {
						$valuecapture = $data[$variable];
						if ($variable == 'user_profile_nationality' && $data[$variable] > 0) {
							$Nationality = new Nationality(array(
									'servicelocator' => $this->getServiceLocator(),
							));
							$valuecapture = $Nationality->getCountryISO($data[$variable]);
						} elseif ($variable == 'user_address_country' && $data[$variable] > 0) {
							$Address = new Address(array(
									'servicelocator' => $this->getServiceLocator(),
							));
							$valuecapture = $Address->getCountryISO($data[$variable]);
						} elseif ($variable == 'user_bank_country' && $data[$variable] > 0) {
							$Bank = new Bank(array(
									'servicelocator' => $this->getServiceLocator(),
							));
							$valuecapture = $Bank->getCountryISO($data[$variable]);
						}
						$data[$data_key] = $valuecapture;
					}
				}
			}
		}

		if (!isset($data['user_username'])) {
			$data['user_username'] = $this->generateUsername($data['user_username_open_tag'], $data['user_username_min'], $data['user_username_max'], $data['user_username_end_tag']);
		}

		$password_retrieve = null;
		if (isset($data['user_access_password'])) {
			$password_retrieve = $data['user_access_password'];
		} elseif (isset($data['user_access_password_retrieve'])) {
			$security_retrieve = $data['user_access_password_retrieve'];
		} elseif (isset($data['user_profile_nric_passport'])) {
			$password_retrieve = $data['user_profile_nric_passport'];
		}
		$security_retrieve = null;
		if (isset($data['user_access_security'])) {
			$security_retrieve = $data['user_access_security'];
		} elseif (isset($data['user_access_security_retrieve'])) {
			$security_retrieve = $data['user_access_security_retrieve'];
		} elseif (isset($data['user_profile_nric_passport'])) {
			$security_retrieve = $data['user_profile_nric_passport'];
		}
		$Bcrypt = new Bcrypt(array(
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST
		));
		$data['user_security'] = $Bcrypt->create($security_retrieve);
		$data['user_password'] = $Bcrypt->create($password_retrieve);
		$data['user_security_retrieve'] = $security_retrieve;
		$data['user_password_retrieve'] = $password_retrieve;

		$IUser = $this->getDatabase();
		$IUser->insert();
		$IUser->into('user_access');
		$IUser
				->values(
						array(
								'user_access_username' => $data['user_username'],
								'user_access_status' => 1,
								'user_rank_id' => $data['user_rank'],
								'user_access_security' => $data['user_security'],
								'user_access_security_retrieve' => $data['user_security_retrieve'],
								'user_access_password' => $data['user_password'],
								'user_access_password_retrieve' => $data['user_password_retrieve'],
								'user_access_created_date' => $data['log_created_date'],
								'user_access_modified_date' => $data['log_modified_date'],
								'user_access_activated_date' => $data['log_created_date'],
								'user_access_created_by' => $data['log_created_by'],
								'user_access_modified_by' => $data['log_modified_by']
						));
		$IUser->execute();
		if ($IUser->affectedRows()) {
			$this->setOption('access', $IUser->getLastGeneratedValue());

			$placement = null;
			$sponsor = null;

			if (isset($data['user_hierarchy_placement'])) {
				$data['user_hierarchy_placement_username'] = $data['user_hierarchy_placement'];
				$QPlacement = $this->getDatabase();
				$QPlacement->select();
				$QPlacement->columns(array(
								'sponsor' => 'user_hierarchy_sponsor',
								'placement' => 'user_hierarchy_placement'
						));
				$QPlacement->from(array(
								'uh' => 'user_hierarchy'
						));
				$QPlacement->where(array(
								'uh.user_access_username = "' . strtoupper($data['user_hierarchy_placement']) . '"'
						));
				$QPlacement->limit(1);
				$QPlacement->execute();
				if ($QPlacement->hasResult()) {
					$placementdata = $QPlacement->current();
					$placement = $placementdata['placement'];

					$PlacementParameter = new Parameter(array(
							'key' => 'user_hierarchy_placement_position',
							'servicelocator' => $this->getServiceLocator(),
					));
					$valuePlacement = $PlacementParameter->getValueByKey($data['user_hierarchy_placement_position']);
					if (strlen($valuePlacement) > 0) {
						$placement .= '|L:' . $valuePlacement;
						$data['user_hierarchy_placement'] = $placement;
					}

					$DBVerify = $this->getDatabase();
					$DBVerify->select();
					$DBVerify->columns(array(
									'id' => 'user_access_id'
							));
					$DBVerify->from(array(
									'uh' => 'user_hierarchy'
							));
					$DBVerify->where(array(
									'uh.user_hierarchy_placement = "' . $placement . '"'
							));
					$DBVerify->execute();
					if ($DBVerify->hasResult()) {
						$status = false;
					}
				} else {
					$status = false;
				}
			} else {
				$data['user_hierarchy_placement_username'] = $this->getUsername(1);
				$data['user_hierarchy_placement'] = 'L:1|L:1';
			}

			if (isset($data['user_hierarchy_sponsor'])) {
				$data['user_hierarchy_sponsor_username'] = $data['user_hierarchy_sponsor'];
				$QSponsor = $this->getDatabase();
				$QSponsor->select();
				$QSponsor->columns(array(
								'sponsor' => 'user_hierarchy_sponsor',
								'placement' => 'user_hierarchy_placement'
						));
				$QSponsor->from(array(
								'uh' => 'user_hierarchy'
						));
				$QSponsor->where(array(
								'uh.user_access_username = "' . strtoupper($data['user_hierarchy_sponsor']) . '"'
						));
				$QSponsor->limit(1);
				$QSponsor->execute();
				if ($QSponsor->hasResult()) {
					$sponsordata = $QSponsor->current();
					$sponsor = $sponsordata['sponsor'];

					$DBVerify = $this->getDatabase();
					$DBVerify->select();
					$DBVerify->columns(array(
									'total' => new \Zend\Db\Sql\Expression('COUNT(uh.user_hierarchy_sponsor_username)')
							));
					$DBVerify->from(array(
									'uh' => 'user_hierarchy'
							));
					$DBVerify->where(array(
									'uh.user_hierarchy_sponsor_username = "' . strtoupper($data['user_hierarchy_sponsor']) . '"'
							));
					$DBVerify->group(array(
									'user_hierarchy_sponsor_username'
							));
					$DBVerify->limit(1);
					$DBVerify->execute();
					$total = 1;
					if ($DBVerify->hasResult()) {
						$verifydata = $DBVerify->current();
						$total = $verifydata['total'] + 1;
					}
					$sponsor .= '|L:' . $total;
					$data['user_hierarchy_sponsor'] = $sponsor;
				} else {
					$status = false;
				}
			} else {
				$data['user_hierarchy_sponsor_username'] = $this->getUsername(1);
				$data['user_hierarchy_sponsor'] = 'L:1|L:1';
			}

			$IHierarchy = $this->getDatabase();
			$IHierarchy->insert();
			$IHierarchy->into('user_hierarchy');
			$IHierarchy
					->values(
							array(
									'user_access_username' => strtoupper($data['user_username']),
									'user_access_id' => $this->getOption('access'),
									'user_hierarchy_sponsor' => $data['user_hierarchy_sponsor'],
									'user_hierarchy_sponsor_username' => strtoupper($data['user_hierarchy_sponsor_username']),
									'user_hierarchy_placement' => $data['user_hierarchy_placement'],
									'user_hierarchy_placement_username' => strtoupper($data['user_hierarchy_placement_username']),
									'user_hierarchy_created_date' => $data['log_created_date'],
									'user_hierarchy_modified_date' => $data['log_modified_date'],
									'user_hierarchy_created_by' => $data['log_created_by'],
									'user_hierarchy_modified_by' => $data['log_modified_by']
							));
			$IHierarchy->execute();
			if (!$IHierarchy->affectedRows()) {
				$status = false;
			}

			if ($status) {
				if (isset($data['user_profile_fullname'])) {
					$data['user_profile_firstname'] = (isset($data['user_profile_fullname']) ? $data['user_profile_fullname'] : null);
				} else {
					$data['user_profile_firstname'] = (isset($data['user_profile_firstname']) ? $data['user_profile_firstname'] : null);
				}
				$data['user_profile_lastname'] = (isset($data['user_profile_lastname']) ? $data['user_profile_lastname'] : null);

				if (isset($data['user_profile_dob']['day'])) {
					$dob_day = $data['user_profile_dob']['day'];
				} else {
					$dob_day = '01';
				}
				if (isset($data['user_profile_dob']['month'])) {
					$dob_month = $data['user_profile_dob']['month'];
				} else {
					$dob_month = '01';
				}
				if (isset($data['user_profile_dob']['year'])) {
					$dob_year = $data['user_profile_dob']['year'];
				} else {
					$dob_year = '0001';
				}
				$data['user_profile_dob'] = $dob_year . '-' . $dob_month . '-' . $dob_day . ' 00:00:00';

				$IProfile = $this->getDatabase();
				$IProfile->insert();
				$IProfile->into('user_profile');
				$IProfile
						->values(
								array(
										'user_profile_designation' => $data['user_profile_designation'],
										'user_profile_firstname' => $data['user_profile_firstname'],
										'user_profile_lastname' => $data['user_profile_lastname'],
										'user_profile_nric_passport' => $data['user_profile_nric_passport'],
										'user_profile_gender' => $data['user_profile_gender'],
										'user_profile_dob' => $data['user_profile_dob'],
										'user_profile_nationality' => $data['user_profile_nationality'],
										'user_profile_email_address' => $data['user_profile_email_address'],
										'user_profile_mobile_no' => $data['user_profile_mobile_no'],
										'user_profile_telephone_no' => $data['user_profile_telephone_no'],
										'user_profile_office_no' => $data['user_profile_office_no'],
										'user_profile_fax_no' => $data['user_profile_fax_no'],
										'user_profile_created_date' => $data['log_created_date'],
										'user_profile_modified_date' => $data['log_modified_date'],
										'user_profile_created_by' => $data['log_created_by'],
										'user_profile_modified_by' => $data['log_modified_by']
								));
				$IProfile->execute();
				if ($IProfile->affectedRows()) {
					$this->setOption('profile', $IProfile->getLastGeneratedValue());

					$UAccess = $this->getDatabase();
					$UAccess->update();
					$UAccess->table('user_access');
					$UAccess->set(array(
									'user_profile_id' => $this->getOption('profile')
							));
					$UAccess->where(array(
									'user_access_id = "' . $this->getOption('access') . '"'
							));
					$UAccess->execute();
					if ($UAccess->affectedRows()) {
						if (isset($data['user_address_country']) && $data['user_address_country'] > 0) {
							$IAddress = $this->getDatabase();
							$IAddress->insert();
							$IAddress->into('user_address');
							$IAddress
									->values(
											array(
													'user_profile_id' => $this->getOption('profile'),
													'user_address_street_1' => $data['user_address_street_1'],
													'user_address_street_2' => $data['user_address_street_2'],
													'user_address_city' => $data['user_address_city'],
													'user_address_postcode' => $data['user_address_postcode'],
													'user_address_state_text' => $data['user_address_state_text'],
													'user_address_state' => $data['user_address_state'],
													'user_address_country_text' => $data['user_address_country_text'],
													'user_address_country' => $data['user_address_country'],
													'user_address_created_date' => $data['log_created_date'],
													'user_address_modified_date' => $data['log_modified_date'],
													'user_address_created_by' => $data['log_created_by'],
													'user_address_modified_by' => $data['log_modified_by']
											));
							$IAddress->execute();
							if ($IAddress->affectedRows()) {
								$this->setOption('address', $IAddress->getLastGeneratedValue());
							} else {
								$this->setOption('address', 0);
							}
						} else {
							$this->setOption('address', 0);
						}

						if (isset($data['user_bank_holder_name']) && $data['user_bank_holder_name'] > 0) {
							$IBank = $this->getDatabase();
							$IBank->insert();
							$IBank->into('user_bank');
							$IBank
									->values(
											array(
													'user_profile_id' => $this->getOption('profile'),
													'user_bank_holder_name' => $data['user_bank_holder_name'],
													'user_bank_holder_no' => $data['user_bank_holder_no'],
													'user_bank_name_text' => $data['user_bank_name_text'],
													'user_bank_name' => $data['user_bank_name'],
													'user_bank_branch_text' => $data['user_bank_branch_text'],
													'user_bank_branch' => $data['user_bank_branch'],
													'user_bank_state_text' => $data['user_bank_state_text'],
													'user_bank_state' => $data['user_bank_state'],
													'user_bank_country_text' => $data['user_bank_country_text'],
													'user_bank_country' => $data['user_bank_country'],
													'user_bank_created_date' => $data['log_created_date'],
													'user_bank_modified_date' => $data['log_modified_date'],
													'user_bank_created_by' => $data['log_created_by'],
													'user_bank_modified_by' => $data['log_modified_by']
											));
							$IBank->execute();
							if ($IBank->affectedRows()) {
								$this->setOption('bank', $IBank->getLastGeneratedValue());
							} else {
								$this->setOption('bank', 0);
							}
						} else {
							$this->setOption('bank', 0);
						}

						if ($this->getOption('bank') > 0 || $this->getOption('address') > 0) {
							$UProfile = $this->getDatabase();
							$UProfile->update();
							$UProfile->table('user_profile');
							$UProfile->set(array(
											'user_bank_id' => $this->getOption('bank'),
											'user_address_id' => $this->getOption('address')
									));
							$UProfile->where(array(
											'user_profile_id = "' . $this->getOption('profile') . '"'
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
			$DUser = $this->getDatabase();
			$DUser->delete();
			$DUser->from('user_access');
			$DUser->where(array(
							'user_access_id' => $this->getOption('access')
					));
			$DUser->execute();

			$DHierarchy = $this->getDatabase();
			$DHierarchy->delete();
			$DHierarchy->from('user_hierarchy');
			$DHierarchy->where(array(
							'user_access_id' => $this->getOption('access')
					));
			$DHierarchy->execute();

			$DProfile = $this->getDatabase();
			$DProfile->delete();
			$DProfile->from('user_profile');
			$DProfile->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DProfile->execute();

			$DAddress = $this->getDatabase();
			$DAddress->delete();
			$DAddress->from('user_address');
			$DAddress->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DAddress->execute();

			$DBank = $this->getDatabase();
			$DBank->delete();
			$DBank->from('user_bank');
			$DBank->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$DBank->execute();
		} else {
			$UUser = $this->getDatabase();
			$UUser->update();
			$UUser->table('user_access');
			$UUser->set(array(
							'user_access_delete_status' => '1'
					));
			$UUser->where(array(
							'user_access_id' => $this->getOption('access')
					));
			$UUser->execute();

			$UProfile = $this->getDatabase();
			$UProfile->update();
			$UProfile->table('user_profile');
			$UProfile->set(array(
							'user_profile_delete_status' => '1'
					));
			$UProfile->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$UProfile->execute();

			$UAddress = $this->getDatabase();
			$UAddress->update();
			$UAddress->table('user_address');
			$UAddress->set(array(
							'user_address_delete_status' => '1'
					));
			$UAddress->where(array(
							'user_profile_id' => $this->getOption('profile')
					));
			$UAddress->execute();

			$UBank = $this->getDatabase();
			$UBank->update();
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
	public function getUsername($id = null) {
		$username = null;
		if ($id > 0) {
			$this->setOption('access', $id);
		}
		$QUser = $this->getDatabase();
		$QUser->select();
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
			$data = $QUser->current();
			$username = $data['user_access_username'];
		}
		return $username;
	}

	/**
	 * getData
	 */
	public function getData($id = null, $rank_group = 10000) {
		$data = array();
		if ($id > 0) {
			$this->setOption('access', $id);
		}
		$orderstr = null;

		$Rank = new Rank(array(
				'group' => $rank_group,
				'servicelocator' => $this->getServiceLocator(),
		));
		$RankAll = $Rank->getRankIDAll();

		$Nationality = new Nationality(array(
				'servicelocator' => $this->getServiceLocator(),
		));

		$PlacementParameter = new Parameter(array(
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator(),
		));

		$StatusParameter = new Parameter(array(
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator(),
		));

		$DesignationParameter = new Parameter(array(
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator(),
		));

		$GenderParameter = new Parameter(array(
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator(),
		));

		$QUser = $this->getDatabase();
		$QUser->select();
		$QUser->columns(array(
						'*'
				));
		$QUser->from(array(
						'ua' => 'user_access'
				));
		$QUser->join(array(
						'uh' => 'user_hierarchy'
				), 'uh.user_access_id  = ua.user_access_id', array(
						'*'
				));
		$QUser->join(array(
						'up' => 'user_profile'
				), 'up.user_profile_id  = ua.user_profile_id', array(
						'*'
				));
		$QUser->where(array(
						'ua.user_access_id' => $this->getOption('access'),
						'up.user_profile_delete_status' => '0',
						'ua.user_access_delete_status' => '0',
						'ua.user_rank_id in (' . implode(', ', $RankAll) . ')'
				));
		$QUser->setCacheName('user_access_' . $this->getOption('access'));
		$QUser->execute();
		if ($QUser->hasResult()) {
			$data = $QUser->current();
			$this->setOption('profile', $data['user_profile_id']);
			$this->setOption('address', $data['user_address_id']);
			$this->setOption('bank', $data['user_bank_id']);

			$data['user_access_password'] = '';
			$data['user_access_password_confirmation'] = '';
			$data['user_access_security'] = '';
			$data['user_access_security_confirmation'] = '';

			$cryptID = new Encrypt($data['user_access_id']);
			$cryptID = $cryptID->__toString();
			$data['id'] = $cryptID;

			$data['user_modify'] = $cryptID;

			$datetime = new \DateTime($data['user_access_activated_date']);
			$data['user_access_activated_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_access_created_date']);
			$data['user_access_created_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_access_expired_date']);
			$data['user_access_expired_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_access_last_login_date']);
			$data['user_access_last_login_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_access_modified_date']);
			$data['user_access_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_hierarchy_created_date']);
			$data['user_hierarchy_created_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_hierarchy_modified_date']);
			$data['user_hierarchy_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_profile_created_date']);
			$data['user_profile_created_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_profile_modified_date']);
			$data['user_profile_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

			$datetime = new \DateTime($data['user_profile_dob']);
			$data['user_profile_dob_format'] = $datetime->format('d-m-Y');

			$data['user_rank_text'] = $data['user_rank_id'];
			$valueRank = $Rank->getMessage($data['user_rank_id']);
			if (strlen($valueRank) > 0) {
				$data['user_rank_text'] = $valueRank;
			}

			$data['user_profile_nationality_text'] = $Nationality->getCountryName($data['user_profile_nationality']);

			$data['user_profile_designation_text'] = $data['user_profile_designation'];
			$valueDesignation = $DesignationParameter->getMessageByValue($data['user_profile_designation_text']);
			if (strlen($valueDesignation) > 0) {
				$data['user_profile_designation_text'] = $valueDesignation;
			}

			$data['user_profile_gender_text'] = $data['user_profile_gender'];
			$valueGender = $GenderParameter->getMessageByValue($data['user_profile_gender_text']);
			if (strlen($valueGender) > 0) {
				$data['user_profile_gender_text'] = $valueGender;
			}

			$data['user_access_status_text'] = $data['user_access_status'];
			$valueStatus = $StatusParameter->getMessageByValue($data['user_access_status_text']);
			if (strlen($valueStatus) > 0) {
				$data['user_access_status_text'] = $valueStatus;
			}

			$data['user_hierarchy_placement_position_text'] = substr($data['user_hierarchy_placement'], -1);
			$valuePlacement = $PlacementParameter->getMessageByValue($data['user_hierarchy_placement_position_text']);
			if (strlen($valuePlacement) > 0) {
				$data['user_hierarchy_placement_position_text'] = $valuePlacement;
			}

			$data['user_profile_fullname'] = $data['user_profile_firstname'] . (!empty($data['user_profile_lastname']) ? ' ' . $data['user_profile_lastname'] : null);

			if ($this->getOption('address') > 0) {
				$Address = new Address(array(
						'profile_id' => $this->getOption('profile'),
						'address_id' => $this->getOption('address'),
						'servicelocator' => $this->getServiceLocator(),
				));
				$rawdata = $Address->getUserAddress($this->getOption('address'));
				$data = array_merge($data, $rawdata);
				$data['user_address_created_date_format'] = "";
				if ($data['user_address_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_address_created_date']);
					$data['user_address_created_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
				$data['user_address_modified_date_format'] = "";
				if ($data['user_address_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_address_modified_date']);
					$data['user_address_modified_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
			}

			if ($this->getOption('bank') > 0) {
				$Bank = new Bank(array(
						'profile_id' => $this->getOption('profile'),
						'bank_id' => $this->getOption('bank'),
						'servicelocator' => $this->getServiceLocator(),
				));
				$rawdata = $Bank->getUserBank($this->getOption('bank'));
				$data = array_merge($data, $rawdata);
				$data['user_bank_created_date_format'] = "";
				if ($data['user_bank_created_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_bank_created_date']);
					$data['user_bank_created_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
				$data['user_bank_modified_date_format'] = "";
				if ($data['user_bank_modified_date'] != '0000-00-00 00:00:00') {
					$datetime = new \DateTime($data['user_bank_modified_date']);
					$data['user_bank_modified_date_format'] = $datetime->format('H:i:s d-m-Y');
				}
			}
		}
		ksort($data);
		if (count($data) > 1) {
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * getListing
	 */
	public function getListing($rank_group = 10000, $search = null, $order = null, $index = 0, $perpage = 10, $cache = 'user_access_profile', $encryted_id = false) {
		$orderstr = null;
		$data = array();

		$Rank = new Rank(array(
				'group' => $rank_group,
				'servicelocator' => $this->getServiceLocator(),
		));
		$RankAll = $Rank->getRankIDAll();

		$Nationality = new Nationality(array(
				'servicelocator' => $this->getServiceLocator(),
		));

		$PlacementParameter = new Parameter(array(
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator(),
		));

		$StatusParameter = new Parameter(array(
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator(),
		));

		$DesignationParameter = new Parameter(array(
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator(),
		));

		$GenderParameter = new Parameter(array(
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator(),
		));

		$QUser = $this->getDatabase();
		$QUser->select();
		$QUser->columns(array(
						'*'
				));
		$QUser->from(array(
						'ua' => 'user_access'
				));
		$QUser->join(array(
						'uh' => 'user_hierarchy'
				), 'uh.user_access_id  = ua.user_access_id', array(
						'*'
				));
		$QUser->join(array(
						'up' => 'user_profile'
				), 'up.user_profile_id  = ua.user_profile_id', array(
						'*'
				));
		$where = array(
				'up.user_profile_delete_status' => '0',
				'ua.user_access_delete_status' => '0',
				'ua.user_rank_id in (' . implode(', ', $RankAll) . ')'
		);
		if (is_array($search) && count($search) > 0 && array_key_exists('user_access', $search)) {
			$where = array_merge($where, $search['user_access']);
		}
		if (is_array($search) && count($search) > 0 && array_key_exists('user_hierarchy', $search)) {
			$where = array_merge($where, $search['user_hierarchy']);
		}
		if (is_array($search) && count($search) > 0 && array_key_exists('user_profile', $search)) {
			$where = array_merge($where, $search['user_profile']);
		}
		$QUser->where($where);
		if (empty($order)) {
			$QUser->order(array(
							'ua.user_access_username'
					));
		} else {
			$QUser->order($order);
		}
		if (isset($perpage)) {
			$QUser->limit((int) $perpage);
		}
		if (isset($index)) {
			$QUser->offset((int) $index);
		}
		$QUser->setCacheName('user_access_' . $cache);
		$QUser->execute();
		if ($QUser->hasResult()) {
			$data = array();
			$count = 1;
			while ($QUser->valid()) {
				$rawdata = $QUser->current();
				$rawdata['no'] = $count;

				$cryptID = new Encrypt($rawdata['user_access_id']);
				$cryptID = $cryptID->__toString();
				$rawdata['id'] = ($encryted_id ? $cryptID : $rawdata['user_access_id']);

				$datetime = new \DateTime($rawdata['user_access_activated_date']);
				$rawdata['user_access_activated_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_access_created_date']);
				$rawdata['user_access_created_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_access_expired_date']);
				$rawdata['user_access_expired_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_access_last_login_date']);
				$rawdata['user_access_last_login_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_access_modified_date']);
				$rawdata['user_access_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_hierarchy_created_date']);
				$rawdata['user_hierarchy_created_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_hierarchy_modified_date']);
				$rawdata['user_hierarchy_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_profile_created_date']);
				$rawdata['user_profile_created_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_profile_modified_date']);
				$rawdata['user_profile_modified_date_format'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_profile_dob']);
				$rawdata['user_profile_dob_format'] = $datetime->format('d-m-Y');

				$rawdata['user_rank_text'] = $rawdata['user_rank_id'];
				$valueRank = $Rank->getMessage($rawdata['user_rank_id']);
				if (strlen($valueRank) > 0) {
					$rawdata['user_rank_text'] = $valueRank;
				}

				$rawdata['user_profile_nationality_text'] = $Nationality->getCountryName($rawdata['user_profile_nationality']);

				$rawdata['user_profile_designation_text'] = $rawdata['user_profile_designation'];
				$valueDesignation = $DesignationParameter->getMessageByValue($rawdata['user_profile_designation_text']);
				if (strlen($valueDesignation) > 0) {
					$rawdata['user_profile_designation_text'] = $valueDesignation;
				}

				$rawdata['user_profile_gender_text'] = $rawdata['user_profile_gender'];
				$valueGender = $GenderParameter->getMessageByValue($rawdata['user_profile_gender_text']);
				if (strlen($valueGender) > 0) {
					$rawdata['user_profile_gender_text'] = $valueGender;
				}

				$rawdata['user_access_status_text'] = $rawdata['user_access_status'];
				$valueStatus = $StatusParameter->getMessageByValue($rawdata['user_access_status_text']);
				if (strlen($valueStatus) > 0) {
					$rawdata['user_access_status_text'] = $valueStatus;
				}

				$rawdata['user_hierarchy_placement_position_text'] = substr($rawdata['user_hierarchy_placement'], -1);
				$valuePlacement = $PlacementParameter->getMessageByValue($rawdata['user_hierarchy_placement_position_text']);
				if (strlen($valuePlacement) > 0) {
					$rawdata['user_hierarchy_placement_position_text'] = $valuePlacement;
				}

				$rawdata['user_profile_fullname'] = $rawdata['user_profile_firstname'] . (!empty($rawdata['user_profile_lastname']) ? ' ' . $rawdata['user_profile_lastname'] : null);

				$QUser->next();
				ksort($rawdata);
				$data[] = $rawdata;
				$count++;
			}
		}
		if (count($data) > 0) {
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * Get ID
	 *
	 * @return Interger
	 *
	 */
	public function getID($username = null, $rankgroup = null, $status = 1) {
		if (!empty($username)) {
			$rank = null;
			if (!empty($rankgroup)) {
				$Rank = new Rank(array(
						'group' => $rankgroup,
						'servicelocator' => $this->getServiceLocator(),
				));
				$rank = $Rank->getRankIDAll();
			}

			$DBVerify = $this->getDatabase();
			$DBVerify->select();
			$DBVerify->columns(array(
							'id' => 'user_access_id'
					));
			$DBVerify->from(array(
							'ua' => 'user_access'
					));
			$where = array(
					'ua.user_access_username = "' . $username . '"',
					'ua.user_access_delete_status = 0'
			);
			if (!empty($status)) {
				$where[] = 'ua.user_access_status = ' . $status;
			}
			if (count($rank) > 0) {
				$where[] = 'ua.user_rank_id in (' . implode(', ', $rank) . ')';
			}
			$DBVerify->where($where);
			$DBVerify->limit(1);
			$DBVerify->execute();
			if ($DBVerify->hasResult()) {
				$data = $DBVerify->current();
				$this->setOption('access', $data['id']);
			}
		}
		return (int) $this->getOption('access');
	}

	/**
	 * Get Profile ID
	 *
	 * @return Interger
	 *
	 */
	public function getProfileID($id = null) {
		if (!empty($id)) {
			$QProfile = $this->getDatabase();
			$QProfile->select();
			$QProfile->columns(array(
							'id' => 'user_profile_id'
					));
			$QProfile->from(array(
							'ua' => 'user_access'
					));
			$QProfile->where(array(
							'ua.user_access_id = "' . $id . '"',
					));
			$QProfile->limit(1);
			$QProfile->execute();
			if ($QProfile->hasResult()) {
				$data = $QProfile->current();
				$this->setOption('profile', $data['id']);
			}
		}
		return (int) $this->getOption('profile');
	}

	/**
	 * Get Address ID
	 *
	 * @return Interger
	 *
	 */
	public function getAddressDefaultID($id = null) {
		if (!empty($id)) {
			$QProfile = $this->getDatabase();
			$QProfile->select();
			$QProfile->columns(array(
							'id' => 'user_profile_id'
					));
			$QProfile->from(array(
							'ua' => 'user_access'
					));
			$QProfile->join(array(
							'up' => 'user_profile'
					), 'up.user_profile_id  = ua.user_profile_id', array(
							'address_id' => 'user_address_id'
					));
			$QProfile->where(array(
							'ua.user_access_id = "' . $id . '"',
					));
			$QProfile->limit(1);
			$QProfile->execute();
			if ($QProfile->hasResult()) {
				$data = $QProfile->current();
				return (int) $data['address_id'];
			}
		}
		return 0;
	}

	/**
	 * Get Bank ID
	 *
	 * @return Interger
	 *
	 */
	public function getBankDefaultID($id = null) {
		if (!empty($id)) {
			$QProfile = $this->getDatabase();
			$QProfile->select();
			$QProfile->columns(array(
							'id' => 'user_profile_id'
					));
			$QProfile->from(array(
							'ua' => 'user_access'
					));
			$QProfile->join(array(
							'up' => 'user_profile'
					), 'up.user_profile_id  = ua.user_profile_id', array(
							'bank_id' => 'user_bank_id'
					));
			$QProfile->where(array(
							'ua.user_access_id = "' . $id . '"',
					));
			$QProfile->limit(1);
			$QProfile->execute();
			if ($QProfile->hasResult()) {
				$data = $QProfile->current();
				return (int) $data['bank_id'];
			}
		}
		return 0;
	}

	/**
	 * verify ID
	 *
	 * @return Interger
	 *
	 */
	public function verifyID($id = null, $rankgroup = null, $status = 1) {
		if (!empty($id)) {
			$rank = null;
			if (!empty($rankgroup)) {
				$Rank = new Rank(array(
						'group' => $rankgroup,
						'servicelocator' => $this->getServiceLocator(),
				));
				$rank = $Rank->getRankIDAll();
			}

			$DBVerify = $this->getDatabase();
			$DBVerify->select();
			$DBVerify->columns(array(
							'id' => 'user_access_id'
					));
			$DBVerify->from(array(
							'ua' => 'user_access'
					));
			$where = array(
					'ua.user_access_id = "' . $id . '"',
					'ua.user_access_delete_status = 0'
			);
			if (!empty($status)) {
				$where[] = 'ua.user_access_status = ' . $status;
			}
			if (count($rank) > 0) {
				$where[] = 'ua.user_rank_id in (' . implode(', ', $rank) . ')';
			}
			$DBVerify->where($where);
			$DBVerify->limit(1);
			$DBVerify->execute();
			if ($DBVerify->hasResult()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Update Status
	 *
	 * @return boolean
	 *
	 */
	public function updateStatus($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$status = 0;
			$StatusParameter = new Parameter(array(
					'key' => 'user_access_status',
					'servicelocator' => $this->getServiceLocator(),
			));
			if ($StatusParameter->hasResult()) {
				$status = $StatusParameter->getValueByKey($data['user_access_status']);
			}

			$UStatus = $this->getDatabase();
			$UStatus->update();
			$UStatus->table('user_access');
			$UStatus->set(array(
							'user_access_status' => $status,
							'user_access_modified_date' => $data['log_modified_date'],
							'user_access_modified_by' => $data['log_modified_by']
					));
			$UStatus->where(array(
							'user_access_id' => $id
					));
			$UStatus->setCacheName('user_access_' . $id);
			$UStatus->execute();
			if ($UStatus->affectedRows()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Update Username
	 *
	 * @return boolean
	 *
	 */
	public function updateUsername($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$UUsername = $this->getDatabase();
			$UUsername->update();
			$UUsername->table('user_access');
			$UUsername->set(array(
							'user_access_username' => $data['user_username'],
							'user_access_modified_date' => $data['log_modified_date'],
							'user_access_modified_by' => $data['log_modified_by']
					));
			$UUsername->where(array(
							'user_access_id' => $id
					));
			$UUsername->setCacheName('user_access_' . $id);
			$UUsername->execute();
			if ($UUsername->affectedRows()) {
				$UHierarchy = $this->getDatabase();
				$UHierarchy->update();
				$UHierarchy->table('user_hierarchy');
				$UHierarchy->set(array(
								'user_access_username' => $data['user_username'],
								'user_hierarchy_modified_date' => $data['log_modified_date'],
								'user_hierarchy_modified_by' => $data['log_modified_by']
						));
				$UHierarchy->where(array(
								'user_access_id' => $id
						));
				$UHierarchy->setCacheName('user_hierarchy_' . $id);
				$UHierarchy->execute();
				if ($UHierarchy->affectedRows()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Update Address
	 *
	 * @return boolean
	 *
	 */
	public function updateAddress($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$profile = $this->getProfileID($id);
			$address = $this->getAddressDefaultID($id);
			if ($profile > 0 && $address > 0) {
				$UAddress = $this->getDatabase();
				$UAddress->update();
				$UAddress->table('user_address');
				$UAddress
						->set(
								array(
										'user_address_street_1' => $data['user_address_street_1'],
										'user_address_street_2' => $data['user_address_street_2'],
										'user_address_city' => $data['user_address_city'],
										'user_address_postcode' => $data['user_address_postcode'],
										'user_address_state_text' => $data['user_address_state_text'],
										'user_address_state' => $data['user_address_state'],
										'user_address_country_text' => $data['user_address_country_text'],
										'user_address_country' => $data['user_address_country'],
										'user_address_created_by' => $data['log_created_by'],
										'user_address_modified_by' => $data['log_modified_by']
								));
				$UAddress->where(array(
								'user_profile_id' => $profile,
								'user_address_id' => $address,
						));
				$UAddress->setCacheName('user_address_' . $address . '_' . $profile);
				$UAddress->execute();
				if ($UAddress->affectedRows()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Update Bank
	 *
	 * @return boolean
	 *
	 */
	public function updateBank($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$profile = $this->getProfileID($id);
			$bank = $this->getBankDefaultID($id);
			if ($profile > 0 && $bank > 0) {
				$UBank = $this->getDatabase();
				$UBank->update();
				$UBank->table('user_bank');
				$UBank
						->set(
								array(
										'user_bank_holder_name' => $data['user_bank_holder_name'],
										'user_bank_holder_no' => $data['user_bank_holder_no'],
										'user_bank_name_text' => $data['user_bank_name_text'],
										'user_bank_name' => $data['user_bank_name'],
										'user_bank_branch_text' => $data['user_bank_branch_text'],
										'user_bank_branch' => $data['user_bank_branch'],
										'user_bank_state_text' => $data['user_bank_state_text'],
										'user_bank_state' => $data['user_bank_state'],
										'user_bank_country_text' => $data['user_bank_country_text'],
										'user_bank_country' => $data['user_bank_country'],
										'user_bank_created_by' => $data['log_created_by'],
										'user_bank_modified_by' => $data['log_modified_by']
								));
				$UBank->where(array(
								'user_profile_id' => $profile,
								'user_bank_id' => $bank,
						));
				$UBank->setCacheName('user_access_' . $bank . '_' . $profile);
				$UBank->execute();
				if ($UBank->affectedRows()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Update Password
	 *
	 * @return boolean
	 *
	 */
	public function updatePassword($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$password = $data['user_access_password'];
			$Bcrypt = new Bcrypt(array(
					'salt' => SYSTEM_BCRYPT_SALT,
					'cost' => SYSTEM_BCRYPT_COST
			));
			$data['user_access_password'] = $Bcrypt->create($password);

			$UPassword = $this->getDatabase();
			$UPassword->update();
			$UPassword->table('user_access');
			$UPassword
					->set(
							array(
									'user_access_password' => $data['user_access_password'],
									'user_access_password_retrieve' => $password,
									'user_password_update_status' => 1,
									'user_access_modified_date' => $data['log_modified_date'],
									'user_access_modified_by' => $data['log_modified_by']
							));
			$UPassword->where(array(
							'user_access_id' => $id
					));
			$UPassword->setCacheName('user_access_' . $id);
			$UPassword->execute();
			if ($UPassword->affectedRows()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Update Security
	 *
	 * @return boolean
	 *
	 */
	public function updateSecurity($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$password = $data['user_access_security'];
			$Bcrypt = new Bcrypt(array(
					'salt' => SYSTEM_BCRYPT_SALT,
					'cost' => SYSTEM_BCRYPT_COST
			));
			$data['user_access_security'] = $Bcrypt->create($password);

			$UPassword = $this->getDatabase();
			$UPassword->update();
			$UPassword->table('user_access');
			$UPassword
					->set(
							array(
									'user_access_security' => $data['user_access_security'],
									'user_access_security_retrieve' => $password,
									'user_security_update_status' => 1,
									'user_access_modified_date' => $data['log_modified_date'],
									'user_access_modified_by' => $data['log_modified_by']
							));
			$UPassword->where(array(
							'user_access_id' => $id
					));
			$UPassword->setCacheName('user_access_' . $id);
			$UPassword->execute();
			if ($UPassword->affectedRows()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Update Profile
	 *
	 * @return boolean
	 *
	 */
	public function updateProfile($id = null, $data = null) {
		if (!empty($id) && count($data) > 0) {
			$id = $this->getProfileID($id);
			if ($id > 0) {
				if (isset($data['user_profile_fullname'])) {
					$data['user_profile_firstname'] = (isset($data['user_profile_fullname']) ? $data['user_profile_fullname'] : null);
				} else {
					$data['user_profile_firstname'] = (isset($data['user_profile_firstname']) ? $data['user_profile_firstname'] : null);
				}
				$data['user_profile_lastname'] = (isset($data['user_profile_lastname']) ? $data['user_profile_lastname'] : null);

				if (isset($data['user_profile_dob']['day'])) {
					$dob_day = $data['user_profile_dob']['day'];
				} else {
					$dob_day = '01';
				}
				if (isset($data['user_profile_dob']['month'])) {
					$dob_month = $data['user_profile_dob']['month'];
				} else {
					$dob_month = '01';
				}
				if (isset($data['user_profile_dob']['year'])) {
					$dob_year = $data['user_profile_dob']['year'];
				} else {
					$dob_year = '0001';
				}
				$data['user_profile_dob'] = $dob_year . '-' . $dob_month . '-' . $dob_day . ' 00:00:00';

				$UProfile = $this->getDatabase();
				$UProfile->update();
				$UProfile->table('user_profile');
				$UProfile
						->set(
								array(
										'user_profile_designation' => $data['user_profile_designation'],
										'user_profile_firstname' => $data['user_profile_firstname'],
										'user_profile_lastname' => $data['user_profile_lastname'],
										'user_profile_nric_passport' => $data['user_profile_nric_passport'],
										'user_profile_gender' => $data['user_profile_gender'],
										'user_profile_dob' => $data['user_profile_dob'],
										'user_profile_nationality' => $data['user_profile_nationality'],
										'user_profile_email_address' => $data['user_profile_email_address'],
										'user_profile_mobile_no' => $data['user_profile_mobile_no'],
										'user_profile_telephone_no' => $data['user_profile_telephone_no'],
										'user_profile_office_no' => $data['user_profile_office_no'],
										'user_profile_fax_no' => $data['user_profile_fax_no'],
										'user_profile_modified_date' => $data['log_modified_date'],
										'user_profile_modified_by' => $data['log_modified_by']
								));
				$UProfile->where(array(
								'user_profile_id' => $id
						));
				$UProfile->setCacheName('user_profile_' . $id);
				$UProfile->execute();
				if ($UProfile->affectedRows()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Generate Username
	 */
	public function generateUsername($open_tag = null, $min = null, $max = null, $end_tag = null) {
		$user_code = 'TNM';
		$user_tail_code = '';
		$length_min = 1;
		$length_max = 9999;
		if (!empty($open_tag)) {
			$user_code = $open_tag;
		} elseif (defined(USERNAME_CODE)) {
			$user_code = USERNAME_CODE;
		}
		if (!empty($end_tag)) {
			$user_tail_code = $end_tag;
		} elseif (defined(USERNAME_TAIL_CODE)) {
			$user_tail_code = USERNAME_TAIL_CODE;
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

		$DBQUsername = $this->getDatabase();
		$DBQUsername->select();
		$DBQUsername->columns(array(
						'id' => 'username_id'
				));
		$DBQUsername->from(array(
						'u' => 'username'
				));
		$DBQUsername->where(array(
						'u.user_access_username like "' . $user_code . $datetime->format('ym') . '%' . $user_tail_code . '"',
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
						'user_access_username' => strtoupper($user_code . $datetime->format('ym') . $current_id . $user_tail_code)
				);
			}
			if (count($username) > 0) {
				$DBUsername = $this->getDatabase();
				$DBUsername->insert();
				$DBUsername->into('username');
				$DBUsername->columns(array(
								'user_access_username'
						));
				$DBUsername->values($username, 'multiple');
				$DBUsername->execute();
				if (!$DBUsername->affectedRows()) {
					return false;
				}
			}
		}
		$DBQUsername = $this->getDatabase();
		$DBQUsername->select();
		$DBQUsername->columns(array(
						'username' => 'user_access_username'
				));
		$DBQUsername->from(array(
						'u' => 'username'
				));
		$DBQUsername->where(array(
						'u.user_access_username like "' . $user_code . $datetime->format('ym') . '%' . $user_tail_code . '"',
						'u.username_status = 1'
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
			$this->generateUsername($user_code, $length_max, ($length_max * 2 + $length_min), $user_tail_code);
		}
		return $username;
	}
}

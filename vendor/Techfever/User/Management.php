<?php

namespace Techfever\User;

use Zend\Db\Sql\Expression;
use Zend\Crypt\Password\Bcrypt;
use Techfever\Address\Address;
use Techfever\Bank\Bank;
use Techfever\Nationality\Nationality;
use Techfever\Parameter\Parameter;
use Techfever\Exception;
use Techfever\Wallet\Wallet;
use Techfever\Functions\General as GeneralBase;

class Management extends GeneralBase {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'access' => 0,
			'profile' => 0,
			'address' => 0,
			'bank' => 0 
	);
	
	/**
	 * Nationality object
	 *
	 * @var General
	 */
	protected $nationalityobject = null;
	
	/**
	 * Address object
	 *
	 * @var General
	 */
	protected $addressobject = null;
	
	/**
	 * Bank object
	 *
	 * @var General
	 */
	protected $bankobject = null;
	
	/**
	 * Wallet object
	 *
	 * @var General
	 */
	protected $walletobject = null;
	private $rank = array ();
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
		
		$this->nationalityobject = new Nationality ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		$this->addressobject = new Address ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		$this->bankobject = new Bank ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		$this->walletobject = new Wallet ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
	}
	
	/**
	 * Nationality Object
	 */
	public function getNationality() {
		if (is_object ( $this->nationalityobject )) {
			return $this->nationalityobject;
		}
		return null;
	}
	
	/**
	 * Address Object
	 */
	public function getAddress() {
		if (is_object ( $this->addressobject )) {
			return $this->addressobject;
		}
		return null;
	}
	
	/**
	 * Bank Object
	 */
	public function getBank() {
		if (is_object ( $this->bankobject )) {
			return $this->bankobject;
		}
		return null;
	}
	
	/**
	 * Bank Object
	 */
	public function getWallet() {
		if (is_object ( $this->walletobject )) {
			return $this->walletobject;
		}
		return null;
	}
	
	/**
	 * createUser
	 */
	public function createUser($data) {
		$status = true;
		$rawdata = $data;
		if (is_array ( $rawdata ) && count ( $rawdata ) > 0) {
			foreach ( $rawdata as $data_key => $data_value ) {
				if (preg_match ( '/val\{(.*)\}$/', $data_value )) {
					$variable = $data_value;
					$variable = str_replace ( 'val{', '', $variable );
					$variable = str_replace ( '}', '', $variable );
					$variable = strtolower ( $variable );
					
					if (array_key_exists ( $variable, $data )) {
						$valuecapture = $data [$variable];
						if ($variable === 'user_profile_nationality' && $data [$variable] > 0) {
							$valuecapture = $this->getNationality ()->getCountryISO ( $data [$variable] );
						} elseif ($variable === 'user_address_country' && $data [$variable] > 0) {
							$valuecapture = $this->getAddress ()->getCountryISO ( $data [$variable] );
						} elseif ($variable === 'user_bank_country' && $data [$variable] > 0) {
							$valuecapture = $this->getBank ()->getCountryISO ( $data [$variable] );
						}
						$data [$data_key] = $valuecapture;
					}
				}
			}
		}
		
		if (! isset ( $data ['user_username'] )) {
			$data ['user_username'] = $this->generateUsername ( $data ['user_username_open_tag'], $data ['user_username_min'], $data ['user_username_max'], $data ['user_username_end_tag'], $data ['user_rank_group_id'] );
		}
		if (! isset ( $data ['user_status'] )) {
			$data ['user_status'] = "0";
		}
		$data ['log_activated_date'] = $data ['log_created_date'];
		if (isset ( $data ['user_activation_status'] ) && $data ['user_activation_status'] == "False") {
			$data ['log_activated_date'] = '0000-00-00 00:00:00';
		}
		if (! isset ( $data ['user_profile_designation'] )) {
			$data ['user_profile_designation'] = "";
		}
		if (! isset ( $data ['user_profile_firstname'] )) {
			$data ['user_profile_firstname'] = "";
		}
		if (! isset ( $data ['user_profile_lastname'] )) {
			$data ['user_profile_lastname'] = "";
		}
		if (! isset ( $data ['user_profile_nric_passport'] )) {
			$data ['user_profile_nric_passport'] = "";
		}
		if (! isset ( $data ['user_profile_gender'] )) {
			$data ['user_profile_gender'] = "";
		}
		if (! isset ( $data ['user_profile_dob'] )) {
			$data ['user_profile_dob'] = "";
		}
		if (! isset ( $data ['user_profile_nationality'] )) {
			$data ['user_profile_nationality'] = "";
		}
		if (! isset ( $data ['user_profile_email_address'] )) {
			$data ['user_profile_email_address'] = "";
		}
		if (! isset ( $data ['user_profile_mobile_no'] )) {
			$data ['user_profile_mobile_no'] = "";
		}
		if (! isset ( $data ['user_profile_telephone_no'] )) {
			$data ['user_profile_telephone_no'] = "";
		}
		if (! isset ( $data ['user_profile_office_no'] )) {
			$data ['user_profile_office_no'] = "";
		}
		if (! isset ( $data ['user_profile_fax_no'] )) {
			$data ['user_profile_fax_no'] = "";
		}
		$password_retrieve = null;
		if (isset ( $data ['user_access_password'] )) {
			$password_retrieve = $data ['user_access_password'];
		} elseif (isset ( $data ['user_access_password_retrieve'] )) {
			$security_retrieve = $data ['user_access_password_retrieve'];
		} elseif (isset ( $data ['user_profile_nric_passport'] )) {
			$password_retrieve = $data ['user_profile_nric_passport'];
		}
		$security_retrieve = null;
		if (isset ( $data ['user_access_security'] )) {
			$security_retrieve = $data ['user_access_security'];
		} elseif (isset ( $data ['user_access_security_retrieve'] )) {
			$security_retrieve = $data ['user_access_security_retrieve'];
		} elseif (isset ( $data ['user_profile_nric_passport'] )) {
			$security_retrieve = $data ['user_profile_nric_passport'];
		}
		$Bcrypt = new Bcrypt ( array (
				'salt' => SYSTEM_BCRYPT_SALT,
				'cost' => SYSTEM_BCRYPT_COST 
		) );
		$data ['user_security'] = $Bcrypt->create ( $security_retrieve );
		$data ['user_password'] = $Bcrypt->create ( $password_retrieve );
		$data ['user_security_retrieve'] = $security_retrieve;
		$data ['user_password_retrieve'] = $password_retrieve;
		
		$data ['user_rank_price_dl'] = 0;
		$data ['user_rank_price_pv'] = 0;
		$data ['user_wallet_amount'] = 0;
		$data ['user_wallet_comment'] = "";
		$data ['user_rank_price_status'] = False;
		$data ['user_rank_wallet_type'] = (array_key_exists ( 'user_rank_wallet_type', $data ) ? $data ['user_rank_wallet_type'] : $this->getWallet ()->getActionType ( 'register', 0 ));
		$Rank = $this->getRank ( $data ['user_rank_group_id'], $data ['user_rank'] );
		if ($Rank->verifyRank ()) {
			$RankData = $Rank->getRank ( $data ['user_rank'] );
			$data ['user_rank_price_dl'] = $RankData ['price_dl'];
			$data ['user_rank_price_pv'] = $RankData ['price_pv'];
			$data ['user_wallet_amount'] = $data ['user_rank_price_pv'];
			$data ['user_rank_price_status'] = ($RankData ['price_status'] == "1" ? True : False);
			if ($data ['user_wallet_deduct_status'] == "True" && ($data ['user_rank_price_status'] && $data ['user_rank_price_pv'] > 0)) {
				$walletoption = array (
						'action' => 'register',
						'from_user' => $this->getUserAccess ()->getID (),
						'to_user' => 1,
						'from_wallet_type' => $data ['user_rank_wallet_type'],
						'to_wallet_type' => $data ['user_rank_wallet_type'],
						'from_user_rank' => $this->getUserAccess ()->getRankID (),
						'to_user_rank' => 8888,
						'transaction_status' => 3,
						'transaction' => $data ['transaction'] 
				);
				$this->getWallet ()->setOptions ( $walletoption );
				if (! $this->getWallet ()->validUserPocketAmount ( $data ['user_rank_price_pv'] )) {
					$status = false;
				} elseif (! $this->getWallet ()->createUserHistory ( $data )) {
					$status = false;
				}
			}
		} else {
			$status = false;
		}
		
		$access_id = 0;
		$profile_id = 0;
		$address_id = 0;
		$bank_id = 0;
		if ($status) {
			$IUser = $this->getDatabase ();
			$IUser->insert ();
			$IUser->into ( 'user_access' );
			$IUser->values ( array (
					'user_access_username' => $data ['user_username'],
					'user_access_status' => $data ['user_status'],
					'user_rank_id' => $data ['user_rank'],
					'user_access_security' => $data ['user_security'],
					'user_access_security_retrieve' => $data ['user_security_retrieve'],
					'user_access_password' => $data ['user_password'],
					'user_access_password_retrieve' => $data ['user_password_retrieve'],
					'user_access_created_date' => $data ['log_created_date'],
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_activated_date' => $data ['log_activated_date'],
					'user_access_created_by' => $data ['log_created_by'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$IUser->execute ();
			if ($IUser->affectedRows ()) {
				$access_id = $IUser->getLastGeneratedValue ();
				$this->setOption ( 'access', $access_id );
				$placement = null;
				$sponsor = null;
				
				if (isset ( $data ['user_hierarchy_placement'] )) {
					$data ['user_hierarchy_placement_username'] = $data ['user_hierarchy_placement'];
					$QPlacement = $this->getDatabase ();
					$QPlacement->select ();
					$QPlacement->columns ( array (
							'sponsor' => 'user_hierarchy_sponsor',
							'placement' => 'user_hierarchy_placement' 
					) );
					$QPlacement->from ( array (
							'uh' => 'user_hierarchy' 
					) );
					$QPlacement->where ( array (
							'uh.user_access_username = "' . strtoupper ( $data ['user_hierarchy_placement'] ) . '"' 
					) );
					$QPlacement->limit ( 1 );
					$QPlacement->execute ();
					if ($QPlacement->hasResult ()) {
						$placementdata = $QPlacement->current ();
						$placement = $placementdata ['placement'];
						
						$PlacementParameter = new Parameter ( array (
								'key' => 'user_hierarchy_placement_position',
								'servicelocator' => $this->getServiceLocator () 
						) );
						$valuePlacement = $PlacementParameter->getValueByKey ( $data ['user_hierarchy_placement_position'] );
						if (strlen ( $valuePlacement ) > 0) {
							$placement .= '|L:' . $valuePlacement;
							$data ['user_hierarchy_placement'] = $placement;
						}
						
						$DBVerify = $this->getDatabase ();
						$DBVerify->select ();
						$DBVerify->columns ( array (
								'id' => 'user_access_id' 
						) );
						$DBVerify->from ( array (
								'uh' => 'user_hierarchy' 
						) );
						$DBVerify->where ( array (
								'uh.user_hierarchy_placement = "' . $placement . '"',
								'uh.user_hierarchy_placement_username = "' . strtoupper ( $data ['user_hierarchy_placement'] ) . '"' 
						) );
						$DBVerify->execute ();
						if ($DBVerify->hasResult ()) {
							$status = false;
						}
					} else {
						$status = false;
					}
				} else {
					$data ['user_hierarchy_placement_username'] = $this->getUsername ( 1 );
					$data ['user_hierarchy_placement'] = 'L:1|L:1';
				}
				
				if (isset ( $data ['user_hierarchy_sponsor'] )) {
					$data ['user_hierarchy_sponsor_username'] = $data ['user_hierarchy_sponsor'];
					$QSponsor = $this->getDatabase ();
					$QSponsor->select ();
					$QSponsor->columns ( array (
							'sponsor' => 'user_hierarchy_sponsor',
							'placement' => 'user_hierarchy_placement' 
					) );
					$QSponsor->from ( array (
							'uh' => 'user_hierarchy' 
					) );
					$QSponsor->where ( array (
							'uh.user_access_username = "' . strtoupper ( $data ['user_hierarchy_sponsor'] ) . '"' 
					) );
					$QSponsor->limit ( 1 );
					$QSponsor->execute ();
					if ($QSponsor->hasResult ()) {
						$sponsordata = $QSponsor->current ();
						$sponsor = $sponsordata ['sponsor'];
						
						$DBVerify = $this->getDatabase ();
						$DBVerify->select ();
						$DBVerify->columns ( array (
								'total' => new \Zend\Db\Sql\Expression ( 'COUNT(uh.user_hierarchy_sponsor_username)' ) 
						) );
						$DBVerify->from ( array (
								'uh' => 'user_hierarchy' 
						) );
						$DBVerify->where ( array (
								'uh.user_hierarchy_sponsor_username = "' . strtoupper ( $data ['user_hierarchy_sponsor'] ) . '"' 
						) );
						$DBVerify->group ( array (
								'user_hierarchy_sponsor_username' 
						) );
						$DBVerify->limit ( 1 );
						$DBVerify->execute ();
						$total = 1;
						if ($DBVerify->hasResult ()) {
							$verifydata = $DBVerify->current ();
							$total = $verifydata ['total'] + 1;
						}
						$sponsor .= '|L:' . $total;
						$data ['user_hierarchy_sponsor'] = $sponsor;
					} else {
						$status = false;
					}
				} else {
					$data ['user_hierarchy_sponsor_username'] = $this->getUsername ( 1 );
					$data ['user_hierarchy_sponsor'] = 'L:1|L:1';
				}
				$IHierarchy = $this->getDatabase ();
				$IHierarchy->insert ();
				$IHierarchy->into ( 'user_hierarchy' );
				$IHierarchy->values ( array (
						'user_access_username' => strtoupper ( $data ['user_username'] ),
						'user_access_id' => $access_id,
						'user_hierarchy_sponsor' => $data ['user_hierarchy_sponsor'],
						'user_hierarchy_sponsor_username' => strtoupper ( $data ['user_hierarchy_sponsor_username'] ),
						'user_hierarchy_placement' => $data ['user_hierarchy_placement'],
						'user_hierarchy_placement_username' => strtoupper ( $data ['user_hierarchy_placement_username'] ),
						'user_hierarchy_created_date' => $data ['log_created_date'],
						'user_hierarchy_modified_date' => $data ['log_modified_date'],
						'user_hierarchy_created_by' => $data ['log_created_by'],
						'user_hierarchy_modified_by' => $data ['log_modified_by'] 
				) );
				$IHierarchy->execute ();
				if (! $IHierarchy->affectedRows ()) {
					$status = false;
				}
				
				if ($status) {
					if (! $this->getWallet ()->createUserType ( $access_id )) {
						$status = false;
					}
				}
				
				if ($status) {
					if (isset ( $data ['user_profile_fullname'] )) {
						$data ['user_profile_firstname'] = (isset ( $data ['user_profile_fullname'] ) ? $data ['user_profile_fullname'] : null);
					} else {
						$data ['user_profile_firstname'] = (isset ( $data ['user_profile_firstname'] ) ? $data ['user_profile_firstname'] : null);
					}
					$data ['user_profile_lastname'] = (isset ( $data ['user_profile_lastname'] ) ? $data ['user_profile_lastname'] : null);
					
					if (isset ( $data ['user_profile_dob'] ['day'] )) {
						$dob_day = $data ['user_profile_dob'] ['day'];
					} else {
						$dob_day = '01';
					}
					if (isset ( $data ['user_profile_dob'] ['month'] )) {
						$dob_month = $data ['user_profile_dob'] ['month'];
					} else {
						$dob_month = '01';
					}
					if (isset ( $data ['user_profile_dob'] ['year'] )) {
						$dob_year = $data ['user_profile_dob'] ['year'];
					} else {
						$dob_year = '0001';
					}
					$data ['user_profile_dob'] = $dob_year . '-' . $dob_month . '-' . $dob_day . ' 00:00:00';
					
					$IProfile = $this->getDatabase ();
					$IProfile->insert ();
					$IProfile->into ( 'user_profile' );
					$IProfile->values ( array (
							'user_profile_designation' => $data ['user_profile_designation'],
							'user_profile_firstname' => $data ['user_profile_firstname'],
							'user_profile_lastname' => $data ['user_profile_lastname'],
							'user_profile_nric_passport' => $data ['user_profile_nric_passport'],
							'user_profile_gender' => $data ['user_profile_gender'],
							'user_profile_dob' => $data ['user_profile_dob'],
							'user_profile_nationality' => $data ['user_profile_nationality'],
							'user_profile_email_address' => $data ['user_profile_email_address'],
							'user_profile_mobile_no' => $data ['user_profile_mobile_no'],
							'user_profile_telephone_no' => $data ['user_profile_telephone_no'],
							'user_profile_office_no' => $data ['user_profile_office_no'],
							'user_profile_fax_no' => $data ['user_profile_fax_no'],
							'user_profile_created_date' => $data ['log_created_date'],
							'user_profile_modified_date' => $data ['log_modified_date'],
							'user_profile_created_by' => $data ['log_created_by'],
							'user_profile_modified_by' => $data ['log_modified_by'] 
					) );
					$IProfile->execute ();
					if ($IProfile->affectedRows ()) {
						$profile_id = $IProfile->getLastGeneratedValue ();
						$this->setOption ( 'profile', $IProfile->getLastGeneratedValue () );
						
						$UAccess = $this->getDatabase ();
						$UAccess->update ();
						$UAccess->table ( 'user_access' );
						$UAccess->set ( array (
								'user_profile_id' => $profile_id 
						) );
						$UAccess->where ( array (
								'user_access_id = "' . $access_id . '"' 
						) );
						$UAccess->execute ();
						if ($UAccess->affectedRows ()) {
							if (isset ( $data ['user_address_country'] ) && $data ['user_address_country'] > 0) {
								$address_id = $this->getAddress ()->createUserAddress ( $profile_id, $data );
								if (is_numeric ( $address_id ) && $address_id > 0) {
									$this->setOption ( 'address', $address_id );
								} else {
									$this->setOption ( 'address', 0 );
								}
							} else {
								$this->setOption ( 'address', 0 );
							}
							
							if (isset ( $data ['user_bank_holder_name'] ) && $data ['user_bank_holder_name'] > 0) {
								$bank_id = $this->getBank ()->createUserBank ( $profile_id, $data );
								if (is_numeric ( $bank_id ) && $bank_id > 0) {
									$this->setOption ( 'bank', $bank_id );
								} else {
									$this->setOption ( 'bank', 0 );
								}
							} else {
								$this->setOption ( 'bank', 0 );
							}
							
							if ($this->getOption ( 'bank' ) > 0 || $this->getOption ( 'address' ) > 0) {
								$UProfile = $this->getDatabase ();
								$UProfile->update ();
								$UProfile->table ( 'user_profile' );
								$UProfile->set ( array (
										'user_bank_id' => $bank_id,
										'user_address_id' => $address_id 
								) );
								$UProfile->where ( array (
										'user_profile_id = "' . $profile_id . '"' 
								) );
								$UProfile->execute ();
								if (! $UProfile->affectedRows ()) {
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
		} else {
			$status = false;
		}
		
		if ($status) {
			
			return $access_id;
		} else {
			if ($data ['user_rank_price_status'] && $data ['user_rank_price_pv'] > 0) {
				$walletoption = array (
						'action' => 'register',
						'from_user' => 1,
						'to_user' => $this->getUserAccess ()->getID (),
						'from_wallet_type' => $data ['user_rank_wallet_type'],
						'to_wallet_type' => $data ['user_rank_wallet_type'],
						'from_user_rank' => 8888,
						'to_user_rank' => $this->getUserAccess ()->getUserRankID (),
						'transaction_status' => 3,
						'transaction' => $data ['transaction'] 
				);
				$this->getWallet ()->setOptions ( $walletoption );
				$this->getWallet ()->createUserHistory ( $data );
			}
			$this->setOption ( 'access', $access_id );
			$this->setOption ( 'profile', $profile_id );
			$this->setOption ( 'bank', $bank_id );
			$this->setOption ( 'address', $address_id );
			$this->deleteUser ( true );
			return false;
		}
	}
	
	/**
	 * deleteUser
	 */
	public function deleteUser($forever = false) {
		$access = $this->getOption ( 'access' );
		$profile = $this->getOption ( 'profile' );
		if ($forever) {
			if ($access > 1) {
				$DUser = $this->getDatabase ();
				$DUser->delete ();
				$DUser->from ( 'user_access' );
				$DUser->where ( array (
						'user_access_id' => $access 
				) );
				$DUser->execute ();
				
				$DHierarchy = $this->getDatabase ();
				$DHierarchy->delete ();
				$DHierarchy->from ( 'user_hierarchy' );
				$DHierarchy->where ( array (
						'user_access_id' => $access 
				) );
				$DHierarchy->execute ();
				
				$this->getWallet ()->deleteUserType ( $access, True );
			}
			
			if ($profile > 1) {
				$DProfile = $this->getDatabase ();
				$DProfile->delete ();
				$DProfile->from ( 'user_profile' );
				$DProfile->where ( array (
						'user_profile_id' => $profile 
				) );
				$DProfile->execute ();
				
				$this->getAddress ()->deleteUserAddress ( $profile, True );
				
				$this->getBank ()->deleteUserBank ( $profile, True );
			}
		} else {
			if ($access > 1) {
				$UUser = $this->getDatabase ();
				$UUser->update ();
				$UUser->table ( 'user_access' );
				$UUser->set ( array (
						'user_access_delete_status' => '1' 
				) );
				$UUser->where ( array (
						'user_access_id' => $access 
				) );
				$UUser->execute ();
				
				$this->getWallet ()->deleteUserType ( $access, False );
			}
			
			if ($profile > 1) {
				
				$UProfile = $this->getDatabase ();
				$UProfile->update ();
				$UProfile->table ( 'user_profile' );
				$UProfile->set ( array (
						'user_profile_delete_status' => '1' 
				) );
				$UProfile->where ( array (
						'user_profile_id' => $profile 
				) );
				$UProfile->execute ();
				
				$this->getAddress ()->deleteUserAddress ( $profile, False );
				
				$this->getBank ()->deleteUserBank ( $profile, False );
			}
		}
		return true;
	}
	
	/**
	 * getData
	 */
	public function getUsername($id = null) {
		$username = null;
		if ($id > 0) {
			$this->setOption ( 'access', $id );
		}
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$QUser->where ( array (
				'ua.user_access_id' => $this->getOption ( 'access' ),
				'ua.user_access_delete_status' => '0' 
		) );
		$QUser->execute ();
		if ($QUser->hasResult ()) {
			$data = $QUser->current ();
			$username = $data ['user_access_username'];
		}
		return $username;
	}
	public function getRank($rank_group = null, $rank_id = null) {
		if (! array_key_exists ( $rank_group, $this->rank ) || ! is_array ( $this->rank [$rank_group] ) || ((! empty ( $rank_group ) && $rank_group > 0) || (! empty ( $rank_id ) && $rank_id > 0))) {
			$this->rank [$rank_group] = new Rank ( array (
					'group' => $rank_group,
					'id' => $rank_id,
					'servicelocator' => $this->getServiceLocator () 
			) );
		}
		return $this->rank [$rank_group];
	}
	
	/**
	 * getData
	 */
	public function getData($id = null, $rank_group = null) {
		$data = array ();
		if ($id > 0) {
			$this->setOption ( 'access', $id );
		}
		$orderstr = null;
		
		$RankAll = array ();
		if (! empty ( $rank_group ) && $rank_group > 0) {
			$RankAll = $this->getRank ( $rank_group )->getRankIDAll ();
		}
		
		$PlacementParameter = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$ActivationStatusParameter = new Parameter ( array (
				'key' => 'user_access_activation_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$DesignationParameter = new Parameter ( array (
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$GenderParameter = new Parameter ( array (
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$QUser->join ( array (
				'uh' => 'user_hierarchy' 
		), 'uh.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$QUser->join ( array (
				'up' => 'user_profile' 
		), 'up.user_profile_id  = ua.user_profile_id', array (
				'*' 
		) );
		$where = array (
				'ua.user_access_id' => $this->getOption ( 'access' ),
				'up.user_profile_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		if (is_array ( $RankAll ) && count ( $RankAll ) > 0) {
			$where [] = 'ua.user_rank_id in (' . implode ( ', ', $RankAll ) . ')';
		}
		$QUser->where ( $where );
		$QUser->execute ();
		if ($QUser->hasResult ()) {
			$data = $QUser->current ();
			$this->setOption ( 'profile', $data ['user_profile_id'] );
			$this->setOption ( 'address', $data ['user_address_id'] );
			$this->setOption ( 'bank', $data ['user_bank_id'] );
			
			$data ['user_access_password'] = '';
			$data ['user_access_password_confirmation'] = '';
			$data ['user_access_security'] = '';
			$data ['user_access_security_confirmation'] = '';
			
			$cryptID = $this->Encrypt ( $data ['user_access_id'] );
			$data ['id'] = $cryptID;
			
			$data ['modify_value'] = $cryptID;
			
			$data ['user_access_activated_date_format'] = "";
			if ($data ['user_access_activated_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_activated_date'] );
				$data ['user_access_activated_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_access_created_date_format'] = "";
			if ($data ['user_access_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_created_date'] );
				$data ['user_access_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_access_expired_date_format'] = "";
			if ($data ['user_access_expired_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_expired_date'] );
				$data ['user_access_expired_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_access_last_login_date_format'] = "";
			if ($data ['user_access_last_login_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_last_login_date'] );
				$data ['user_access_last_login_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_access_modified_date_format'] = "";
			if ($data ['user_access_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_modified_date'] );
				$data ['user_access_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_hierarchy_created_date_format'] = "";
			if ($data ['user_hierarchy_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_hierarchy_created_date'] );
				$data ['user_hierarchy_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_hierarchy_modified_date_format'] = "";
			if ($data ['user_hierarchy_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_hierarchy_modified_date'] );
				$data ['user_hierarchy_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_profile_created_date_format'] = "";
			if ($data ['user_profile_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_profile_created_date'] );
				$data ['user_profile_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_profile_modified_date_format'] = "";
			if ($data ['user_profile_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_profile_modified_date'] );
				$data ['user_profile_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_profile_dob_format'] = "";
			if ($data ['user_profile_dob'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_profile_dob'] );
				$data ['user_profile_dob_format'] = $datetime->format ( 'd-F-Y' );
			}
			
			$Rank = $this->getRank ( null, $data ['user_rank_id'] );
			$data ['user_rank_text'] = $data ['user_rank_id'];
			$valueRank = $Rank->getMessage ( $data ['user_rank_id'] );
			if (strlen ( $valueRank ) > 0) {
				$data ['user_rank_text'] = $valueRank;
			}
			$data ['user_rank_group_id'] = $Rank->getRankGroup ( $data ['user_rank_id'] );
			
			$data ['user_profile_nationality_text'] = $this->getNationality ()->getCountryName ( $data ['user_profile_nationality'] );
			
			$data ['user_profile_designation_text'] = $data ['user_profile_designation'];
			$valueDesignation = $DesignationParameter->getMessageByKey ( $data ['user_profile_designation_text'] );
			if (strlen ( $valueDesignation ) > 0) {
				$data ['user_profile_designation_text'] = $valueDesignation;
			}
			
			$data ['user_profile_gender_text'] = $data ['user_profile_gender'];
			$valueGender = $GenderParameter->getMessageByKey ( $data ['user_profile_gender_text'] );
			if (strlen ( $valueGender ) > 0) {
				$data ['user_profile_gender_text'] = $valueGender;
			}
			$data ['user_access_status_text'] = $data ['user_access_status'];
			$valueStatus = $StatusParameter->getMessageByValue ( $data ['user_access_status_text'] );
			if (strlen ( $valueStatus ) > 0) {
				$data ['user_access_status_text'] = $valueStatus;
			}
			
			$data ['user_access_activation_status'] = 1;
			if ($data ['user_access_activated_date'] == "0000-00-00 00:00:00") {
				$data ['user_access_activation_status'] = 0;
			}
			
			$data ['user_access_activation_status_text'] = $data ['user_access_activation_status'];
			$valueActivationStatus = $ActivationStatusParameter->getMessageByValue ( $data ['user_access_activation_status_text'] );
			if (strlen ( $valueStatus ) > 0) {
				$data ['user_access_activation_status_text'] = $valueActivationStatus;
			}
			
			$data ['user_hierarchy_placement_position_text'] = substr ( $data ['user_hierarchy_placement'], - 1 );
			$valuePlacement = $PlacementParameter->getMessageByValue ( $data ['user_hierarchy_placement_position_text'] );
			if (strlen ( $valuePlacement ) > 0) {
				$data ['user_hierarchy_placement_position_text'] = $valuePlacement;
			}
			
			$data ['user_profile_fullname'] = $data ['user_profile_firstname'] . (! empty ( $data ['user_profile_lastname'] ) ? ' ' . $data ['user_profile_lastname'] : null);
			
			if ($this->getOption ( 'address' ) > 0) {
				$this->getAddress ()->setOption ( 'profile_id', $this->getOption ( 'profile' ) );
				$this->getAddress ()->setOption ( 'address_id', $this->getOption ( 'address' ) );
				$rawdata = $this->getAddress ()->getUserAddress ( $this->getOption ( 'address' ) );
				$this->getAddress ()->clearUserAddressData ();
				if (! is_array ( $rawdata )) {
					$rawdata = array ();
					$rawdata ['user_address_created_date_format'] = "";
					$rawdata ['user_address_created_date'] = "";
					$rawdata ['user_address_modified_date_format'] = "";
					$rawdata ['user_address_modified_date'] = "";
				}
				$data = array_merge ( $data, $rawdata );
				$data ['user_address_created_date_format'] = "";
				if ($data ['user_address_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $data ['user_address_created_date'] );
					$data ['user_address_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				$data ['user_address_modified_date_format'] = "";
				if ($data ['user_address_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $data ['user_address_modified_date'] );
					$data ['user_address_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
			}
			
			if ($this->getOption ( 'bank' ) > 0) {
				$this->getBank ()->setOption ( 'profile_id', $this->getOption ( 'profile' ) );
				$this->getBank ()->setOption ( 'bank_id', $this->getOption ( 'bank' ) );
				$rawdata = $this->getBank ()->getUserBank ( $this->getOption ( 'bank' ) );
				$this->getBank ()->clearUserBankData ();
				if (! is_array ( $rawdata )) {
					$rawdata = array ();
					$rawdata ['user_bank_created_date_format'] = "";
					$rawdata ['user_bank_created_date'] = "";
					$rawdata ['user_bank_modified_date_format'] = "";
					$rawdata ['user_bank_modified_date'] = "";
				}
				$data = array_merge ( $data, $rawdata );
				$data ['user_bank_created_date_format'] = "";
				if ($data ['user_bank_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $data ['user_bank_created_date'] );
					$data ['user_bank_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				$data ['user_bank_modified_date_format'] = "";
				if ($data ['user_bank_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $data ['user_bank_modified_date'] );
					$data ['user_bank_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
			}
			
			$this->getWallet ()->setOption ( 'from_user', $data ['user_access_id'] );
			$user_wallet = $this->getWallet ()->getWalletData ();
			$data ['user_wallet'] = array ();
			if (is_array ( $user_wallet ) && count ( $user_wallet ) > 0) {
				$data ['user_wallet'] = $user_wallet;
				foreach ( $user_wallet as $user_wallet_value ) {
					$data ['user_wallet_' . $user_wallet_value ['key'] . '_amount'] = $user_wallet_value ['amount_total'];
					$data ['user_wallet_' . $user_wallet_value ['key']] = $user_wallet_value ['amount'];
				}
			}
		}
		ksort ( $data );
		if (count ( $data ) > 1) {
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * getListing
	 */
	public function getListingTotal($rank_group = 10000, $search = null, $encryted_id = false) {
		$orderstr = null;
		
		$RankAll = array ();
		if (! empty ( $rank_group ) && $rank_group > 0) {
			$RankAll = $this->getRank ( $rank_group )->getRankIDAll ();
		}
		
		$PlacementParameter = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$DesignationParameter = new Parameter ( array (
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$GenderParameter = new Parameter ( array (
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$QUser->join ( array (
				'uh' => 'user_hierarchy' 
		), 'uh.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$QUser->join ( array (
				'up' => 'user_profile' 
		), 'up.user_profile_id  = ua.user_profile_id', array (
				'*' 
		) );
		$where = array (
				'up.user_profile_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		if (is_array ( $RankAll ) && count ( $RankAll ) > 0) {
			$where [] = 'ua.user_rank_id in (' . implode ( ', ', $RankAll ) . ')';
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access', $search )) {
			$where = array_merge ( $where, $search ['user_access'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_hierarchy', $search )) {
			$where = array_merge ( $where, $search ['user_hierarchy'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_profile', $search )) {
			$where = array_merge ( $where, $search ['user_profile'] );
		}
		$QUser->where ( $where );
		$QUser->execute ();
		$count = 0;
		if ($QUser->hasResult ()) {
			$count = $QUser->count ();
		}
		return $count;
	}
	
	/**
	 * getListing
	 */
	public function getListing($rank_group = 10000, $search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		
		$RankAll = array ();
		if (! empty ( $rank_group ) && $rank_group > 0) {
			$RankAll = $this->getRank ( $rank_group )->getRankIDAll ();
		}
		
		$PlacementParameter = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$ActivationStatusParameter = new Parameter ( array (
				'key' => 'user_access_activation_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$DesignationParameter = new Parameter ( array (
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$GenderParameter = new Parameter ( array (
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$QUser->join ( array (
				'uh' => 'user_hierarchy' 
		), 'uh.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$QUser->join ( array (
				'up' => 'user_profile' 
		), 'up.user_profile_id  = ua.user_profile_id', array (
				'*' 
		) );
		$where = array (
				'up.user_profile_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		if (is_array ( $RankAll ) && count ( $RankAll ) > 0) {
			$where [] = 'ua.user_rank_id in (' . implode ( ', ', $RankAll ) . ')';
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access', $search )) {
			$where = array_merge ( $where, $search ['user_access'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_hierarchy', $search )) {
			$where = array_merge ( $where, $search ['user_hierarchy'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_profile', $search )) {
			$where = array_merge ( $where, $search ['user_profile'] );
		}
		$QUser->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'ua.user_access_username' 
			);
		}
		$QUser->order ( $order );
		if (isset ( $perpage )) {
			$QUser->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QUser->offset ( ( int ) $index );
		}
		$QUser->execute ();
		if ($QUser->hasResult ()) {
			$data = array ();
			$count = 1;
			while ( $QUser->valid () ) {
				$rawdata = $QUser->current ();
				$rawdata ['no'] = $count;
				
				$cryptID = $this->Encrypt ( $rawdata ['user_access_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['user_access_id']);
				
				$rawdata ['user_access_activated_date_format'] = "";
				if ($rawdata ['user_access_activated_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_activated_date'] );
					$rawdata ['user_access_activated_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_created_date_format'] = "";
				if ($rawdata ['user_access_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_created_date'] );
					$rawdata ['user_access_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_expired_date_format'] = "";
				if ($rawdata ['user_access_expired_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_expired_date'] );
					$rawdata ['user_access_expired_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_last_login_date_format'] = "";
				if ($rawdata ['user_access_last_login_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_last_login_date'] );
					$rawdata ['user_access_last_login_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_modified_date_format'] = "";
				if ($rawdata ['user_access_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_modified_date'] );
					$rawdata ['user_access_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_hierarchy_created_date_format'] = "";
				if ($rawdata ['user_hierarchy_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_hierarchy_created_date'] );
					$rawdata ['user_hierarchy_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_hierarchy_modified_date_format'] = "";
				if ($rawdata ['user_hierarchy_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_hierarchy_modified_date'] );
					$rawdata ['user_hierarchy_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_profile_created_date_format'] = "";
				if ($rawdata ['user_profile_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_profile_created_date'] );
					$rawdata ['user_profile_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_profile_modified_date_format'] = "";
				if ($rawdata ['user_profile_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_profile_modified_date'] );
					$rawdata ['user_profile_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_profile_dob_format'] = "";
				if ($rawdata ['user_profile_dob'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_profile_dob'] );
					$rawdata ['user_profile_dob_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['user_rank_text'] = $rawdata ['user_rank_id'];
				$valueRank = $this->getRank ( null, $rawdata ['user_rank_id'] )->getMessage ( $rawdata ['user_rank_id'] );
				if (strlen ( $valueRank ) > 0) {
					$rawdata ['user_rank_text'] = $valueRank;
				}
				
				$rawdata ['user_profile_nationality_text'] = $this->getNationality ()->getCountryName ( $rawdata ['user_profile_nationality'] );
				
				$rawdata ['user_profile_designation_text'] = $rawdata ['user_profile_designation'];
				$valueDesignation = $DesignationParameter->getMessageByKey ( $rawdata ['user_profile_designation_text'] );
				if (strlen ( $valueDesignation ) > 0) {
					$rawdata ['user_profile_designation_text'] = $valueDesignation;
				}
				
				$rawdata ['user_profile_gender_text'] = $rawdata ['user_profile_gender'];
				$valueGender = $GenderParameter->getMessageByKey ( $rawdata ['user_profile_gender_text'] );
				if (strlen ( $valueGender ) > 0) {
					$rawdata ['user_profile_gender_text'] = $valueGender;
				}
				
				$rawdata ['user_access_status_text'] = $rawdata ['user_access_status'];
				$valueStatus = $StatusParameter->getMessageByValue ( $rawdata ['user_access_status_text'] );
				if (strlen ( $valueStatus ) > 0) {
					$rawdata ['user_access_status_text'] = $valueStatus;
				}
				
				$rawdata ['user_access_activation_status'] = 1;
				if ($rawdata ['user_access_activated_date'] == "0000-00-00 00:00:00") {
					$rawdata ['user_access_activation_status'] = 0;
				}
				
				$rawdata ['user_access_activation_status_text'] = $rawdata ['user_access_activation_status'];
				$valueActivationStatus = $ActivationStatusParameter->getMessageByValue ( $rawdata ['user_access_activation_status_text'] );
				if (strlen ( $valueStatus ) > 0) {
					$rawdata ['user_access_activation_status_text'] = $valueActivationStatus;
				}
				
				$rawdata ['user_hierarchy_placement_position_text'] = substr ( $rawdata ['user_hierarchy_placement'], - 1 );
				$valuePlacement = $PlacementParameter->getMessageByValue ( $rawdata ['user_hierarchy_placement_position_text'] );
				if (strlen ( $valuePlacement ) > 0) {
					$rawdata ['user_hierarchy_placement_position_text'] = $valuePlacement;
				}
				
				$rawdata ['user_profile_fullname'] = $rawdata ['user_profile_firstname'] . (! empty ( $rawdata ['user_profile_lastname'] ) ? ' ' . $rawdata ['user_profile_lastname'] : null);
				
				$QUser->next ();
				ksort ( $rawdata );
				$data [] = $rawdata;
				$count ++;
			}
		}
		if (count ( $data ) > 0) {
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
		if (! empty ( $username )) {
			
			$rank = array ();
			if (! empty ( $rankgroup ) && $rankgroup > 0) {
				$rank = $this->getRank ( $rankgroup )->getRankIDAll ();
			}
			
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'user_access_id' 
			) );
			$DBVerify->from ( array (
					'ua' => 'user_access' 
			) );
			$where = array (
					'ua.user_access_username = "' . $username . '"',
					'ua.user_access_delete_status = 0' 
			);
			if (! empty ( $status )) {
				$where [] = 'ua.user_access_status = ' . $status;
			}
			if (count ( $rank ) > 0) {
				$where [] = 'ua.user_rank_id in (' . implode ( ', ', $rank ) . ')';
			}
			$DBVerify->where ( $where );
			$DBVerify->limit ( 1 );
			$DBVerify->execute ();
			if ($DBVerify->hasResult ()) {
				$data = $DBVerify->current ();
				$this->setOption ( 'access', $data ['id'] );
			}
		}
		return ( int ) $this->getOption ( 'access' );
	}
	
	/**
	 * Get Rank ID
	 *
	 * @return Interger
	 *
	 */
	public function getRankID($id = null) {
		if ($id > 0) {
			$this->setOption ( 'access', $id );
		}
		
		$rank_id = 0;
		$DBVerify = $this->getDatabase ();
		$DBVerify->select ();
		$DBVerify->columns ( array (
				'rank_id' => 'user_rank_id' 
		) );
		$DBVerify->from ( array (
				'ua' => 'user_access' 
		) );
		$where = array (
				'ua.user_access_id = "' . ( int ) $this->getOption ( 'access' ) . '"' 
		);
		$DBVerify->where ( $where );
		$DBVerify->limit ( 1 );
		$DBVerify->execute ();
		if ($DBVerify->hasResult ()) {
			$data = $DBVerify->current ();
			$rank_id = $data ['rank_id'];
		}
		
		return $rank_id;
	}
	
	/**
	 * Get Profile ID
	 *
	 * @return Interger
	 *
	 */
	public function getProfileID($id = null) {
		if (! empty ( $id )) {
			$QProfile = $this->getDatabase ();
			$QProfile->select ();
			$QProfile->columns ( array (
					'id' => 'user_profile_id' 
			) );
			$QProfile->from ( array (
					'ua' => 'user_access' 
			) );
			$QProfile->where ( array (
					'ua.user_access_id = "' . $id . '"' 
			) );
			$QProfile->limit ( 1 );
			$QProfile->execute ();
			if ($QProfile->hasResult ()) {
				$data = $QProfile->current ();
				$this->setOption ( 'profile', $data ['id'] );
			}
		}
		return ( int ) $this->getOption ( 'profile' );
	}
	
	/**
	 * verify ID
	 *
	 * @return Interger
	 *
	 */
	public function verifyID($id = null, $rankgroup = null, $status = 1) {
		if (! empty ( $id )) {
			
			$rank = array ();
			if (! empty ( $rankgroup ) && $rankgroup > 0) {
				$rank = $this->getRank ( $rankgroup )->getRankIDAll ();
			}
			
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'user_access_id' 
			) );
			$DBVerify->from ( array (
					'ua' => 'user_access' 
			) );
			$where = array (
					'ua.user_access_id = "' . $id . '"',
					'ua.user_access_delete_status = 0' 
			);
			if (! empty ( $status )) {
				$where [] = 'ua.user_access_status = ' . $status;
			}
			if (count ( $rank ) > 0) {
				$where [] = 'ua.user_rank_id in (' . implode ( ', ', $rank ) . ')';
			}
			$DBVerify->where ( $where );
			$DBVerify->limit ( 1 );
			$DBVerify->execute ();
			if ($DBVerify->hasResult ()) {
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
		if (! empty ( $id ) && count ( $data ) > 0) {
			$status = 0;
			$StatusParameter = new Parameter ( array (
					'key' => 'user_access_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($StatusParameter->hasResult ()) {
				$status = $StatusParameter->getValueByKey ( $data ['user_access_status'] );
			}
			
			$UStatus = $this->getDatabase ();
			$UStatus->update ();
			$UStatus->table ( 'user_access' );
			$UStatus->set ( array (
					'user_access_status' => $status,
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$UStatus->where ( array (
					'user_access_id' => $id 
			) );
			$UStatus->execute ();
			if ($UStatus->affectedRows ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Update Activation
	 *
	 * @return boolean
	 *
	 */
	public function updateActivation($id = null, $data = null) {
		if (! empty ( $id ) && count ( $data ) > 0) {
			$status = 0;
			$StatusParameter = new Parameter ( array (
					'key' => 'user_access_activation_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($StatusParameter->hasResult ()) {
				$status = $StatusParameter->getValueByKey ( $data ['user_access_activation_status'] );
			}
			if ($status == "0") {
				$data ['log_activated_date'] = "0000-00-00 00:00:00";
			} else if ($status == "1") {
				$data ['log_activated_date'] = $data ['log_modified_date'];
			}
			$UStatus = $this->getDatabase ();
			$UStatus->update ();
			$UStatus->table ( 'user_access' );
			$UStatus->set ( array (
					'user_access_status' => $status,
					'user_access_activated_date' => $data ['log_activated_date'],
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$UStatus->where ( array (
					'user_access_id' => $id 
			) );
			$UStatus->execute ();
			if ($UStatus->affectedRows ()) {
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
		if (! empty ( $id ) && count ( $data ) > 0) {
			$UUsername = $this->getDatabase ();
			$UUsername->update ();
			$UUsername->table ( 'user_access' );
			$UUsername->set ( array (
					'user_access_username' => $data ['user_username'],
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$UUsername->where ( array (
					'user_access_id' => $id 
			) );
			$UUsername->execute ();
			if ($UUsername->affectedRows ()) {
				$UHierarchy = $this->getDatabase ();
				$UHierarchy->update ();
				$UHierarchy->table ( 'user_hierarchy' );
				$UHierarchy->set ( array (
						'user_access_username' => $data ['user_username'],
						'user_hierarchy_modified_date' => $data ['log_modified_date'],
						'user_hierarchy_modified_by' => $data ['log_modified_by'] 
				) );
				$UHierarchy->where ( array (
						'user_access_id' => $id 
				) );
				$UHierarchy->execute ();
				if ($UHierarchy->affectedRows ()) {
					$UHierarchyS = $this->getDatabase ();
					$UHierarchyS->update ();
					$UHierarchyS->table ( 'user_hierarchy' );
					$UHierarchyS->set ( array (
							'user_hierarchy_sponsor_username' => $data ['user_username'] 
					) );
					$UHierarchyS->where ( array (
							'user_hierarchy_sponsor_username' => $data ['user_username_old'] 
					) );
					$UHierarchyS->execute ();
					
					$UHierarchyP = $this->getDatabase ();
					$UHierarchyP->update ();
					$UHierarchyP->table ( 'user_hierarchy' );
					$UHierarchyP->set ( array (
							'user_hierarchy_placement_username' => $data ['user_username'] 
					) );
					$UHierarchyP->where ( array (
							'user_hierarchy_placement_username' => $data ['user_username_old'] 
					) );
					$UHierarchyP->execute ();
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
		if (! empty ( $id ) && count ( $data ) > 0) {
			$password = $data ['user_access_password'];
			$Bcrypt = new Bcrypt ( array (
					'salt' => SYSTEM_BCRYPT_SALT,
					'cost' => SYSTEM_BCRYPT_COST 
			) );
			$data ['user_access_password'] = $Bcrypt->create ( $password );
			
			$UPassword = $this->getDatabase ();
			$UPassword->update ();
			$UPassword->table ( 'user_access' );
			$UPassword->set ( array (
					'user_access_password' => $data ['user_access_password'],
					'user_access_password_retrieve' => $password,
					'user_password_update_status' => 1,
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$UPassword->where ( array (
					'user_access_id' => $id 
			) );
			$UPassword->execute ();
			if ($UPassword->affectedRows ()) {
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
		if (! empty ( $id ) && count ( $data ) > 0) {
			$password = $data ['user_access_security'];
			$Bcrypt = new Bcrypt ( array (
					'salt' => SYSTEM_BCRYPT_SALT,
					'cost' => SYSTEM_BCRYPT_COST 
			) );
			$data ['user_access_security'] = $Bcrypt->create ( $password );
			
			$UPassword = $this->getDatabase ();
			$UPassword->update ();
			$UPassword->table ( 'user_access' );
			$UPassword->set ( array (
					'user_access_security' => $data ['user_access_security'],
					'user_access_security_retrieve' => $password,
					'user_security_update_status' => 1,
					'user_access_modified_date' => $data ['log_modified_date'],
					'user_access_modified_by' => $data ['log_modified_by'] 
			) );
			$UPassword->where ( array (
					'user_access_id' => $id 
			) );
			$UPassword->execute ();
			if ($UPassword->affectedRows ()) {
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
		if (! empty ( $id ) && count ( $data ) > 0) {
			$id = $this->getProfileID ( $id );
			if ($id > 0) {
				if (isset ( $data ['user_profile_fullname'] )) {
					$data ['user_profile_firstname'] = (isset ( $data ['user_profile_fullname'] ) ? $data ['user_profile_fullname'] : null);
				} else {
					$data ['user_profile_firstname'] = (isset ( $data ['user_profile_firstname'] ) ? $data ['user_profile_firstname'] : null);
				}
				$data ['user_profile_lastname'] = (isset ( $data ['user_profile_lastname'] ) ? $data ['user_profile_lastname'] : null);
				
				if (! isset ( $data ['user_profile_dob'] )) {
					$data ['user_profile_dob'] = "";
				}
				$UProfile = $this->getDatabase ();
				$UProfile->update ();
				$UProfile->table ( 'user_profile' );
				$UProfile->set ( array (
						'user_profile_designation' => $data ['user_profile_designation'],
						'user_profile_firstname' => $data ['user_profile_firstname'],
						'user_profile_lastname' => $data ['user_profile_lastname'],
						'user_profile_nric_passport' => $data ['user_profile_nric_passport'],
						'user_profile_gender' => $data ['user_profile_gender'],
						'user_profile_dob' => $data ['user_profile_dob'],
						'user_profile_nationality' => $data ['user_profile_nationality'],
						'user_profile_email_address' => $data ['user_profile_email_address'],
						'user_profile_mobile_no' => $data ['user_profile_mobile_no'],
						'user_profile_telephone_no' => $data ['user_profile_telephone_no'],
						'user_profile_office_no' => $data ['user_profile_office_no'],
						'user_profile_fax_no' => $data ['user_profile_fax_no'],
						'user_profile_modified_date' => $data ['log_modified_date'],
						'user_profile_modified_by' => $data ['log_modified_by'] 
				) );
				$UProfile->where ( array (
						'user_profile_id' => $id 
				) );
				$UProfile->execute ();
				if ($UProfile->affectedRows ()) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Generate Username
	 */
	public function generateUsername($open_tag = null, $min = null, $max = null, $end_tag = null, $group_id = null) {
		$user_code = 'TNM';
		$user_tail_code = '';
		$length_min = 1;
		$length_max = 9999;
		if (! empty ( $open_tag )) {
			$user_code = $open_tag;
		} elseif (defined ( USERNAME_CODE )) {
			$user_code = USERNAME_CODE;
		}
		if (! empty ( $end_tag )) {
			$user_tail_code = $end_tag;
		} elseif (defined ( USERNAME_TAIL_CODE )) {
			$user_tail_code = USERNAME_TAIL_CODE;
		}
		if (! empty ( $min )) {
			$length_min = $min;
		} elseif (defined ( USERNAME_LENGTH_MIN )) {
			$length_min = USERNAME_LENGTH_MIN;
		}
		if (! empty ( $max )) {
			$length_max = $max;
		} elseif (defined ( USERNAME_LENGTH_MAX )) {
			$length_max = USERNAME_LENGTH_MAX;
		}
		
		$datetime = new \DateTime ();
		
		$DBQUsername = $this->getDatabase ();
		$DBQUsername->select ();
		$DBQUsername->columns ( array (
				'id' => 'username_id' 
		) );
		$DBQUsername->from ( array (
				'u' => 'username' 
		) );
		$DBQUsername->where ( array (
				'u.user_rank_group_id = ' . $group_id,
				'u.user_access_username like "' . $user_code . $datetime->format ( 'ym' ) . '%' . $user_tail_code . '"' 
		) );
		$DBQUsername->limit ( 1 );
		$DBQUsername->execute ();
		if ($DBQUsername->count () < 1) {
			$username = array ();
			for($i = $length_min; $i <= $length_max; $i ++) {
				$current_id = $i;
				for($fix = strlen ( $i ); $fix < 4; $fix ++) {
					$current_id = '0' . $current_id;
				}
				$username [] = array (
						'user_access_username' => strtoupper ( $user_code . $datetime->format ( 'ym' ) . $current_id . $user_tail_code ),
						'user_rank_group_id' => $group_id 
				);
			}
			if (count ( $username ) > 0) {
				$DBUsername = $this->getDatabase ();
				$DBUsername->insert ();
				$DBUsername->into ( 'username' );
				$DBUsername->columns ( array (
						'user_access_username',
						'user_rank_group_id' 
				) );
				$DBUsername->values ( $username, 'multiple' );
				$DBUsername->execute ();
				if (! $DBUsername->affectedRows ()) {
					return false;
				}
			}
		}
		$DBQUsername = $this->getDatabase ();
		$DBQUsername->select ();
		$DBQUsername->columns ( array (
				'username' => 'user_access_username' 
		) );
		$DBQUsername->from ( array (
				'u' => 'username' 
		) );
		$DBQUsername->where ( array (
				'u.user_rank_group_id = ' . $group_id,
				'u.user_access_username like "' . $user_code . $datetime->format ( 'ym' ) . '%' . $user_tail_code . '"',
				'u.username_status = 1' 
		) );
		$DBQUsername->group ( array (
				'user_access_username' 
		) );
		$DBQUsername->order ( array (
				new Expression ( 'RAND()' ) 
		) );
		$DBQUsername->limit ( 1 );
		$DBQUsername->execute ();
		$username = null;
		if ($DBQUsername->count () > 0) {
			$username = $DBQUsername->current ();
			$username = $username ['username'];
		} else {
			$this->generateUsername ( $user_code, $length_max, ($length_max * 2 + $length_min), $user_tail_code, $group_id );
		}
		return $username;
	}
}

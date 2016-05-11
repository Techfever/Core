<?php

namespace Techfever\Bank;

use Techfever\Exception;

class Bank extends Country {
	
	/**
	 *
	 * @var Country Data
	 *     
	 */
	private $_bank_data = array ();
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array (
			'country' => 0,
			'state' => 0,
			'bank' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0 
	);
	
	/**
	 *
	 * @var User Bank Data
	 *     
	 */
	private $_user_bank_data = array ();
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Prepare
	 */
	public function getUserBankData() {
		if (! is_array ( $this->_user_bank_data ) || count ( $this->_user_bank_data ) < 1) {
			$QBank = $this->getDatabase ();
			$QBank->select ();
			$QBank->columns ( array (
					'*' 
			) );
			$QBank->from ( array (
					'ud' => 'user_bank' 
			) );
			$QBank->where ( array (
					'ud.user_profile_id' => $this->getOption ( 'profile_id' ),
					'ud.user_bank_delete_status' => '0' 
			) );
			$QBank->execute ();
			if ($QBank->hasResult ()) {
				$data = array ();
				while ( $QBank->valid () ) {
					$data = $QBank->current ();
					
					if ($data ['user_bank_name'] > 0) {
						$this->setOption ( 'bank', $data ['user_bank_name'] );
						$data ['user_bank_name_text'] = $this->getBankName ( $data ['user_bank_name'] );
					}
					if ($data ['user_bank_country'] > 0) {
						$data ['user_bank_country_text'] = $this->getCountryName ( $data ['user_bank_country'] );
						
						if ($data ['user_bank_state'] > 0) {
							$this->setOption ( 'country', $data ['user_bank_country'] );
							$data ['user_bank_state_text'] = $this->getStateName ( $data ['user_bank_state'] );
							
							if ($data ['user_bank_branch'] > 0) {
								$this->setOption ( 'state', $data ['user_bank_state'] );
								$this->prepareBranch ();
								$data ['user_bank_branch_text'] = $this->getBranchName ( $data ['user_bank_name'] );
							}
						}
					}
					
					$this->_user_bank_data [$data ['user_bank_id']] = $data;
					$QBank->next ();
				}
			}
		}
		return $this->_user_bank_data;
	}
	
	/**
	 * Prepare
	 */
	public function clearUserBankData() {
		$_user_bank_data = array ();
		return true;
	}
	
	/**
	 * Get Country
	 */
	public function getUserBank($id = null) {
		$data = $this->getUserBankData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Prepare
	 */
	public function getBankData() {
		if (! is_array ( $this->_bank_data ) || count ( $this->_bank_data ) < 1) {
			$DBBank = $this->getDatabase ();
			$DBBank->select ();
			$DBBank->columns ( array (
					'id' => 'bank_id',
					'name' => 'bank_name',
					'iso' => 'bank_iso' 
			) );
			$DBBank->from ( array (
					'b' => 'bank' 
			) );
			$DBBank->where ( array (
					'b.bank_status = 1' 
			) );
			$DBBank->order ( array (
					'bank_name ASC' 
			) );
			$DBBank->execute ();
			if ($DBBank->hasResult ()) {
				$data = array ();
				while ( $DBBank->valid () ) {
					$data = $DBBank->current ();
					$this->_bank_data [$data ['id']] = $data;
					$DBBank->next ();
				}
			}
		}
		return $this->_bank_data;
	}
	
	/**
	 * Get Bank
	 */
	public function getBank($id = null) {
		$data = $this->getBankData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get Bank ISO
	 */
	public function getBankISO($id = null) {
		$data = $this->getBank ( $id );
		$iso = "";
		if (strlen ( $data ['iso'] ) > 0) {
			$iso = $data ['iso'];
		}
		return $iso;
	}
	
	/**
	 * Get Bank Name
	 */
	public function getBankName($id = null) {
		$data = $this->getBank ( $id );
		$iso = $data ['iso'];
		$name = "";
		if (strlen ( $data ['iso'] ) > 0) {
			$name = $this->getTranslate ( 'text_bank_' . strtolower ( $this->convertToUnderscore ( $iso, ' ' ) ) );
		}
		return $name;
	}
	
	/**
	 * Get Bank All
	 */
	public function getBankAll() {
		return $this->getBankData ();
	}
	
	/**
	 * Bank To Form
	 */
	public function bankToForm() {
		$data = $this->getBankData ();
		$bankData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $bank ) {
				$bankData [$bank ['id']] = $this->getBankName ( $bank ['id'] );
			}
		}
		return $bankData;
	}
	
	/**
	 * Create User Bank
	 */
	public function createUserBank($profile, $data) {
		if (! empty ( $profile ) && count ( $data ) > 0) {
			$IBank = $this->getDatabase ();
			$IBank->insert ();
			$IBank->into ( 'user_bank' );
			$IBank->values ( array (
					'user_profile_id' => $profile,
					'user_bank_holder_name' => $data ['user_bank_holder_name'],
					'user_bank_holder_no' => $data ['user_bank_holder_no'],
					'user_bank_name_text' => $data ['user_bank_name_text'],
					'user_bank_name' => $data ['user_bank_name'],
					'user_bank_branch_text' => $data ['user_bank_branch_text'],
					'user_bank_branch' => $data ['user_bank_branch'],
					'user_bank_state_text' => $data ['user_bank_state_text'],
					'user_bank_state' => $data ['user_bank_state'],
					'user_bank_country_text' => $data ['user_bank_country_text'],
					'user_bank_country' => $data ['user_bank_country'],
					'user_bank_created_date' => $data ['log_created_date'],
					'user_bank_modified_date' => $data ['log_modified_date'],
					'user_bank_created_by' => $data ['log_created_by'],
					'user_bank_modified_by' => $data ['log_modified_by'] 
			) );
			$IBank->execute ();
			if ($IBank->affectedRows ()) {
				$bank = $IBank->getLastGeneratedValue ();
				$UProfile = $this->getDatabase ();
				$UProfile->update ();
				$UProfile->table ( 'user_profile' );
				$UProfile->set ( array (
						'user_bank_id' => $bank 
				) );
				$UProfile->where ( array (
						'user_profile_id = "' . $profile . '"' 
				) );
				$UProfile->execute ();
				if ($UProfile->affectedRows ()) {
					return $bank;
				}
			}
		}
		return false;
	}
	
	/**
	 * Update User Bank
	 */
	public function updateUserBank($profile, $data) {
		if ($profile > 0 && count ( $data ) > 0) {
			$bank = $this->getUserBankDefaultID ( $profile );
			if ($bank > 0) {
				$UBank = $this->getDatabase ();
				$UBank->update ();
				$UBank->table ( 'user_bank' );
				$UBank->set ( array (
						'user_bank_holder_name' => $data ['user_bank_holder_name'],
						'user_bank_holder_no' => $data ['user_bank_holder_no'],
						'user_bank_name_text' => $data ['user_bank_name_text'],
						'user_bank_name' => $data ['user_bank_name'],
						'user_bank_branch_text' => $data ['user_bank_branch_text'],
						'user_bank_branch' => $data ['user_bank_branch'],
						'user_bank_state_text' => $data ['user_bank_state_text'],
						'user_bank_state' => $data ['user_bank_state'],
						'user_bank_country_text' => $data ['user_bank_country_text'],
						'user_bank_country' => $data ['user_bank_country'],
						'user_bank_created_by' => $data ['log_created_by'],
						'user_bank_modified_by' => $data ['log_modified_by'] 
				) );
				$UBank->where ( array (
						'user_profile_id' => $profile,
						'user_bank_id' => $bank 
				) );
				$UBank->execute ();
				if ($UBank->affectedRows ()) {
					return true;
				}
			} else {
				$bank = $this->createUserBank ( $profile, $data );
				if ($bank > 0) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get User Bank ID
	 *
	 * @return Interger
	 *
	 */
	public function getUserBankDefaultID($profile = null) {
		if (! empty ( $profile )) {
			$QProfile = $this->getDatabase ();
			$QProfile->select ();
			$QProfile->columns ( array (
					'bank_id' => 'user_bank_id' 
			) );
			$QProfile->from ( array (
					'ua' => 'user_profile' 
			) );
			$QProfile->where ( array (
					'ua.user_profile_id = "' . $profile . '"' 
			) );
			$QProfile->limit ( 1 );
			$QProfile->execute ();
			if ($QProfile->hasResult ()) {
				$data = $QProfile->current ();
				return ( int ) $data ['bank_id'];
			}
		}
		return 0;
	}
	
	/**
	 * Delete User Bank
	 */
	public function deleteUserBank($profile, $forever = false) {
		if ($forever) {
			$DBank = $this->getDatabase ();
			$DBank->delete ();
			$DBank->from ( 'user_bank' );
			$DBank->where ( array (
					'user_profile_id' => $profile 
			) );
			$DBank->execute ();
		} else {
			$UBank = $this->getDatabase ();
			$UBank->update ();
			$UBank->table ( 'user_bank' );
			$UBank->set ( array (
					'user_bank_delete_status' => '1' 
			) );
			$UBank->where ( array (
					'user_profile_id' => $profile 
			) );
			$UBank->execute ();
		}
		return true;
	}
}

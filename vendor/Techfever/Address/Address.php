<?php

namespace Techfever\Address;

use Techfever\Exception;

class Address extends Country {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array (
			'country' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0 
	);
	
	/**
	 *
	 * @var User Address Data
	 *     
	 */
	private $_user_address_data = array ();
	
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
	public function getUserAddressData() {
		if (! is_array ( $this->_user_address_data ) || count ( $this->_user_address_data ) < 1) {
			if ($this->getOption ( 'profile_id' ) > 0) {
				$QAddress = $this->getDatabase ();
				$QAddress->select ();
				$QAddress->columns ( array (
						'*' 
				) );
				$QAddress->from ( array (
						'ud' => 'user_address' 
				) );
				$QAddress->where ( array (
						'ud.user_profile_id' => $this->getOption ( 'profile_id' ),
						'ud.user_address_delete_status' => '0' 
				) );
				$QAddress->setCacheName ( 'user_address_' . $this->getOption ( 'profile_id' ) );
				$QAddress->execute ();
				if ($QAddress->hasResult ()) {
					$data = array ();
					while ( $QAddress->valid () ) {
						$data = $QAddress->current ();
						
						if ($data ['user_address_country'] > 0) {
							$data ['user_address_country_text'] = $this->getCountryName ( $data ['user_address_country'] );
							
							if ($data ['user_address_state'] > 0) {
								$this->setOption ( 'country', $data ['user_address_country'] );
								$data ['user_address_state_text'] = $this->getStateName ( $data ['user_address_state'] );
							}
						}
						
						$this->_user_address_data [$data ['user_address_id']] = $data;
						$QAddress->next ();
					}
				}
			}
		}
		return $this->_user_address_data;
	}
	
	/**
	 * Prepare
	 */
	public function clearUserAddressData() {
		$_user_address_data = array ();
		return true;
	}
	
	/**
	 * Get Country
	 */
	public function getUserAddress($id = null) {
		$data = $this->getUserAddressData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Create User Address
	 */
	public function createUserAddress($profile, $data) {
		if (! empty ( $profile ) && count ( $data ) > 0) {
			$IAddress = $this->getDatabase ();
			$IAddress->insert ();
			$IAddress->into ( 'user_address' );
			$IAddress->values ( array (
					'user_profile_id' => $profile,
					'user_address_street_1' => $data ['user_address_street_1'],
					'user_address_street_2' => $data ['user_address_street_2'],
					'user_address_city' => $data ['user_address_city'],
					'user_address_postcode' => $data ['user_address_postcode'],
					'user_address_state_text' => $data ['user_address_state_text'],
					'user_address_state' => $data ['user_address_state'],
					'user_address_country_text' => $data ['user_address_country_text'],
					'user_address_country' => $data ['user_address_country'],
					'user_address_created_date' => $data ['log_created_date'],
					'user_address_modified_date' => $data ['log_modified_date'],
					'user_address_created_by' => $data ['log_created_by'],
					'user_address_modified_by' => $data ['log_modified_by'] 
			) );
			$IAddress->execute ();
			if ($IAddress->affectedRows ()) {
				$address = $IAddress->getLastGeneratedValue ();
				$UProfile = $this->getDatabase ();
				$UProfile->update ();
				$UProfile->table ( 'user_profile' );
				$UProfile->set ( array (
						'user_address_id' => $address 
				) );
				$UProfile->where ( array (
						'user_profile_id = "' . $profile . '"' 
				) );
				$UProfile->execute ();
				if ($UProfile->affectedRows ()) {
					return $address;
				}
			}
		}
		return false;
	}
	
	/**
	 * Update User Address
	 */
	public function updateUserAddress($profile, $data) {
		if ($profile > 0 && count ( $data ) > 0) {
			$address = $this->getUserAddressDefaultID ( $profile );
			if ($address > 0) {
				$UAddress = $this->getDatabase ();
				$UAddress->update ();
				$UAddress->table ( 'user_address' );
				$UAddress->set ( array (
						'user_address_street_1' => $data ['user_address_street_1'],
						'user_address_street_2' => $data ['user_address_street_2'],
						'user_address_city' => $data ['user_address_city'],
						'user_address_postcode' => $data ['user_address_postcode'],
						'user_address_state_text' => $data ['user_address_state_text'],
						'user_address_state' => $data ['user_address_state'],
						'user_address_country_text' => $data ['user_address_country_text'],
						'user_address_country' => $data ['user_address_country'],
						'user_address_created_by' => $data ['log_created_by'],
						'user_address_modified_by' => $data ['log_modified_by'] 
				) );
				$UAddress->where ( array (
						'user_profile_id' => $profile,
						'user_address_id' => $address 
				) );
				$UAddress->setCacheName ( 'user_address_' . $address . '_' . $profile );
				$UAddress->execute ();
				if ($UAddress->affectedRows ()) {
					return true;
				}
			} else {
				$address = $this->createUserAddress ( $profile, $data );
				if ($address > 0) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get User Address ID
	 *
	 * @return Interger
	 *
	 */
	public function getUserAddressDefaultID($profile = null) {
		if (! empty ( $profile )) {
			$QProfile = $this->getDatabase ();
			$QProfile->select ();
			$QProfile->columns ( array (
					'address_id' => 'user_address_id' 
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
				return ( int ) $data ['address_id'];
			}
		}
		return 0;
	}
	
	/**
	 * Delete User Address
	 */
	public function deleteUserAddress($profile, $forever = false) {
		if ($forever) {
			$DAddress = $this->getDatabase ();
			$DAddress->delete ();
			$DAddress->from ( 'user_address' );
			$DAddress->where ( array (
					'user_profile_id' => $profile 
			) );
			$DAddress->execute ();
		} else {
			$UAddress = $this->getDatabase ();
			$UAddress->update ();
			$UAddress->table ( 'user_address' );
			$UAddress->set ( array (
					'user_address_delete_status' => '1' 
			) );
			$UAddress->where ( array (
					'user_profile_id' => $profile 
			) );
			$UAddress->execute ();
		}
		return true;
	}
}

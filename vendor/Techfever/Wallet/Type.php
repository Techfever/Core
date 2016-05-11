<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Type extends Transaction {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'action' => '',
			'from_user' => 0,
			'to_user' => 0,
			'from_wallet_type' => '',
			'to_wallet_type' => '',
			'from_user_rank' => '',
			'to_user_rank' => '',
			'transaction_status' => '',
			'transaction' => '' 
	);
	
	/**
	 *
	 * @var Wallet type Data
	 *     
	 */
	private $wallet_type_data = null;
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
	 * Get Type Data
	 *
	 * @return array data
	 *        
	 */
	public function getTypeData() {
		if (! is_array ( $this->wallet_type_data ) || count ( $this->wallet_type_data ) < 1) {
			$rawdata = array ();
			$QType = $this->getDatabase ();
			$QType->select ();
			$QType->columns ( array (
					'id' => 'wallet_type_id',
					'key' => 'wallet_type_key',
					'register' => 'wallet_type_register_status',
					'transfer' => 'wallet_type_transfer_status',
					'exchange' => 'wallet_type_exchange_status',
					'withdraw' => 'wallet_type_withdraw_status',
					'sponsor' => 'wallet_type_sponsor_status',
					'sponsor_percentage' => 'wallet_type_sponsor_percentage',
					'sponsor_rebate' => 'wallet_type_sponsor_rebate_status',
					'sponsor_rebate_percentage' => 'wallet_type_sponsor_rebate_percentage',
					'pairing' => 'wallet_type_pairing_status',
					'pairing_percentage' => 'wallet_type_pairing_percentage',
					'matching' => 'wallet_type_matching_status',
					'matching_percentage' => 'wallet_type_matching_percentage',
					'level' => 'wallet_type_level_status',
					'level_percentage' => 'wallet_type_level_percentage',
					'roi' => 'wallet_type_roi_status',
					'roi_percentage' => 'wallet_type_roi_percentage',
					'created_date' => 'wallet_type_created_date',
					'modified_date' => 'wallet_type_modified_date',
					'created_by' => 'wallet_type_created_by',
					'modified_by' => 'wallet_type_modified_by' 
			) );
			$QType->from ( array (
					'wt' => 'wallet_type' 
			) );
			$QType->where ( array (
					'wt.wallet_type_status = 1' 
			) );
			$QType->execute ();
			if ($QType->hasResult ()) {
				while ( $QType->valid () ) {
					$rawdata = $QType->current ();
					$this->wallet_type_data [$rawdata ['id']] = $rawdata;
					$QType->next ();
				}
			}
		}
		return $this->wallet_type_data;
	}
	
	/**
	 * Create User Type
	 */
	public function createUserType($id = null) {
		$data = $this->getTypeData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $type ) {
				$IUserType = $this->getDatabase ();
				$IUserType->insert ();
				$IUserType->into ( 'user_wallet' );
				$IUserType->values ( array (
						'wallet_type_id' => $type ['id'],
						'user_access_id' => (! empty ( $id ) ? $id : $this->getOption ( 'from_user' )) 
				) );
				$IUserType->execute ();
				if (! $IUserType->affectedRows ()) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Delete User Type
	 */
	public function deleteUserType($id = null, $forever = false) {
		if ($forever) {
			$DUserType = $this->getDatabase ();
			$DUserType->delete ();
			$DUserType->into ( 'user_wallet' );
			$DUserType->where ( array (
					'user_access_id' => (! empty ( $id ) ? $id : $this->getOption ( 'from_user' )) 
			) );
			$DUserType->execute ();
		} else {
			$UUserType = $this->getDatabase ();
			$UUserType->update ();
			$UUserType->table ( 'user_wallet' );
			$UUserType->set ( array (
					'user_wallet_status' => '0' 
			) );
			$UUserType->where ( array (
					'user_access_id' => (! empty ( $id ) ? $id : $this->getOption ( 'from_user' )) 
			) );
			$UUserType->execute ();
		}
		return true;
	}
	
	/**
	 * Get Type
	 */
	public function getTypeAllID() {
		$data = $this->getTypeData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			$typedata = array ();
			foreach ( $data as $type ) {
				$typedata [$type ['id']] = $type ['id'];
			}
			return $typedata;
		}
		return false;
	}
	
	/**
	 * Get Type All
	 */
	public function getType($id = null) {
		$data = $this->getTypeData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get Type Bonus
	 */
	public function getTypeBonus($action = null) {
		$data = $this->getTypeData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			$typedata = array ();
			foreach ( $data as $type ) {
				if (array_key_exists ( $action, $type )) {
					$status = $type [$action];
					if ($status == 1 && array_key_exists ( $action . '_percentage', $type )) {
						$typedata [$type ['id']] = array (
								'id' => $type ['id'],
								'action' => $action,
								'percentage' => $type [$action . '_percentage'] 
						);
					}
				}
			}
			return $typedata;
		}
		return false;
	}
	
	/**
	 * Get Type Message
	 */
	public function getTypeMessage($id = null) {
		$key = $this->getTypeKey ( $id );
		$name = "";
		if (strlen ( $key ) > 0) {
			$name = $this->getTranslate ( 'text_user_wallet_' . $key );
		}
		return $name;
	}
	
	/**
	 * Get Action Type
	 */
	public function getActionType($action = null, $index = null) {
		$data = $this->getTypeData ();
		$typedata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $type ) {
				if (array_key_exists ( $action, $type ) && $type [$action] == "1") {
					$typedata [] = $type ['id'];
				}
			}
		}
		ksort ( $typedata );
		if (is_int ( $index ) && $index >= 0) {
			return $typedata [$index];
		}
		return $typedata;
	}
	
	/**
	 * Get Type Key
	 */
	public function getTypeKey($id = null) {
		$data = $this->getType ( $id );
		$key = null;
		if (is_array ( $data ) && array_key_exists ( 'key', $data )) {
			$key = $data ['key'];
		}
		return $key;
	}
	
	/**
	 * Type To Form
	 */
	public function TypeToForm() {
		$data = $this->getTypeData ();
		$typedata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $type ) {
				$typedata [$type ['id']] = $this->getTypeMessage ( $type ['id'] );
			}
		}
		ksort ( $typedata );
		return $typedata;
	}
	
	/**
	 * Get Action Type to Form
	 */
	public function getActionTypeToForm($action = null, $index = null) {
		$data = $this->getTypeData ();
		$typedata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $type ) {
				if (array_key_exists ( $action, $type ) && $type [$action] == "1") {
					$typedata [$type ['id']] = $this->getTypeMessage ( $type ['id'] );
				}
			}
		}
		ksort ( $typedata );
		return $typedata;
	}
}

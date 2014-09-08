<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Wallet extends Configuration {
	
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
	 * @var Wallet Data
	 *     
	 */
	private $user_wallet_data = null;
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
	 * Get User Wallet Data
	 *
	 * @return array data
	 *        
	 */
	public function getWalletData() {
		if (! is_array ( $this->user_wallet_data ) || count ( $this->user_wallet_data ) < 1) {
			if ($this->getOption ( 'from_user' ) > 0) {
				$QWallet = $this->getDatabase ();
				$QWallet->select ();
				$QWallet->columns ( array (
						'type' => 'wallet_type_id',
						'amount_unlocked' => 'user_wallet_amount',
						'amount_locked' => 'user_wallet_amount_locked' 
				) );
				$QWallet->from ( array (
						'uw' => 'user_wallet' 
				) );
				$QWallet->where ( array (
						'uw.user_access_id' => $this->getOption ( 'from_user' ),
						'uw.user_wallet_status' => '1' 
				) );
				$QWallet->setCacheName ( 'user_wallet_' . $this->getOption ( 'from_user' ) );
				$QWallet->execute ();
				if ($QWallet->hasResult ()) {
					while ( $QWallet->valid () ) {
						$rawdata = $QWallet->current ();
						$amount = ($rawdata ['amount_unlocked'] - $rawdata ['amount_locked']);
						$rawdata ['amount_total'] = $amount;
						$rawdata ['title'] = $this->getTypeMessage ( $rawdata ['type'] );
						$rawdata ['key'] = $this->getTypeKey ( $rawdata ['type'] );
						$rawdata ['amount'] = $this->formatNumber ( $amount );
						$rawdata ['currency'] = $this->formatCurrency ( $amount, 'USD' );
						$this->user_wallet_data [$rawdata ['type']] = $rawdata;
						$QWallet->next ();
					}
				}
			}
		}
		return $this->user_wallet_data;
	}
	
	/**
	 * Get User Wallet
	 */
	public function getWallet($id = null) {
		$data = $this->getWalletData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get User Total
	 */
	public function getPocket() {
		$data = $this->getWalletData ();
		$rawdata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $data_key => $data_value ) {
				$rawdata [$data_key] = $data_value ['amount'];
			}
		}
		return $rawdata;
	}
	
	/**
	 * Get User Total by ID
	 */
	public function getPocketByID($id = null) {
		$data = $this->getPocket ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get User History List
	 */
	public function getHistoryListing($search = null, $order = null, $index = 0, $perpage = 10, $cache = 'user_wallet_history', $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		
		$QHistory = $this->getDatabase ();
		$QHistory->select ();
		$QHistory->columns ( array (
				'*' 
		) );
		$QHistory->from ( array (
				'uwh' => 'user_wallet_history' 
		) );
		$QHistory->join ( array (
				'ws' => 'wallet_status' 
		), 'ws.wallet_status_id  = uwh.wallet_status_id', array (
				'wallet_status_key' 
		) );
		$QHistory->join ( array (
				'wt' => 'wallet_transaction' 
		), 'wt.wallet_transaction_id  = uwh.wallet_transaction_id', array (
				'wallet_transaction_key' 
		) );
		$QHistory->join ( array (
				'uaf' => 'user_access' 
		), 'uaf.user_access_id  = uwh.user_access_id_from', array (
				'user_username_to' => 'user_access_username' 
		) );
		$QHistory->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id  = uwh.user_access_id_to', array (
				'user_username_from' => 'user_access_username' 
		) );
		$QHistory->join ( array (
				'wtf' => 'wallet_type' 
		), 'wtf.wallet_type_id  = uwh.wallet_type_id_from', array (
				'wallet_type_key_from' => 'wallet_type_key' 
		) );
		$QHistory->join ( array (
				'wtt' => 'wallet_type' 
		), 'wtt.wallet_type_id  = uwh.wallet_type_id_to', array (
				'wallet_type_key_to' => 'wallet_type_key' 
		) );
		$where = array (
				'uwh.user_wallet_history_visible_status = 1',
				'uwh.user_wallet_history_deleted_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_wallet_history', $search )) {
			$where = array_merge ( $where, $search ['user_wallet_history'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_to', $search )) {
			$where = array_merge ( $where, $search ['user_access_to'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_from', $search )) {
			$where = array_merge ( $where, $search ['user_access_from'] );
		}
		$QHistory->where ( $where );
		if (empty ( $order )) {
			$QHistory->order ( array (
					'uwh.user_wallet_history_id' 
			) );
		} else {
			$QHistory->order ( $order );
		}
		if (isset ( $perpage )) {
			$QHistory->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QHistory->offset ( ( int ) $index );
		}
		$QHistory->setCacheName ( 'user_wallet_history_' . $cache );
		$this->getLog ()->info ( $QHistory->getSQLString () );
		$QHistory->execute ();
		if ($QHistory->hasResult ()) {
			$data = array ();
			$count = 1;
			while ( $QHistory->valid () ) {
				$rawdata = $QHistory->current ();
				$rawdata ['no'] = $count;
				
				$cryptID = $this->Encrypt ( $rawdata ['user_wallet_history_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['user_wallet_history_id']);
				
				$rawdata ['user_wallet_type_from_text'] = $rawdata ['wallet_type_id_from'];
				$rawdata ['user_wallet_type_from_text'] = $this->getTypeMessage ( $rawdata ['wallet_type_id_from'] );
				
				$rawdata ['user_wallet_type_to_text'] = $rawdata ['wallet_type_id_to'];
				$rawdata ['user_wallet_type_to_text'] = $this->getTypeMessage ( $rawdata ['wallet_type_id_to'] );
				
				$rawdata ['user_wallet_transaction_text'] = $rawdata ['wallet_transaction_id'];
				$rawdata ['user_wallet_transaction_text'] = $this->getTransactionMessage ( $rawdata ['wallet_transaction_id'] );
				
				$rawdata ['user_wallet_status_text'] = $rawdata ['wallet_status_id'];
				$rawdata ['user_wallet_status_text'] = $this->getStatusMessage ( $rawdata ['wallet_status_id'] );
				
				$rawdata ['user_wallet_history_amount_in'] = $this->formatNumber ( $rawdata ['user_wallet_history_amount_in'] );
				$rawdata ['user_wallet_history_amount_out'] = $this->formatNumber ( $rawdata ['user_wallet_history_amount_out'] );
				
				$rawdata ['user_wallet_history_modified_date_format'] = "";
				if ($rawdata ['user_wallet_history_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_modified_date'] );
					$rawdata ['user_wallet_history_modified_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				}
				
				$rawdata ['user_wallet_history_created_date_format'] = "";
				if ($rawdata ['user_wallet_history_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_created_date'] );
					$rawdata ['user_wallet_history_created_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				}
				
				$rawdata ['user_wallet_history_approved_date_format'] = "";
				if ($rawdata ['user_wallet_history_approved_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_approved_date'] );
					$rawdata ['user_wallet_history_approved_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
				}
				
				$QHistory->next ();
				ksort ( $rawdata );
				$data [$rawdata ['user_wallet_history_id']] = $rawdata;
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
	 * verify ID
	 *
	 * @return Interger
	 *
	 */
	public function verifyHistoryID($id = null) {
		if (! empty ( $id )) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'user_wallet_history_id' 
			) );
			$DBVerify->from ( array (
					'uwh' => 'user_wallet_history' 
			) );
			$where = array (
					'uwh.user_wallet_history_id = ' . $id 
			);
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
	 * Get User History List
	 */
	public function getHistoryData($id = null) {
		$data = array ();
		
		$QHistory = $this->getDatabase ();
		$QHistory->select ();
		$QHistory->columns ( array (
				'*' 
		) );
		$QHistory->from ( array (
				'uwh' => 'user_wallet_history' 
		) );
		$QHistory->join ( array (
				'ws' => 'wallet_status' 
		), 'ws.wallet_status_id  = uwh.wallet_status_id', array (
				'wallet_status_key' 
		) );
		$QHistory->join ( array (
				'wt' => 'wallet_transaction' 
		), 'wt.wallet_transaction_id  = uwh.wallet_transaction_id', array (
				'wallet_transaction_key' 
		) );
		$QHistory->join ( array (
				'uaf' => 'user_access' 
		), 'uaf.user_access_id  = uwh.user_access_id_from', array (
				'user_username_to' => 'user_access_username' 
		) );
		$QHistory->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id  = uwh.user_access_id_to', array (
				'user_username_from' => 'user_access_username' 
		) );
		$QHistory->join ( array (
				'wtf' => 'wallet_type' 
		), 'wtf.wallet_type_id  = uwh.wallet_type_id_from', array (
				'wallet_type_key_from' => 'wallet_type_key' 
		) );
		$QHistory->join ( array (
				'wtt' => 'wallet_type' 
		), 'wtt.wallet_type_id  = uwh.wallet_type_id_to', array (
				'wallet_type_key_to' => 'wallet_type_key' 
		) );
		$where = array (
				'uwh.user_wallet_history_visible_status = 1',
				'uwh.user_wallet_history_id = ' . $id 
		);
		$QHistory->where ( $where );
		$QHistory->setCacheName ( 'user_wallet_history_' . $id );
		$QHistory->execute ();
		if ($QHistory->hasResult ()) {
			$data = $QHistory->current ();
			
			$cryptID = $this->Encrypt ( $data ['user_wallet_history_id'] );
			$data ['id'] = $cryptID;
			
			$data ['wallet_history_modify'] = $cryptID;
			
			$data ['user_wallet_type_from_text'] = $data ['wallet_type_id_from'];
			$data ['user_wallet_type_from_text'] = $this->getTypeMessage ( $data ['wallet_type_id_from'] );
			
			$data ['user_wallet_type_to_text'] = $data ['wallet_type_id_to'];
			$data ['user_wallet_type_to_text'] = $this->getTypeMessage ( $data ['wallet_type_id_to'] );
			
			$data ['user_wallet_transaction_text'] = $data ['wallet_transaction_id'];
			$data ['user_wallet_transaction_text'] = $this->getTransactionMessage ( $data ['wallet_transaction_id'] );
			
			$data ['user_wallet_status_text'] = $data ['wallet_status_id'];
			$data ['user_wallet_status_text'] = $this->getStatusMessage ( $data ['wallet_status_id'] );
			
			$data ['user_wallet_history_amount_in_format'] = $this->formatNumber ( $data ['user_wallet_history_amount_in'] );
			$data ['user_wallet_history_amount_out_format'] = $this->formatNumber ( $data ['user_wallet_history_amount_out'] );
			
			$data ['user_wallet_history_modified_date_format'] = "";
			if ($data ['user_wallet_history_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_wallet_history_modified_date'] );
				$data ['user_wallet_history_modified_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
			}
			
			$data ['user_wallet_history_created_date_format'] = "";
			if ($data ['user_wallet_history_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_wallet_history_created_date'] );
				$data ['user_wallet_history_created_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
			}
			
			$data ['user_wallet_history_approved_date_format'] = "";
			if ($data ['user_wallet_history_approved_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_wallet_history_approved_date'] );
				$data ['user_wallet_history_approved_date_format'] = $datetime->format ( 'H:i:s d-m-Y' );
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
	 * Type From To Form
	 */
	public function TypeFromToForm() {
		$data = $this->getConfigurationData ();
		$configurationData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $configuration ) {
				$configurationData [$configuration ['wallet_type_from']] = $this->getTypeMessage ( $configuration ['wallet_type_from'] );
			}
		}
		ksort ( $configurationData );
		return $configurationData;
	}
	
	/**
	 * Type To To Form
	 */
	public function TypeToToForm() {
		$data = $this->getConfigurationData ();
		$configurationData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $configuration ) {
				$configurationData [$configuration ['wallet_type_to']] = $this->getTypeMessage ( $configuration ['wallet_type_to'] );
			}
		}
		ksort ( $configurationData );
		return $configurationData;
	}
	
	/**
	 * Validation User Rank Allow
	 *
	 * @return boolean
	 *
	 */
	public function validRankAllow($from_rank = null, $to_rank = null) {
		if (empty ( $from_rank )) {
			$from_rank = $this->getOption ( 'from_user_rank' );
		}
		if (empty ( $to_rank )) {
			$to_rank = $this->getOption ( 'to_user_rank' );
		}
		$data = $this->getConfigurationData ();
		$configurationData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $configuration ) {
				if ($configuration ['user_rank_from'] == $from_rank && $configuration ['user_rank_to'] == $to_rank) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Validation User Type Allow
	 *
	 * @return boolean
	 *
	 */
	public function validTypeAllow($from_rank = null, $to_rank = null, $from_type = null, $to_type = null) {
		if (empty ( $from_rank )) {
			$from_rank = $this->getOption ( 'from_user_rank' );
		}
		if (empty ( $to_rank )) {
			$to_rank = $this->getOption ( 'to_user_rank' );
		}
		if (empty ( $from_type )) {
			$from_type = $this->getOption ( 'from_wallet_type' );
		}
		if (empty ( $to_type )) {
			$to_type = $this->getOption ( 'to_wallet_type' );
		}
		$data = $this->getConfigurationData ();
		$configurationData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $configuration ) {
				if ($configuration ['user_rank_from'] == $from_rank && $configuration ['user_rank_to'] == $to_rank) {
					if ($configuration ['wallet_type_from'] == $from_type && $configuration ['wallet_type_to'] == $to_type) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Validation User Wallet Amount
	 *
	 * @return boolean
	 *
	 */
	public function validUserPocketAmount($amount) {
		$QWallet = $this->getDatabase ();
		$QWallet->select ();
		$QWallet->columns ( array (
				'type' => 'wallet_type_id',
				'amount_unlocked' => 'user_wallet_amount',
				'amount_locked' => 'user_wallet_amount_locked' 
		) );
		$QWallet->from ( array (
				'uw' => 'user_wallet' 
		) );
		$where = array (
				'(uw.user_wallet_amount - uw.user_wallet_amount_locked) >= ' . $amount,
				'uw.user_access_id = ' . $this->getOption ( 'from_user' ),
				'uw.user_wallet_status = 1',
				'uw.wallet_type_id = ' . $this->getOption ( 'from_wallet_type' ) 
		);
		$QWallet->where ( $where );
		$QWallet->setCacheName ( 'user_wallet_' . $this->getOption ( 'from_user' ) . '_' . $this->getOption ( 'from_wallet_type' ) );
		$QWallet->execute ();
		if ($QWallet->hasResult ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Create User Wallet History
	 *
	 * @return boolean
	 *
	 */
	public function createUserHistory($data) {
		$status = false;
		$history_from = 0;
		$history_to = 0;
		$bank = 0;
		if ($this->validUserPocketAmount ( $data ['user_wallet_amount'] )) {
			$history_from = $this->insertUserHistory ( $data, '-' );
			$history_to = 0;
			if ($history_from !== false && $history_from > 0) {
				if ($this->getOption ( 'transaction_status' ) !== 1) {
					$pocket_from = $this->updateUserPocket ( $this->getOption ( 'from_user' ), $this->getOption ( 'from_wallet_type' ), $data ['user_wallet_amount'], '-' );
					if ($pocket_from) {
						$history_to = $this->insertUserHistory ( $data, '+' );
						if ($history_to !== false && $history_to > 0) {
							$pocket_to = $this->updateUserPocket ( $this->getOption ( 'to_user' ), $this->getOption ( 'to_wallet_type' ), $data ['user_wallet_amount'], '+' );
							if ($pocket_to) {
								$status = true;
							}
						}
					}
				} else if ($this->getOption ( 'transaction_status' ) === 1) {
					if ($this->getOption ( 'transaction' ) === 5000) {
						$bank = $this->insertUserHistoryBank ( $history_from, $data );
						if ($bank) {
							$status = true;
						}
					}
				}
			}
		}
		if ($status) {
			return true;
		} else {
			$this->deleteUserHistory ( $history_from );
			$this->deleteUserHistory ( $history_to );
			$this->deleteUserHistoryBank ( $bank );
			return false;
		}
	}
	
	/**
	 * Create User Wallet History From
	 *
	 * @return boolean
	 *
	 */
	public function insertUserHistory($data, $action) {
		$status = true;
		$id = 0;
		$IHistory = $this->getDatabase ();
		$IHistory->insert ();
		$IHistory->into ( 'user_wallet_history' );
		$IHistory->values ( array (
				'wallet_status_id' => $this->getOption ( 'transaction_status' ),
				'wallet_transaction_id' => $this->getOption ( 'transaction' ),
				'user_access_id' => ($action === '-' ? $this->getOption ( 'from_user' ) : ($action === '+' ? $this->getOption ( 'to_user' ) : 0)),
				'user_access_id_from' => $this->getOption ( 'from_user' ),
				'user_access_id_to' => $this->getOption ( 'to_user' ),
				'wallet_type_id_from' => $this->getOption ( 'from_wallet_type' ),
				'wallet_type_id_to' => $this->getOption ( 'to_wallet_type' ),
				'user_wallet_history_amount_in' => ($action === '+' ? $data ['user_wallet_amount'] : 0),
				'user_wallet_history_amount_out' => ($action === '-' ? $data ['user_wallet_amount'] : 0),
				'user_wallet_history_ref_no' => $data ['timestamp'],
				'user_wallet_history_comment' => $data ['user_wallet_comment'],
				'user_wallet_history_visible_status' => 1,
				'user_wallet_history_deleted_status' => 0,
				'user_wallet_history_created_date' => $data ['log_created_date'],
				'user_wallet_history_modified_date' => $data ['log_modified_date'],
				'user_wallet_history_approved_date' => ($this->getOption ( 'transaction_status' ) == 3 ? $data ['log_created_date'] : '0000-00-00 00:00:00'),
				'user_wallet_history_created_by' => $data ['log_created_by'],
				'user_wallet_history_modified_by' => $data ['log_modified_by'],
				'user_wallet_history_approved_by' => ($this->getOption ( 'transaction_status' ) == 3 ? $data ['log_created_by'] : null) 
		) );
		$IHistory->execute ();
		if ($IHistory->affectedRows ()) {
			$id = $IHistory->getLastGeneratedValue ();
		} else {
			$status = false;
		}
		if ($status) {
			return $id;
		} else {
			$this->deleteUserHistory ( $id );
			return false;
		}
	}
	
	/**
	 * Delete User Wallet History
	 *
	 * @return boolean
	 *
	 */
	public function deleteUserHistory($id, $forever = false) {
		if ($forever) {
			$DHistory = $this->getDatabase ();
			$DHistory->delete ();
			$DHistory->from ( 'user_wallet_history' );
			$DHistory->where ( array (
					'user_wallet_history_id' => $id 
			) );
			$DHistory->execute ();
		} else {
			$UHistory = $this->getDatabase ();
			$UHistory->update ();
			$UHistory->table ( 'user_wallet_history' );
			$UHistory->set ( array (
					'user_wallet_history_deleted_status' => '1' 
			) );
			$UHistory->where ( array (
					'user_wallet_history_id' => $id 
			) );
			$UHistory->execute ();
		}
		return true;
	}
	
	/**
	 * update History Status
	 *
	 * @return boolean
	 *
	 */
	public function updateHistoryStatus($id = null, $status = null, $data = null) {
		if ((! empty ( $status ) && $status > 0) && (is_array ( $data ) && count ( $data ) > 0)) {
			if (empty ( $id ) && $id < 1) {
				$id = $data ['user_wallet_history_id'];
			}
			$UHistory = $this->getDatabase ();
			$UHistory->update ();
			$UHistory->table ( 'user_wallet_history' );
			$UHistory->set ( array (
					'wallet_status_id' => $status,
					'user_wallet_history_modified_date' => $data ['log_modified_date'],
					'user_wallet_history_approved_date' => $data ['log_modified_date'],
					'user_wallet_history_modified_by' => $data ['log_modified_by'],
					'user_wallet_history_approved_by' => $data ['log_modified_by'] 
			) );
			$UHistory->where ( array (
					'user_wallet_history_id' => $id 
			) );
			$UHistory->execute ();
			if ($UHistory->affectedRows ()) {
				if ($status == 2) {
					$this->updateUserPocket ( $data ['user_access_id_from'], $data ['wallet_type_id_from'], $data ['user_wallet_history_amount_out'], "+" );
				}
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Create User Wallet HistoryBank From
	 *
	 * @return boolean
	 *
	 */
	public function insertUserHistoryBank($history_id, $data) {
		$status = true;
		$id = 0;
		$IHistoryBank = $this->getDatabase ();
		$IHistoryBank->insert ();
		$IHistoryBank->into ( 'user_wallet_history_bank' );
		$IHistoryBank->values ( array (
				'user_wallet_history_id' => $history_id,
				'user_wallet_history_bank_holder_name' => $data ['user_bank_holder_name'],
				'user_wallet_history_bank_holder_no' => $data ['user_bank_holder_no'],
				'user_wallet_history_bank_name_text' => $data ['user_bank_name_text'],
				'user_wallet_history_bank_name' => $data ['user_bank_name'],
				'user_wallet_history_bank_branch_text' => $data ['user_bank_branch_text'],
				'user_wallet_history_bank_branch' => $data ['user_bank_branch'],
				'user_wallet_history_bank_state_text' => $data ['user_bank_state_text'],
				'user_wallet_history_bank_state' => $data ['user_bank_state'],
				'user_wallet_history_bank_country_text' => $data ['user_bank_country_text'],
				'user_wallet_history_bank_country' => $data ['user_bank_country'] 
		) );
		$IHistoryBank->execute ();
		if ($IHistoryBank->affectedRows ()) {
			$id = $IHistoryBank->getLastGeneratedValue ();
		} else {
			$status = false;
		}
		if ($status) {
			return $id;
		} else {
			$this->deleteUserHistoryBank ( $id );
			return false;
		}
	}
	
	/**
	 * Delete User Wallet HistoryBank
	 *
	 * @return boolean
	 *
	 */
	public function deleteUserHistoryBank($id) {
		$DHistoryBank = $this->getDatabase ();
		$DHistoryBank->delete ();
		$DHistoryBank->from ( 'user_wallet_history_bank' );
		$DHistoryBank->where ( array (
				'user_wallet_history_bank_id' => $id 
		) );
		$DHistoryBank->execute ();
		if ($DHistoryBank->affectedRows ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Update User Wallet Amount
	 *
	 * @return boolean
	 *
	 */
	public function updateUserPocket($id, $type, $amount, $action) {
		$UPocket = $this->getDatabase ();
		$UPocket->update ();
		$UPocket->table ( 'user_wallet' );
		if ($action === '+') {
			$UPocket->set ( array (
					'user_wallet_amount' => new \Zend\Db\Sql\Expression ( '(user_wallet_amount + ' . $amount . ')' ) 
			) );
		} elseif ($action === '-') {
			$UPocket->set ( array (
					'user_wallet_amount' => new \Zend\Db\Sql\Expression ( '(user_wallet_amount - ' . $amount . ')' ) 
			) );
		}
		$UPocket->where ( array (
				'wallet_type_id' => $type,
				'user_access_id' => $id 
		) );
		$UPocket->execute ();
		if ($UPocket->affectedRows ()) {
			return true;
		}
		return false;
	}
}

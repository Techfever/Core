<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Withdraw extends Type {
	
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
	 * @var Wallet withdraw Data
	 *     
	 */
	private $wallet_withdraw_data = null;
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
	 * Get Withdraw Data
	 *
	 * @return array data
	 *        
	 */
	public function getWithdrawData() {
		if (! is_array ( $this->wallet_withdraw_data ) || count ( $this->wallet_withdraw_data ) < 1) {
			$rawdata = array ();
			$QWithdraw = $this->getDatabase ();
			$QWithdraw->select ();
			$QWithdraw->columns ( array (
					'id' => 'wallet_withdraw_id',
					'user_rank' => 'user_rank_id',
					'wallet_type' => 'wallet_type_id',
					'date' => 'wallet_withdraw_date',
					'flag' => 'wallet_withdraw_flag',
					'created_date' => 'wallet_withdraw_created_date',
					'modified_date' => 'wallet_withdraw_modified_date',
					'created_by' => 'wallet_withdraw_created_by',
					'modified_by' => 'wallet_withdraw_modified_by' 
			) );
			$QWithdraw->from ( array (
					'ww' => 'wallet_withdraw' 
			) );
			$QWithdraw->where ( array (
					'ww.user_rank' => $this->getOption ( 'from_user_rank' ),
					'ww.wallet_withdraw_status' => '1' 
			) );
			$QWithdraw->execute ();
			if ($QWithdraw->hasResult ()) {
				while ( $QWithdraw->valid () ) {
					$rawdata = $QWithdraw->current ();
					$this->wallet_withdraw_data [$rawdata ['id']] = $rawdata;
					$QWithdraw->next ();
				}
			}
		}
		return $this->wallet_withdraw_data;
	}
	
	/**
	 * Get User Withdraw History List
	 */
	public function getWithdrawHistoryListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		
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
		$QHistory->join ( array (
				'uwhb' => 'user_wallet_history_bank' 
		), 'uwhb.user_wallet_history_id  = uwh.user_wallet_history_id', array (
				'*' 
		) );
		$where = array (
				'uwh.wallet_transaction_id = 5000',
				'uwh.user_wallet_history_visible_status = 1',
				'uwh.user_wallet_history_deleted_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_wallet_history', $search )) {
			$where = array_merge ( $where, $search ['user_wallet_history'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_wallet_history_bank', $search )) {
			$where = array_merge ( $where, $search ['user_wallet_history_bank'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_to', $search )) {
			$where = array_merge ( $where, $search ['user_access_to'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_from', $search )) {
			$where = array_merge ( $where, $search ['user_access_from'] );
		}
		$QHistory->where ( $where );
		$QHistory->execute ();
		$count = 0;
		if ($QHistory->hasResult ()) {
			$count = $QHistory->count ();
		}
		return $count;
	}
	
	/**
	 * Get User Withdraw History List
	 */
	public function getWithdrawHistoryListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
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
		$QHistory->join ( array (
				'uwhb' => 'user_wallet_history_bank' 
		), 'uwhb.user_wallet_history_id  = uwh.user_wallet_history_id', array (
				'*' 
		) );
		$where = array (
				'uwh.wallet_transaction_id = 5000',
				'uwh.user_wallet_history_visible_status = 1',
				'uwh.user_wallet_history_deleted_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_wallet_history', $search )) {
			$where = array_merge ( $where, $search ['user_wallet_history'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_wallet_history_bank', $search )) {
			$where = array_merge ( $where, $search ['user_wallet_history_bank'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_to', $search )) {
			$where = array_merge ( $where, $search ['user_access_to'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access_from', $search )) {
			$where = array_merge ( $where, $search ['user_access_from'] );
		}
		$QHistory->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'uwh.user_wallet_history_id' 
			);
		}
		$QHistory->order ( $order );
		if (isset ( $perpage )) {
			$QHistory->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QHistory->offset ( ( int ) $index );
		}
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
				
				$rawdata ['wallet_type_id_from_text'] = $rawdata ['wallet_type_id_from'];
				$rawdata ['wallet_type_id_from_text'] = $this->getTypeMessage ( $rawdata ['wallet_type_id_from'] );
				
				$rawdata ['wallet_type_id_to_text'] = $rawdata ['wallet_type_id_to'];
				$rawdata ['wallet_type_id_to_text'] = $this->getTypeMessage ( $rawdata ['wallet_type_id_to'] );
				
				$rawdata ['wallet_transaction_id_text'] = $rawdata ['wallet_transaction_id'];
				$rawdata ['wallet_transaction_id_text'] = $this->getTransactionMessage ( $rawdata ['wallet_transaction_id'] );
				
				$rawdata ['wallet_status_id_text'] = $rawdata ['wallet_status_id'];
				$rawdata ['wallet_status_id_text'] = $this->getStatusMessage ( $rawdata ['wallet_status_id'] );
				
				$rawdata ['user_wallet_history_amount_in'] = $this->formatNumber ( $rawdata ['user_wallet_history_amount_in'] );
				$rawdata ['user_wallet_history_amount_out'] = $this->formatNumber ( $rawdata ['user_wallet_history_amount_out'] );
				
				$rawdata ['user_wallet_history_modified_date_format'] = "";
				if ($rawdata ['user_wallet_history_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_modified_date'] );
					$rawdata ['user_wallet_history_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_wallet_history_created_date_format'] = "";
				if ($rawdata ['user_wallet_history_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_created_date'] );
					$rawdata ['user_wallet_history_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_wallet_history_approved_date_format'] = "";
				if ($rawdata ['user_wallet_history_approved_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_wallet_history_approved_date'] );
					$rawdata ['user_wallet_history_approved_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
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
	 * Get Withdraw
	 */
	public function getWithdraw($id = null) {
		$data = $this->getWithdrawData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
}

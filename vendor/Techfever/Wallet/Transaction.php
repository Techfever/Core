<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Transaction extends Status {
	
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
	 * @var Wallet transaction Data
	 *     
	 */
	private $wallet_transaction_data = null;
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
	 * Get Transaction Data
	 *
	 * @return array data
	 *        
	 */
	public function getTransactionData() {
		if (! is_array ( $this->wallet_transaction_data ) || count ( $this->wallet_transaction_data ) < 1) {
			$rawdata = array ();
			$QTransaction = $this->getDatabase ();
			$QTransaction->select ();
			$QTransaction->columns ( array (
					'id' => 'wallet_transaction_id',
					'key' => 'wallet_transaction_key',
					'created_date' => 'wallet_transaction_created_date',
					'modified_date' => 'wallet_transaction_modified_date',
					'created_by' => 'wallet_transaction_created_by',
					'modified_by' => 'wallet_transaction_modified_by' 
			) );
			$QTransaction->from ( array (
					'wt' => 'wallet_transaction' 
			) );
			$QTransaction->order ( array (
					'wt.wallet_transaction_sort_order ASC' 
			) );
			$QTransaction->execute ();
			if ($QTransaction->hasResult ()) {
				while ( $QTransaction->valid () ) {
					$rawdata = $QTransaction->current ();
					$this->wallet_transaction_data [$rawdata ['id']] = $rawdata;
					$QTransaction->next ();
				}
			}
		}
		return $this->wallet_transaction_data;
	}
	
	/**
	 * Get Transaction
	 */
	public function getTransaction($id = null) {
		$data = $this->getTransactionData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get Transaction Message
	 */
	public function getTransactionMessage($id = null) {
		$key = $this->getTransactionKey ( $id );
		$name = "";
		if (strlen ( $key ) > 0) {
			$name = $this->getTranslate ( 'text_wallet_transaction_' . $key );
		}
		return $name;
	}
	
	/**
	 * Get Transaction Key
	 */
	public function getTransactionKey($id = null) {
		$data = $this->getTransaction ( $id );
		$key = null;
		if (is_array ( $data ) && array_key_exists ( 'key', $data )) {
			$key = $data ['key'];
		}
		return $key;
	}
	
	/**
	 * Transaction To Form
	 */
	public function TransactionToForm() {
		$data = $this->getTransactionData ();
		$transactiondata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $transaction ) {
				$transactiondata [$transaction ['id']] = $this->getTransactionMessage ( $transaction ['id'] );
			}
		}
		ksort ( $transactiondata );
		return $transactiondata;
	}
}

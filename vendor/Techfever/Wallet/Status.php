<?php

namespace Techfever\Wallet;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Status extends GeneralBase {
	
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
	 * @var Wallet status Data
	 *     
	 */
	private $wallet_status_data = null;
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
	 * Get Status Data
	 *
	 * @return array data
	 *        
	 */
	public function getStatusData() {
		if (! is_array ( $this->wallet_status_data ) || count ( $this->wallet_status_data ) < 1) {
			$rawdata = array ();
			$QStatus = $this->getDatabase ();
			$QStatus->select ();
			$QStatus->columns ( array (
					'id' => 'wallet_status_id',
					'key' => 'wallet_status_key',
					'created_date' => 'wallet_status_created_date',
					'modified_date' => 'wallet_status_modified_date',
					'created_by' => 'wallet_status_created_by',
					'modified_by' => 'wallet_status_modified_by' 
			) );
			$QStatus->from ( array (
					'ws' => 'wallet_status' 
			) );
			$QStatus->setCacheName ( 'wallet_status' );
			$QStatus->execute ();
			if ($QStatus->hasResult ()) {
				while ( $QStatus->valid () ) {
					$rawdata = $QStatus->current ();
					$this->wallet_status_data [$rawdata ['id']] = $rawdata;
					$QStatus->next ();
				}
			}
		}
		return $this->wallet_status_data;
	}
	
	/**
	 * Get Status
	 */
	public function getStatus($id = null) {
		$data = $this->getStatusData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get Status Message
	 */
	public function getStatusMessage($id = null) {
		$key = $this->getStatusKey ( $id );
		$name = "";
		if (strlen ( $key ) > 0) {
			$name = $this->getTranslate ( 'text_wallet_status_' . $key );
		}
		return $name;
	}
	
	/**
	 * Get Status Key
	 */
	public function getStatusKey($id = null) {
		$data = $this->getStatus ( $id );
		$key = null;
		if (is_array ( $data ) && array_key_exists ( 'key', $data )) {
			$key = $data ['key'];
		}
		return $key;
	}
	
	/**
	 * Status To Form
	 */
	public function StatusToForm() {
		$data = $this->getStatusData ();
		$statusdata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $status ) {
				$statusdata [$status ['id']] = $this->getStatusMessage ( $status ['id'] );
			}
		}
		ksort ( $statusdata );
		return $statusdata;
	}
}

<?php

namespace Techfever\Mt4;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;
use Techfever\Parameter\Parameter;
use Techfever\User\Rank;

class Management extends GeneralBase {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'mt4' => 0 
	);
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
	 * createMT4User
	 */
	public function createMT4User($data) {
		$status = true;
		$mt4_id = 0;
		$IMT4 = $this->getDatabase ();
		$IMT4->insert ();
		$IMT4->into ( 'user_mt4' );
		$IMT4->values ( array (
				'user_access_id' => $data ['user_access_id'],
				'user_mt4_fullname' => $data ['user_mt4_fullname'],
				'user_mt4_email_address' => $data ['user_mt4_email_address'],
				'user_mt4_nric_passport' => $data ['user_mt4_nric_passport'],
				'user_mt4_bank_name' => $data ['user_mt4_bank_name'],
				'user_mt4_bank_holder_name' => $data ['user_mt4_bank_holder_name'],
				'user_mt4_bank_holder_account' => $data ['user_mt4_bank_holder_no'],
				'user_mt4_agent' => $data ['user_mt4_agent'],
				'user_mt4_created_date' => $data ['log_created_date'],
				'user_mt4_modified_date' => $data ['log_modified_date'],
				'user_mt4_created_by' => $data ['log_created_by'],
				'user_mt4_modified_by' => $data ['log_modified_by'] 
		) );
		$IMT4->execute ();
		if ($IMT4->affectedRows ()) {
			$mt4_id = $IMT4->getLastGeneratedValue ();
			$this->setOption ( 'mt4', $mt4_id );
		} else {
			$status = false;
		}
		if ($status) {
			
			return $mt4_id;
		} else {
			$this->deleteMT4User ( true );
			return false;
		}
	}
	
	/**
	 * deleteMT4User
	 */
	public function deleteMT4User($forever = false) {
		$mt4 = $this->getOption ( 'mt4' );
		if ($forever) {
			if ($mt4 > 1) {
				$DMT4 = $this->getDatabase ();
				$DMT4->delete ();
				$DMT4->from ( 'user_mt4' );
				$DMT4->where ( array (
						'user_mt4_id' => $mt4 
				) );
				$DMT4->execute ();
			}
		} else {
			if ($mt4 > 1) {
				$UMT4 = $this->getDatabase ();
				$UMT4->update ();
				$UMT4->table ( 'user_mt4' );
				$UMT4->set ( array (
						'user_mt4_delete_status' => '1' 
				) );
				$UMT4->where ( array (
						'user_mt4_id' => $mt4 
				) );
				$UMT4->execute ();
			}
		}
		return true;
	}
	
	/**
	 * getData
	 */
	public function getData($id = null, $rank_group = null) {
		$data = array ();
		if ($id > 0) {
			$this->setOption ( 'mt4', $id );
		}
		$orderstr = null;
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'mt' => 'user_mt4' 
		) );
		$QUser->join ( array (
				'ua' => 'user_access' 
		), 'mt.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$where = array (
				'mt.user_mt4_id' => $this->getOption ( 'mt4' ),
				'mt.user_mt4_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		$QUser->where ( $where );
		$QUser->execute ();
		if ($QUser->hasResult ()) {
			$data = $QUser->current ();
			
			$cryptID = $this->Encrypt ( $data ['user_mt4_id'] );
			$data ['id'] = $cryptID;
			
			$data ['modify_value'] = $cryptID;
			
			$data ['user_access_created_date_format'] = "";
			if ($data ['user_access_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_created_date'] );
				$data ['user_access_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_access_modified_date_format'] = "";
			if ($data ['user_access_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_access_modified_date'] );
				$data ['user_access_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_mt4_created_date_format'] = "";
			if ($data ['user_mt4_created_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_mt4_created_date'] );
				$data ['user_mt4_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
			}
			
			$data ['user_mt4_modified_date_format'] = "";
			if ($data ['user_mt4_modified_date'] !== "0000-00-00 00:00:00") {
				$datetime = new \DateTime ( $data ['user_mt4_modified_date'] );
				$data ['user_mt4_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
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
	public function getListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'mt' => 'user_mt4' 
		) );
		$QUser->join ( array (
				'ua' => 'user_access' 
		), 'mt.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$where = array (
				'mt.user_mt4_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access', $search )) {
			$where = array_merge ( $where, $search ['user_access'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_mt4', $search )) {
			$where = array_merge ( $where, $search ['user_mt4'] );
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
	public function getListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$Rank = new Rank ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'*' 
		) );
		$QUser->from ( array (
				'mt' => 'user_mt4' 
		) );
		$QUser->join ( array (
				'ua' => 'user_access' 
		), 'mt.user_access_id  = ua.user_access_id', array (
				'*' 
		) );
		$where = array (
				'mt.user_mt4_delete_status' => '0',
				'ua.user_access_delete_status' => '0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_mt4', $search )) {
			$where = array_merge ( $where, $search ['user_mt4'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'user_access', $search )) {
			$where = array_merge ( $where, $search ['user_access'] );
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
				
				$cryptID = $this->Encrypt ( $rawdata ['user_mt4_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['user_mt4_id']);
				
				$rawdata ['user_access_created_date_format'] = "";
				if ($rawdata ['user_access_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_created_date'] );
					$rawdata ['user_access_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_modified_date_format'] = "";
				if ($rawdata ['user_access_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_access_modified_date'] );
					$rawdata ['user_access_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_mt4_created_date_format'] = "";
				if ($rawdata ['user_mt4_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_mt4_created_date'] );
					$rawdata ['user_mt4_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_mt4_modified_date_format'] = "";
				if ($rawdata ['user_mt4_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['user_mt4_modified_date'] );
					$rawdata ['user_mt4_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['user_access_status_text'] = $rawdata ['user_access_status'];
				$valueStatus = $StatusParameter->getMessageByValue ( $rawdata ['user_access_status_text'] );
				if (strlen ( $valueStatus ) > 0) {
					$rawdata ['user_access_status_text'] = $valueStatus;
				}
				
				$rawdata ['user_rank_text'] = $rawdata ['user_rank_id'];
				$valueRank = $Rank->getMessage ( $rawdata ['user_rank_id'] );
				if (strlen ( $valueRank ) > 0) {
					$rawdata ['user_rank_text'] = $valueRank;
				}
				
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
	 * verify ID
	 *
	 * @return Interger
	 *
	 */
	public function verifyID($id = null) {
		if (! empty ( $id )) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'user_mt4_id' 
			) );
			$DBVerify->from ( array (
					'mt' => 'user_mt4' 
			) );
			$where = array (
					'mt.user_mt4_id = "' . $id . '"',
					'mt.user_mt4_delete_status = 0' 
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
	 * verify ID
	 *
	 * @return Interger
	 *
	 */
	public function verifyUser($id = null) {
		if (! empty ( $id )) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'user_mt4_id' 
			) );
			$DBVerify->from ( array (
					'mt' => 'user_mt4' 
			) );
			$where = array (
					'mt.user_access_id = "' . $id . '"',
					'mt.user_mt4_delete_status = 0' 
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
	 * get ID
	 *
	 * @return Interger
	 *
	 */
	public function getID($id = null) {
		$status = false;
		if (! empty ( $id )) {
			$QUser = $this->getDatabase ();
			$QUser->select ();
			$QUser->columns ( array (
					'id' => 'user_mt4_id' 
			) );
			$QUser->from ( array (
					'mt' => 'user_mt4' 
			) );
			$where = array (
					'mt.user_access_id = "' . $id . '"',
					'mt.user_mt4_delete_status = 0' 
			);
			$QUser->where ( $where );
			$QUser->limit ( 1 );
			$QUser->execute ();
			if ($QUser->hasResult ()) {
				$data = $QUser->current ();
				$mt4_id = $data ['id'];
				$status = true;
			}
		}
		if ($status) {
			return $mt4_id;
		} else {
			return false;
		}
	}
}

<?php

namespace Techfever\Document;

use Techfever\Exception;
use Techfever\Document\Data\Data as GData;
use Techfever\Parameter\Parameter;

class Data extends GData {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Document Data All
	 *     
	 */
	private $document_data_all = null;
	
	/**
	 * Initial Document Data
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
	 * Verify Document Data Fixed
	 *
	 * @return array()
	 */
	public function isDataFixed() {
		$status = false;
		$data_id = $this->getDocumentDataID ();
		$type_id = $this->getDocumentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getDocumentUserID ();
		}
		$rawdata = array ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'document_data_fixed_status' 
		) );
		$QData->from ( array (
				'cd' => 'document_data' 
		) );
		$where = array (
				'cd.document_data_id = "' . $data_id . '"',
				'cd.document_type_id = "' . $type_id . '"',
				'cd.document_data_delete_status = "0"' 
		);
		if (! is_null ( $user_id ) && $user_id > 0) {
			$where [] = 'cd.user_access_id = "' . $user_id . '"';
		}
		$QData->where ( $where );
		$QData->limit ( 1 );
		$QData->execute ();
		if ($QData->hasResult ()) {
			while ( $QData->valid () ) {
				$rawdata = $QData->current ();
				if ($rawdata ['document_data_fixed_status'] == "1") {
					$status = true;
				}
				$QData->next ();
			}
		}
		return $status;
	}
	
	/**
	 * Search Document Data
	 *
	 * @return array()
	 */
	public function searchData($search = null) {
		$id = 0;
		if (! empty ( $search )) {
			$type_id = $this->getDocumentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getDocumentUserID ();
			}
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'document_data_id' 
			) );
			$QData->from ( array (
					'cd' => 'document_data' 
			) );
			$where = array (
					'cd.document_data_ref_id = "' . $search . '"',
					'cd.document_type_id = "' . $type_id . '"',
					'cd.document_data_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QData->where ( $where );
			$QData->limit ( 1 );
			$QData->execute ();
			if ($QData->hasResult ()) {
				while ( $QData->valid () ) {
					$rawdata = $QData->current ();
					$id = $rawdata ['document_data_id'];
					$QData->next ();
				}
			}
		}
		return ( int ) $id;
	}
	
	/**
	 * Get Document Data
	 *
	 * @return string
	 */
	public function getDataCode($id = null) {
		$code = "";
		if (! empty ( $id ) && $id > 0) {
			$type_id = $this->getDocumentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getDocumentUserID ();
			}
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'document_data_ref_id' 
			) );
			$QData->from ( array (
					'cd' => 'document_data' 
			) );
			$where = array (
					'cd.document_data_id = "' . $id . '"',
					'cd.document_type_id = "' . $type_id . '"',
					'cd.document_data_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QData->where ( $where );
			$QData->limit ( 1 );
			$QData->execute ();
			if ($QData->hasResult ()) {
				while ( $QData->valid () ) {
					$rawdata = $QData->current ();
					$code = $rawdata ['document_data_ref_id'];
					$QData->next ();
				}
			}
		}
		return ( string ) $code;
	}
	
	/**
	 * Get Document Data Complete
	 *
	 * @return array()
	 */
	public function getDataComplete() {
		if (! is_array ( $this->document_data_all ) || count ( $this->document_data_all ) < 1) {
			$rawdata = array ();
			$data = $this->getData ();
			if (is_array ( $data ) && count ( $data ) > 0) {
				$detail = $this->getDataDetail ();
				$permission = $this->getDataPermission ();
				
				$rawdata ['data'] = $data;
				$rawdata ['detail'] = $detail;
				$rawdata ['permission'] = $permission;
			}
			$this->document_data_all = $rawdata;
		}
		return $this->document_data_all;
	}
	
	/**
	 * Get Document Data List
	 */
	public function getDataListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		$PublishParameter = new Parameter ( array (
				'key' => 'document_data_publish_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$LoginParameter = new Parameter ( array (
				'key' => 'document_data_login_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$user_id = $this->getDocumentUserID ();
		$type_id = $this->getDocumentTypeID ();
		$language_id = $this->getDocumentLanguageID ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'document_data_id',
				'document_data_publish_status',
				'document_data_publish_date',
				'document_data_login_status',
				'user_access_id',
				'document_data_ref_id',
				'document_data_taken_date',
				'document_data_taken_by',
				'document_data_location',
				'document_data_created_date',
				'document_data_modified_date',
				'document_data_created_by',
				'document_data_modified_by' 
		) );
		$QData->from ( array (
				'cd' => 'document_data' 
		) );
		$QData->join ( array (
				'cdd' => 'document_data_detail' 
		), 'cdd.document_data_id  = cd.document_data_id', array (
				'document_data_detail_id',
				'document_data_detail_title',
				'document_data_detail_information',
				'document_data_detail_created_date',
				'document_data_detail_created_by',
				'document_data_detail_modified_date',
				'document_data_detail_modified_by' 
		) );
		$where = array (
				'cd.document_type_id = ' . $type_id,
				'cd.document_data_delete_status = 0',
				'cdd.document_data_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'document_data', $search )) {
			$where = array_merge ( $where, $search ['document_data'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'document_data_detail', $search )) {
			$where = array_merge ( $where, $search ['document_data_detail'] );
		}
		$QData->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'cd.document_data_created_date' 
			);
		}
		$QData->order ( $order );
		if (isset ( $perpage )) {
			$QData->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QData->offset ( ( int ) $index );
		}
		$QData->execute ();
		if ($QData->hasResult ()) {
			$data = array ();
			$count = 1;
			while ( $QData->valid () ) {
				$rawdata = $QData->current ();
				$rawdata ['no'] = $count;
				
				$rawdata ['document_data_code'] = $rawdata ['document_data_ref_id'];
				$cryptID = $this->Encrypt ( $rawdata ['document_data_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['document_data_id']);
				
				$rawdata ['document_data_publish_date_format'] = "";
				if ($rawdata ['document_data_publish_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_publish_date'] );
					$rawdata ['document_data_publish_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['document_data_taken_date_format'] = "";
				if ($rawdata ['document_data_taken_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_taken_date'] );
					$rawdata ['document_data_taken_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['document_data_created_date_format'] = "";
				if ($rawdata ['document_data_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_created_date'] );
					$rawdata ['document_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['document_data_modified_date_format'] = "";
				if ($rawdata ['document_data_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_modified_date'] );
					$rawdata ['document_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['document_data_detail_created_date_format'] = "";
				if ($rawdata ['document_data_detail_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_detail_created_date'] );
					$rawdata ['document_data_detail_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['document_data_detail_modified_date_format'] = "";
				if ($rawdata ['document_data_detail_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['document_data_detail_modified_date'] );
					$rawdata ['document_data_detail_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['document_data_publish_status_text'] = "";
				if ($PublishParameter->hasResult ()) {
					$rawdata ['document_data_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['document_data_publish_status'] );
				}
				
				$rawdata ['document_data_login_status_text'] = "";
				if ($LoginParameter->hasResult ()) {
					$rawdata ['document_data_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['document_data_login_status'] );
				}
				
				$rawdata ['document_data_login_status'] = ($rawdata ['document_data_login_status'] == "1" ? True : False);
				
				$QData->next ();
				ksort ( $rawdata );
				$data [$rawdata ['document_data_id']] = $rawdata;
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
	 * Get Document Data List Total
	 */
	public function getDataListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		
		$type_id = $this->getDocumentTypeID ();
		$user_id = $this->getDocumentUserID ();
		$language_id = $this->getDocumentLanguageID ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'document_data_id',
				'document_data_publish_status',
				'document_data_publish_date',
				'document_data_login_status',
				'user_access_id',
				'document_data_ref_id',
				'document_data_taken_date',
				'document_data_taken_by',
				'document_data_location',
				'document_data_created_date',
				'document_data_modified_date',
				'document_data_created_by',
				'document_data_modified_by' 
		) );
		$QData->from ( array (
				'cd' => 'document_data' 
		) );
		$QData->join ( array (
				'cdd' => 'document_data_detail' 
		), 'cdd.document_data_id  = cd.document_data_id', array (
				'document_data_detail_id',
				'document_data_detail_title',
				'document_data_detail_information',
				'document_data_detail_created_date',
				'document_data_detail_created_by' 
		) );
		$where = array (
				'cd.document_type_id = ' . $type_id,
				'cd.document_data_delete_status = 0',
				'cdd.document_data_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'document_data', $search )) {
			$where = array_merge ( $where, $search ['document_data'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'document_data_detail', $search )) {
			$where = array_merge ( $where, $search ['document_data_detail'] );
		}
		$QData->where ( $where );
		$QData->execute ();
		$count = 0;
		if ($QData->hasResult ()) {
			$count = $QData->count ();
		}
		return $count;
	}
	
	/**
	 * Update Document Data
	 */
	public function updateDataFactory($data) {
		$status = false;
		$rawdata = $this->generateDataData ( $data );
		if (count ( $rawdata ) > 0) {
			$status = true;
			if (! $this->updateData ( $rawdata ['data'] )) {
				$status = false;
			}
			if ($status) {
				if (! $this->updateDataDetail ( $rawdata ['detail'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateDataPermission ( $rawdata ['permission'] )) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Document Data
	 */
	public function createDataFactory($data) {
		$status = false;
		$rawdata = $this->generateDataData ( $data, true );
		if (count ( $rawdata ) > 0) {
			$data_id = $this->createData ( $rawdata ['data'] );
			if ($data_id != false) {
				$this->setDocumentDataID ( $data_id );
				$status = true;
				if ($status) {
					if (! $this->createDataDetail ( $rawdata ['detail'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createDataPermission ( $rawdata ['permission'] )) {
						$status = false;
					}
				}
			}
		}
		if ($status) {
			return $data_id;
		} else {
			$this->deleteDataFactory ( true );
		}
		return $status;
	}
	
	/**
	 * Delete Document Data
	 */
	public function deleteDataFactory($forever = false) {
		$this->deleteData ( $forever );
		$this->deleteDataDetail ( $forever );
		$this->deleteDataPermission ( $forever );
		return true;
	}
	
	/**
	 * Generate Document Data Data
	 */
	public function generateDataData($data, $update = false) {
		$rawdata = array ();
		$old_data = array ();
		if ($update) {
			$old_data = $this->getDataComplete ();
			$old_data = $this->dataDataArrange ( $old_data );
		}
		if (array_key_exists ( 'document_data_publish_status', $data ) && isset ( $data ['document_data_publish_status'] )) {
			$PublishParameter = new Parameter ( array (
					'key' => 'document_data_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PublishParameter->hasResult ()) {
				$publishstatus = $PublishParameter->getValueByKey ( $data ['document_data_publish_status'] );
			}
		} else {
			$data ['document_data_publish_status'] = 1;
		}
		
		if (array_key_exists ( 'document_data_login_status', $data ) && isset ( $data ['document_data_login_status'] )) {
			$LoginParameter = new Parameter ( array (
					'key' => 'document_data_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($LoginParameter->hasResult ()) {
				$loginstatus = $LoginParameter->getValueByKey ( $data ['document_data_login_status'] );
			}
		} else {
			$data ['document_data_login_status'] = 0;
		}
		
		if (! array_key_exists ( 'document_data_publish_date', $data ) || ! isset ( $data ['document_data_publish_date'] )) {
			if ($update) {
				$data ['document_data_publish_date'] = $data ['document_data_publish_date'];
			} else {
				$data ['document_data_publish_date'] = $data ['log_created_date'];
			}
		}
		
		if (! array_key_exists ( 'document_data_taken_date', $data ) || ! isset ( $data ['document_data_taken_date'] )) {
			if ($update) {
				$data ['document_data_taken_date'] = $data ['document_data_taken_date'];
			} else {
				$data ['document_data_taken_date'] = $data ['log_created_date'];
			}
		}
		
		$rawdata ['data'] = array (
				'document_data_publish_status' => $publishstatus,
				'document_data_publish_date' => $data ['document_data_publish_date'],
				'document_data_login_status' => $loginstatus,
				'timestamp' => $data ['timestamp'],
				'document_data_taken_date' => $data ['document_data_taken_date'],
				'document_data_taken_by' => $data ['document_data_taken_by'],
				'document_data_location' => $data ['document_data_location'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		
		$rawdata ['detail'] = array ();
		$AllLocale = $this->getTranslator ()->getAllLocale ();
		$DefaultLocale = $this->getTranslator ()->getLocaleIDbyISO ( SYSTEM_DEFAULT_LOCALE );
		if (is_array ( $AllLocale ) && count ( $AllLocale ) > 0) {
			foreach ( $AllLocale as $locale_value ) {
				$data_status = false;
				$locale_id = $locale_value ['id'];
				$locale_iso = strtolower ( $locale_value ['iso'] );
				$title = null;
				$information = null;
				if (array_key_exists ( 'document_data_detail_title_' . $locale_iso, $data )) {
					$title = $data ['document_data_detail_title_' . $locale_iso];
					$data_status = true;
				}
				if (array_key_exists ( 'document_data_detail_title_' . $locale_iso, $data )) {
					$information = $data ['document_data_detail_information_' . $locale_iso];
				}
				if ($data_status) {
					$rawdata ['detail'] [$locale_id] = array (
							'system_language_id' => $locale_id,
							'document_data_detail_title' => $title,
							'document_data_detail_information' => $information,
							'timestamp' => $data ['timestamp'],
							'log_created_by' => $data ['log_created_by'],
							'log_created_date' => $data ['log_created_date'],
							'log_modified_by' => $data ['log_modified_by'],
							'log_modified_date' => $data ['log_modified_date'] 
					);
				}
			}
		}
		
		$rawdata ['permission'] = array ();
		$rawdata ['permission'] [0] = array (
				'document_data_permission_visitor' => 1,
				'document_data_permission_user' => 0,
				'document_data_permission_rank' => 0,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		if (array_key_exists ( 'document_data_permission_visitor', $data )) {
			$PermissionVisitorParameter = new Parameter ( array (
					'key' => 'document_data_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PermissionVisitorParameter->hasResult ()) {
				$permissionvisitorstatus = $PermissionVisitorParameter->getValueByKey ( $data ['document_data_permission_visitor'] );
			}
			$rawdata ['permission'] [0] = array (
					'document_data_permission_visitor' => $permissionvisitorstatus,
					'document_data_permission_user' => 0,
					'document_data_permission_rank' => 0,
					'timestamp' => $data ['timestamp'],
					'log_created_by' => $data ['log_created_by'],
					'log_created_date' => $data ['log_created_date'],
					'log_modified_by' => $data ['log_modified_by'],
					'log_modified_date' => $data ['log_modified_date'] 
			);
			if ($permissionvisitorstatus == 0) {
				if (array_key_exists ( 'document_data_permission_user', $data ) && count ( $data ['document_data_permission_user'] ) > 0) {
					foreach ( $data ['document_data_permission_user'] as $user_value ) {
						$user_value = $this->Decrypt ( $user_value );
						if ($user_value > 0) {
							$rawdata ['permission'] [] = array (
									'document_data_permission_visitor' => 0,
									'document_data_permission_user' => $user_value,
									'document_data_permission_rank' => 0,
									'timestamp' => $data ['timestamp'],
									'log_created_by' => $data ['log_created_by'],
									'log_created_date' => $data ['log_created_date'],
									'log_modified_by' => $data ['log_modified_by'],
									'log_modified_date' => $data ['log_modified_date'] 
							);
						}
					}
				}
				if (array_key_exists ( 'document_data_permission_rank', $data ) && count ( $data ['document_data_permission_rank'] ) > 0) {
					foreach ( $data ['document_data_permission_rank'] as $rank_value ) {
						$rank_value = $this->Decrypt ( $rank_value );
						if ($rank_value > 0) {
							$rawdata ['permission'] [] = array (
									'document_data_permission_visitor' => 0,
									'document_data_permission_user' => 0,
									'document_data_permission_rank' => $rank_value,
									'timestamp' => $data ['timestamp'],
									'log_created_by' => $data ['log_created_by'],
									'log_created_date' => $data ['log_created_date'],
									'log_modified_by' => $data ['log_modified_by'],
									'log_modified_date' => $data ['log_modified_date'] 
							);
						}
					}
				}
			}
		}
		
		unset ( $data );
		return $rawdata;
	}
	
	/**
	 * Data DataArrange
	 *
	 * @return Array
	 */
	public function dataDataArrange($data = null) {
		$rawdata = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (is_array ( $data ['detail'] ) && count ( $data ['detail'] ) > 0) {
				$Translator = $this->getTranslator ();
				foreach ( $data ['detail'] as $details_key => $details_value ) {
					$iso = $Translator->getLocaleISObyID ( $details_key );
					foreach ( $details_value as $detail_key => $detail_value ) {
						$rawdata [strtolower ( $detail_key . '_' . $iso )] = $detail_value;
					}
				}
			}
			$rawdata = array_merge ( $rawdata, $data ['data'] );
			$rawdata ['document_data_permission_visitor_text'] = "";
			$rawdata ['document_data_permission_rank'] = "";
			$rawdata ['document_data_permission_user'] = "";
			$rawdata ['document_data_permission_rank_options'] = array ();
			$rawdata ['document_data_permission_user_options'] = array ();
			if (is_array ( $data ['permission'] ) && count ( $data ['permission'] ) > 0) {
				$permission_rank_status = false;
				$permission_user_status = false;
				$permission_visitor_status = false;
				$document_data_permission_rank = "";
				$document_data_permission_user = "";
				$document_data_permission_rank_options = array ();
				$document_data_permission_user_options = array ();
				$document_data_permission_visitor_text = "";
				$document_data_permission_visitor = "";
				foreach ( $data ['permission'] as $permissions_key => $permissions_value ) {
					if (array_key_exists ( 'document_data_permission_rank', $permissions_value ) && ! empty ( $permissions_value ['document_data_permission_rank'] )) {
						$document_data_permission_rank .= $permissions_value ['document_data_permission_rank'] . ", ";
						$document_data_permission_rank_options [$permissions_value ['document_data_permission_rank_options']] = $permissions_value ['document_data_permission_rank'];
						$permission_rank_status = true;
					}
					if (array_key_exists ( 'document_data_permission_user', $permissions_value ) && ! empty ( $permissions_value ['document_data_permission_user'] )) {
						$document_data_permission_user .= $permissions_value ['document_data_permission_user'] . ", ";
						$document_data_permission_user_options [$permissions_value ['document_data_permission_user_options']] = $permissions_value ['document_data_permission_user'];
						$permission_user_status = true;
					}
					if (array_key_exists ( 'document_data_permission_visitor', $permissions_value )) {
						if ($permissions_value ['document_data_permission_visitor'] == 1) {
							$document_data_permission_visitor_text = $permissions_value ['document_data_permission_visitor_text'];
							$document_data_permission_visitor = $permissions_value ['document_data_permission_visitor'];
							$permission_visitor_status = true;
						}
						if (! $permission_visitor_status) {
							$document_data_permission_visitor_text = $permissions_value ['document_data_permission_visitor_text'];
							$document_data_permission_visitor = $permissions_value ['document_data_permission_visitor'];
						}
					}
				}
				$document_data_permission_rank = trim ( $document_data_permission_rank );
				if (substr ( $document_data_permission_rank, strlen ( $document_data_permission_rank ) - 1 ) == ",") {
					$document_data_permission_rank = substr ( $document_data_permission_rank, 0, (strlen ( $document_data_permission_rank ) - 1) );
				}
				$document_data_permission_rank = trim ( $document_data_permission_rank );
				if (substr ( $document_data_permission_rank, strlen ( $document_data_permission_rank ) - 1 ) == ",") {
					$document_data_permission_rank = substr ( $document_data_permission_rank, 0, (strlen ( $document_data_permission_rank ) - 1) );
				}
				$rawdata ['document_data_permission_visitor_text'] = $document_data_permission_visitor_text;
				$rawdata ['document_data_permission_visitor'] = $document_data_permission_visitor;
				if (! $permission_visitor_status) {
					$rawdata ['document_data_permission_rank'] = $document_data_permission_rank;
					$rawdata ['document_data_permission_user'] = $document_data_permission_user;
					$rawdata ['document_data_permission_rank_options'] = $document_data_permission_rank_options;
					$rawdata ['document_data_permission_user_options'] = $document_data_permission_user_options;
				} else {
					$rawdata ['document_data_permission_rank'] = 'N/A';
					$rawdata ['document_data_permission_user'] = 'N/A';
					$rawdata ['document_data_permission_rank_options'] = 'N/A';
					$rawdata ['document_data_permission_user_options'] = 'N/A';
				}
			}
			ksort ( $rawdata );
		}
		return $rawdata;
	}
}

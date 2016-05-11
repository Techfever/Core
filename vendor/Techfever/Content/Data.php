<?php

namespace Techfever\Content;

use Techfever\Exception;
use Techfever\Content\Data\Data as GData;
use Techfever\Parameter\Parameter;
use Techfever\Template\Plugin\Filters\ToUnderscore;

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
			'data_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Data All
	 *     
	 */
	private $content_data_all = null;
	
	/**
	 * Initial Content Data
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
	 * Verify Content Data Fixed
	 *
	 * @return array()
	 */
	public function isDataFixed() {
		$status = false;
		$data_id = $this->getContentDataID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		$rawdata = array ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'content_data_fixed_status' 
		) );
		$QData->from ( array (
				'cd' => 'content_data' 
		) );
		$where = array (
				'cd.content_data_id = "' . $data_id . '"',
				'cd.content_type_id = "' . $type_id . '"',
				'cd.content_data_delete_status = "0"' 
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
				if ($rawdata ['content_data_fixed_status'] == "1") {
					$status = true;
				}
				$QData->next ();
			}
		}
		return $status;
	}
	
	/**
	 * Search Content Data
	 *
	 * @return array()
	 */
	public function searchData($search = null) {
		$id = 0;
		if (! empty ( $search )) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'content_data_id' 
			) );
			$QData->from ( array (
					'cd' => 'content_data' 
			) );
			$where = array (
					'cd.content_data_ref_id = "' . $search . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_data_delete_status = "0"' 
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
					$id = $rawdata ['content_data_id'];
					$QData->next ();
				}
			}
		}
		return ( int ) $id;
	}
	
	/**
	 * Get Content Data
	 *
	 * @return string
	 */
	public function getDataCode($id = null) {
		$code = "";
		if (! empty ( $id ) && $id > 0) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'content_data_ref_id' 
			) );
			$QData->from ( array (
					'cd' => 'content_data' 
			) );
			$where = array (
					'cd.content_data_id = "' . $id . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_data_delete_status = "0"' 
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
					$code = $rawdata ['content_data_ref_id'];
					$QData->next ();
				}
			}
		}
		return ( string ) $code;
	}
	
	/**
	 * Get Content Data Complete
	 *
	 * @return array()
	 */
	public function getDataComplete() {
		if (! is_array ( $this->content_data_all ) || count ( $this->content_data_all ) < 1) {
			$rawdata = array ();
			$data = $this->getData ();
			if (is_array ( $data ) && count ( $data ) > 0) {
				$detail = $this->getDataDetail ();
				$permission = $this->getDataPermission ();
				$url = $this->getDataUrl ();
				$link_label = $this->getDataLinkLabel ();
				$link_tag = $this->getDataLinkTag ();
				
				$rawdata ['data'] = $data;
				$rawdata ['detail'] = $detail;
				$rawdata ['permission'] = $permission;
				$rawdata ['url'] = $url;
				$rawdata ['link_label'] = $link_label;
				$rawdata ['link_tag'] = $link_tag;
			}
			$this->content_data_all = $rawdata;
		}
		return $this->content_data_all;
	}
	
	/**
	 * Get Content Data List
	 */
	public function getDataListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		$PublishParameter = new Parameter ( array (
				'key' => 'content_data_publish_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$LoginParameter = new Parameter ( array (
				'key' => 'content_data_login_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$user_id = $this->getContentUserID ();
		$type_id = $this->getContentTypeID ();
		$language_id = $this->getContentLanguageID ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'content_data_id',
				'content_data_publish_status',
				'content_data_publish_date',
				'content_data_login_status',
				'user_access_id',
				'content_data_ref_id',
				'content_data_taken_date',
				'content_data_taken_by',
				'content_data_location',
				'content_data_created_date',
				'content_data_modified_date',
				'content_data_created_by',
				'content_data_modified_by' 
		) );
		$QData->from ( array (
				'cd' => 'content_data' 
		) );
		$QData->join ( array (
				'cdd' => 'content_data_detail' 
		), 'cdd.content_data_id  = cd.content_data_id', array (
				'content_data_detail_id',
				'content_data_detail_title',
				'content_data_detail_information',
				'content_data_detail_created_date',
				'content_data_detail_created_by',
				'content_data_detail_modified_date',
				'content_data_detail_modified_by' 
		) );
		$QData->join ( array (
				'cdu' => 'content_data_url' 
		), 'cdu.content_data_id = cd.content_data_id', array (
				'content_data_url_id',
				'content_data_url_keyword',
				'content_data_url_created_date',
				'content_data_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_data_delete_status = 0',
				'cdd.content_data_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_data_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data', $search )) {
			$where = array_merge ( $where, $search ['content_data'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data_detail', $search )) {
			$where = array_merge ( $where, $search ['content_data_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data_url', $search )) {
			$where = array_merge ( $where, $search ['content_data_url'] );
		}
		$QData->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'cd.content_data_created_date' 
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
				
				$rawdata ['content_data_code'] = $rawdata ['content_data_ref_id'];
				$cryptID = $this->Encrypt ( $rawdata ['content_data_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['content_data_id']);
				
				$rawdata ['content_data_publish_date_format'] = "";
				if ($rawdata ['content_data_publish_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_publish_date'] );
					$rawdata ['content_data_publish_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['content_data_taken_date_format'] = "";
				if ($rawdata ['content_data_taken_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_taken_date'] );
					$rawdata ['content_data_taken_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['content_data_created_date_format'] = "";
				if ($rawdata ['content_data_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_created_date'] );
					$rawdata ['content_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_data_modified_date_format'] = "";
				if ($rawdata ['content_data_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_modified_date'] );
					$rawdata ['content_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_data_detail_created_date_format'] = "";
				if ($rawdata ['content_data_detail_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_detail_created_date'] );
					$rawdata ['content_data_detail_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_data_detail_modified_date_format'] = "";
				if ($rawdata ['content_data_detail_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_detail_modified_date'] );
					$rawdata ['content_data_detail_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_data_url_created_date_format'] = "";
				if ($rawdata ['content_data_url_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_data_url_created_date'] );
					$rawdata ['content_data_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_data_publish_status_text'] = "";
				if ($PublishParameter->hasResult ()) {
					$rawdata ['content_data_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_data_publish_status'] );
				}
				
				$rawdata ['content_data_login_status_text'] = "";
				if ($LoginParameter->hasResult ()) {
					$rawdata ['content_data_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_data_login_status'] );
				}
				
				$rawdata ['content_data_login_status'] = ($rawdata ['content_data_login_status'] == "1" ? True : False);
				
				$QData->next ();
				ksort ( $rawdata );
				$data [$rawdata ['content_data_id']] = $rawdata;
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
	 * Get Content Data List Total
	 */
	public function getDataListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		
		$type_id = $this->getContentTypeID ();
		$user_id = $this->getContentUserID ();
		$language_id = $this->getContentLanguageID ();
		$QData = $this->getDatabase ();
		$QData->select ();
		$QData->columns ( array (
				'content_data_id',
				'content_data_publish_status',
				'content_data_publish_date',
				'content_data_login_status',
				'user_access_id',
				'content_data_ref_id',
				'content_data_taken_date',
				'content_data_taken_by',
				'content_data_location',
				'content_data_created_date',
				'content_data_modified_date',
				'content_data_created_by',
				'content_data_modified_by' 
		) );
		$QData->from ( array (
				'cd' => 'content_data' 
		) );
		$QData->join ( array (
				'cdd' => 'content_data_detail' 
		), 'cdd.content_data_id  = cd.content_data_id', array (
				'content_data_detail_id',
				'content_data_detail_title',
				'content_data_detail_information',
				'content_data_detail_created_date',
				'content_data_detail_created_by' 
		) );
		$QData->join ( array (
				'cdu' => 'content_data_url' 
		), 'cdu.content_data_id = cd.content_data_id', array (
				'content_data_url_id',
				'content_data_url_keyword',
				'content_data_url_created_date',
				'content_data_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_data_delete_status = 0',
				'cdd.content_data_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_data_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data', $search )) {
			$where = array_merge ( $where, $search ['content_data'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data_detail', $search )) {
			$where = array_merge ( $where, $search ['content_data_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_data_url', $search )) {
			$where = array_merge ( $where, $search ['content_data_url'] );
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
	 * Update Content Data
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
			if ($status) {
				if (! $this->updateDataUrl ( $rawdata ['url'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateDataLinkLabel ( $rawdata ['link_label'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateDataLinkTag ( $rawdata ['link_tag'] )) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Data
	 */
	public function createDataFactory($data) {
		$status = false;
		$rawdata = $this->generateDataData ( $data, true );
		if (count ( $rawdata ) > 0) {
			$data_id = $this->createData ( $rawdata ['data'] );
			if ($data_id != false) {
				$this->setContentDataID ( $data_id );
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
				if ($status) {
					if (! $this->createDataUrl ( $rawdata ['url'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createDataLinkLabel ( $rawdata ['link_label'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createDataLinkTag ( $rawdata ['link_tag'] )) {
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
	 * Delete Content Data
	 */
	public function deleteDataFactory($forever = false) {
		$this->deleteData ( $forever );
		$this->deleteDataDetail ( $forever );
		$this->deleteDataPermission ( $forever );
		$this->deleteDataUrl ( $forever );
		$this->deleteDataLinkLabel ( $forever );
		$this->deleteDataLinkTag ( $forever );
		return true;
	}
	
	/**
	 * Generate Content Data Data
	 */
	public function generateDataData($data, $update = false) {
		$rawdata = array ();
		$old_data = array ();
		if ($update) {
			$old_data = $this->getDataComplete ();
			$old_data = $this->dataDataArrange ( $old_data );
		}
		if (array_key_exists ( 'content_data_publish_status', $data ) && isset ( $data ['content_data_publish_status'] )) {
			$PublishParameter = new Parameter ( array (
					'key' => 'content_data_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PublishParameter->hasResult ()) {
				$publishstatus = $PublishParameter->getValueByKey ( $data ['content_data_publish_status'] );
			}
		} else {
			$data ['content_data_publish_status'] = 1;
		}
		
		if (array_key_exists ( 'content_data_login_status', $data ) && isset ( $data ['content_data_login_status'] )) {
			$LoginParameter = new Parameter ( array (
					'key' => 'content_data_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($LoginParameter->hasResult ()) {
				$loginstatus = $LoginParameter->getValueByKey ( $data ['content_data_login_status'] );
			}
		} else {
			$data ['content_data_login_status'] = 0;
		}
		
		if (! array_key_exists ( 'content_data_publish_date', $data ) || ! isset ( $data ['content_data_publish_date'] )) {
			if ($update) {
				$data ['content_data_publish_date'] = $data ['content_data_publish_date'];
			} else {
				$data ['content_data_publish_date'] = $data ['log_created_date'];
			}
		}
		
		if (! array_key_exists ( 'content_data_taken_date', $data ) || ! isset ( $data ['content_data_taken_date'] )) {
			if ($update) {
				$data ['content_data_taken_date'] = $data ['content_data_taken_date'];
			} else {
				$data ['content_data_taken_date'] = $data ['log_created_date'];
			}
		}
		
		$rawdata ['data'] = array (
				'content_data_publish_status' => $publishstatus,
				'content_data_publish_date' => $data ['content_data_publish_date'],
				'content_data_login_status' => $loginstatus,
				'timestamp' => $data ['timestamp'],
				'content_data_taken_date' => $data ['content_data_taken_date'],
				'content_data_taken_by' => $data ['content_data_taken_by'],
				'content_data_location' => $data ['content_data_location'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		
		$rawdata ['detail'] = array ();
		$AllLocale = $this->getTranslator ()->getAllLocale ();
		$urltitle = "";
		$DefaultLocale = $this->getTranslator ()->getLocaleIDbyISO ( SYSTEM_DEFAULT_LOCALE );
		if (is_array ( $AllLocale ) && count ( $AllLocale ) > 0) {
			foreach ( $AllLocale as $locale_value ) {
				$data_status = false;
				$locale_id = $locale_value ['id'];
				$locale_iso = strtolower ( $locale_value ['iso'] );
				$title = null;
				$information = null;
				if (array_key_exists ( 'content_data_detail_title_' . $locale_iso, $data )) {
					$title = $data ['content_data_detail_title_' . $locale_iso];
					$data_status = true;
				}
				if (array_key_exists ( 'content_data_detail_title_' . $locale_iso, $data )) {
					$information = $data ['content_data_detail_information_' . $locale_iso];
				}
				if ($data_status) {
					if ($DefaultLocale == $locale_id) {
						$urltitle = $title;
					}
					$rawdata ['detail'] [$locale_id] = array (
							'system_language_id' => $locale_id,
							'content_data_detail_title' => $title,
							'content_data_detail_information' => $information,
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
				'content_data_permission_visitor' => 1,
				'content_data_permission_user' => 0,
				'content_data_permission_rank' => 0,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		if (array_key_exists ( 'content_data_permission_visitor', $data )) {
			$PermissionVisitorParameter = new Parameter ( array (
					'key' => 'content_data_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PermissionVisitorParameter->hasResult ()) {
				$permissionvisitorstatus = $PermissionVisitorParameter->getValueByKey ( $data ['content_data_permission_visitor'] );
			}
			$rawdata ['permission'] [0] = array (
					'content_data_permission_visitor' => $permissionvisitorstatus,
					'content_data_permission_user' => 0,
					'content_data_permission_rank' => 0,
					'timestamp' => $data ['timestamp'],
					'log_created_by' => $data ['log_created_by'],
					'log_created_date' => $data ['log_created_date'],
					'log_modified_by' => $data ['log_modified_by'],
					'log_modified_date' => $data ['log_modified_date'] 
			);
			if ($permissionvisitorstatus == 0) {
				if (array_key_exists ( 'content_data_permission_user', $data ) && count ( $data ['content_data_permission_user'] ) > 0) {
					foreach ( $data ['content_data_permission_user'] as $user_value ) {
						$user_value = $this->Decrypt ( $user_value );
						if ($user_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_data_permission_visitor' => 0,
									'content_data_permission_user' => $user_value,
									'content_data_permission_rank' => 0,
									'timestamp' => $data ['timestamp'],
									'log_created_by' => $data ['log_created_by'],
									'log_created_date' => $data ['log_created_date'],
									'log_modified_by' => $data ['log_modified_by'],
									'log_modified_date' => $data ['log_modified_date'] 
							);
						}
					}
				}
				if (array_key_exists ( 'content_data_permission_rank', $data ) && count ( $data ['content_data_permission_rank'] ) > 0) {
					foreach ( $data ['content_data_permission_rank'] as $rank_value ) {
						$rank_value = $this->Decrypt ( $rank_value );
						if ($rank_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_data_permission_visitor' => 0,
									'content_data_permission_user' => 0,
									'content_data_permission_rank' => $rank_value,
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
		
		$ToUnderscore = new ToUnderscore ( ' ' );
		$urltitle = (! empty ( $urltitle ) ? $urltitle : $data ['timestamp']);
		$data ['content_data_url_keyword'] = (array_key_exists ( 'content_data_url_keyword', $data ) ? $data ['content_data_url_keyword'] : $urltitle);
		$data ['content_data_url_keyword'] = strtoupper ( $data ['content_data_url_keyword'] );
		$data ['content_data_url_keyword'] = trim ( $data ['content_data_url_keyword'] );
		$urltitle = $ToUnderscore->filter ( $data ['content_data_url_keyword'] );
		$rawdata ['url'] = array (
				'content_data_url_keyword' => $urltitle,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		
		$rawdata ['link_label'] = array ();
		if (array_key_exists ( 'content_data_label', $data )) {
			foreach ( $data ['content_data_label'] as $value ) {
				$rawdata ['link_label'] [] = array (
						'content_data_label' => $value,
						'timestamp' => $data ['timestamp'],
						'log_created_by' => $data ['log_created_by'],
						'log_created_date' => $data ['log_created_date'],
						'log_modified_by' => $data ['log_modified_by'],
						'log_modified_date' => $data ['log_modified_date'] 
				);
			}
		}
		$rawdata ['link_tag'] = array ();
		if (array_key_exists ( 'content_data_tag', $data )) {
			foreach ( $data ['content_data_tag'] as $value ) {
				$rawdata ['link_tag'] [] = array (
						'content_data_tag' => $value,
						'timestamp' => $data ['timestamp'],
						'log_created_by' => $data ['log_created_by'],
						'log_created_date' => $data ['log_created_date'],
						'log_modified_by' => $data ['log_modified_by'],
						'log_modified_date' => $data ['log_modified_date'] 
				);
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
			$rawdata = array_merge ( $rawdata, $data ['url'] );
			$rawdata ['content_data_permission_visitor_text'] = "";
			$rawdata ['content_data_permission_rank'] = "";
			$rawdata ['content_data_permission_user'] = "";
			$rawdata ['content_data_permission_rank_options'] = array ();
			$rawdata ['content_data_permission_user_options'] = array ();
			if (is_array ( $data ['permission'] ) && count ( $data ['permission'] ) > 0) {
				$permission_rank_status = false;
				$permission_user_status = false;
				$permission_visitor_status = false;
				$content_data_permission_rank = "";
				$content_data_permission_user = "";
				$content_data_permission_rank_options = array ();
				$content_data_permission_user_options = array ();
				$content_data_permission_visitor_text = "";
				$content_data_permission_visitor = "";
				foreach ( $data ['permission'] as $permissions_key => $permissions_value ) {
					if (array_key_exists ( 'content_data_permission_rank', $permissions_value ) && ! empty ( $permissions_value ['content_data_permission_rank'] )) {
						$content_data_permission_rank .= $permissions_value ['content_data_permission_rank'] . ", ";
						$content_data_permission_rank_options [$permissions_value ['content_data_permission_rank_options']] = $permissions_value ['content_data_permission_rank'];
						$permission_rank_status = true;
					}
					if (array_key_exists ( 'content_data_permission_user', $permissions_value ) && ! empty ( $permissions_value ['content_data_permission_user'] )) {
						$content_data_permission_user .= $permissions_value ['content_data_permission_user'] . ", ";
						$content_data_permission_user_options [$permissions_value ['content_data_permission_user_options']] = $permissions_value ['content_data_permission_user'];
						$permission_user_status = true;
					}
					if (array_key_exists ( 'content_data_permission_visitor', $permissions_value )) {
						if ($permissions_value ['content_data_permission_visitor'] == 1) {
							$content_data_permission_visitor_text = $permissions_value ['content_data_permission_visitor_text'];
							$content_data_permission_visitor = $permissions_value ['content_data_permission_visitor'];
							$permission_visitor_status = true;
						}
						if (! $permission_visitor_status) {
							$content_data_permission_visitor_text = $permissions_value ['content_data_permission_visitor_text'];
							$content_data_permission_visitor = $permissions_value ['content_data_permission_visitor'];
						}
					}
				}
				$content_data_permission_rank = trim ( $content_data_permission_rank );
				if (substr ( $content_data_permission_rank, strlen ( $content_data_permission_rank ) - 1 ) == ",") {
					$content_data_permission_rank = substr ( $content_data_permission_rank, 0, (strlen ( $content_data_permission_rank ) - 1) );
				}
				$content_data_permission_rank = trim ( $content_data_permission_rank );
				if (substr ( $content_data_permission_rank, strlen ( $content_data_permission_rank ) - 1 ) == ",") {
					$content_data_permission_rank = substr ( $content_data_permission_rank, 0, (strlen ( $content_data_permission_rank ) - 1) );
				}
				$rawdata ['content_data_permission_visitor_text'] = $content_data_permission_visitor_text;
				$rawdata ['content_data_permission_visitor'] = $content_data_permission_visitor;
				if (! $permission_visitor_status) {
					$rawdata ['content_data_permission_rank'] = $content_data_permission_rank;
					$rawdata ['content_data_permission_user'] = $content_data_permission_user;
					$rawdata ['content_data_permission_rank_options'] = $content_data_permission_rank_options;
					$rawdata ['content_data_permission_user_options'] = $content_data_permission_user_options;
				} else {
					$rawdata ['content_data_permission_rank'] = 'N/A';
					$rawdata ['content_data_permission_user'] = 'N/A';
					$rawdata ['content_data_permission_rank_options'] = 'N/A';
					$rawdata ['content_data_permission_user_options'] = 'N/A';
				}
			}
			if (is_array ( $data ['link_label'] ) && count ( $data ['link_label'] ) > 0) {
				$content_data_link_label = null;
				$content_data_link_label_options = array ();
				foreach ( $data ['link_label'] as $label_value ) {
					$content_data_link_label .= $label_value ['content_label_detail_title'] . ", ";
					$content_data_link_label_options [] = $label_value ['modify_value'];
				}
				$content_data_link_label = trim ( $content_data_link_label );
				if (substr ( $content_data_link_label, strlen ( $content_data_link_label ) - 1 ) == ",") {
					$content_data_link_label = substr ( $content_data_link_label, 0, (strlen ( $content_data_link_label ) - 1) );
				}
				$rawdata ['content_data_label'] = $content_data_link_label;
				$rawdata ['content_data_label_options'] = $content_data_link_label_options;
			}
			if (is_array ( $data ['link_tag'] ) && count ( $data ['link_tag'] ) > 0) {
				$content_data_link_tag = null;
				$content_data_link_tag_options = array ();
				foreach ( $data ['link_tag'] as $tag_value ) {
					$content_data_link_tag .= $tag_value ['content_tag_detail_title'] . ", ";
					$content_data_link_tag_options [] = $tag_value ['modify_value'];
				}
				$content_data_link_tag = trim ( $content_data_link_tag );
				if (substr ( $content_data_link_tag, strlen ( $content_data_link_tag ) - 1 ) == ",") {
					$content_data_link_tag = substr ( $content_data_link_tag, 0, (strlen ( $content_data_link_tag ) - 1) );
				}
				$rawdata ['content_data_tag'] = $content_data_link_tag;
				$rawdata ['content_data_tag_options'] = $content_data_link_tag_options;
			}
			ksort ( $rawdata );
		}
		return $rawdata;
	}
}

<?php

namespace Techfever\Content;

use Techfever\Exception;
use Techfever\Content\Label\Label as GLabel;
use Techfever\Parameter\Parameter;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class Label extends GLabel {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'label_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Label All
	 *     
	 */
	private $content_label_all = null;
	
	/**
	 * Initial Content Label
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
	 * Verify Content Label Fixed
	 *
	 * @return array()
	 */
	public function isLabelFixed() {
		$status = false;
		$label_id = $this->getContentLabelID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		$rawdata = array ();
		$QLabel = $this->getDatabase ();
		$QLabel->select ();
		$QLabel->columns ( array (
				'content_label_fixed_status' 
		) );
		$QLabel->from ( array (
				'cd' => 'content_label' 
		) );
		$where = array (
				'cd.content_label_id = "' . $label_id . '"',
				'cd.content_type_id = "' . $type_id . '"',
				'cd.content_label_delete_status = "0"' 
		);
		if (! is_null ( $user_id ) && $user_id > 0) {
			$where [] = 'cd.user_access_id = "' . $user_id . '"';
		}
		$QLabel->where ( $where );
		$QLabel->limit ( 1 );
		$QLabel->execute ();
		if ($QLabel->hasResult ()) {
			while ( $QLabel->valid () ) {
				$rawdata = $QLabel->current ();
				if ($rawdata ['content_label_fixed_status'] == "1") {
					$status = true;
				}
				$QLabel->next ();
			}
		}
		return $status;
	}
	
	/**
	 * Search Content Label
	 *
	 * @return array()
	 */
	public function searchLabel($search = null) {
		$id = 0;
		if (! empty ( $search )) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QLabel = $this->getDatabase ();
			$QLabel->select ();
			$QLabel->columns ( array (
					'content_label_id' 
			) );
			$QLabel->from ( array (
					'cd' => 'content_label' 
			) );
			$where = array (
					'cd.content_label_ref_id = "' . $search . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_label_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QLabel->where ( $where );
			$QLabel->limit ( 1 );
			$QLabel->execute ();
			if ($QLabel->hasResult ()) {
				while ( $QLabel->valid () ) {
					$rawdata = $QLabel->current ();
					$id = $rawdata ['content_label_id'];
					$QLabel->next ();
				}
			}
		}
		return ( int ) $id;
	}
	
	/**
	 * Get Content Label
	 *
	 * @return string
	 */
	public function getLabelCode($id = null) {
		$code = "";
		if (! empty ( $id ) && $id > 0) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QLabel = $this->getDatabase ();
			$QLabel->select ();
			$QLabel->columns ( array (
					'content_label_ref_id' 
			) );
			$QLabel->from ( array (
					'cd' => 'content_label' 
			) );
			$where = array (
					'cd.content_label_id = "' . $id . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_label_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QLabel->where ( $where );
			$QLabel->limit ( 1 );
			$QLabel->execute ();
			if ($QLabel->hasResult ()) {
				while ( $QLabel->valid () ) {
					$rawdata = $QLabel->current ();
					$code = $rawdata ['content_label_ref_id'];
					$QLabel->next ();
				}
			}
		}
		return ( string ) $code;
	}
	
	/**
	 * Get Content Label Complete
	 *
	 * @return array()
	 */
	public function getLabelComplete() {
		if (! is_array ( $this->content_label_all ) || count ( $this->content_label_all ) < 1) {
			$rawdata = array ();
			$label = $this->getLabel ();
			if (is_array ( $label ) && count ( $label ) > 0) {
				$detail = $this->getLabelDetail ();
				$permission = $this->getLabelPermission ();
				$url = $this->getLabelUrl ();
				$link = $this->getLabelLink ();
				
				$rawdata ['label'] = $label;
				$rawdata ['detail'] = $detail;
				$rawdata ['permission'] = $permission;
				$rawdata ['url'] = $url;
				$rawdata ['link'] = $link;
			}
			$this->content_label_all = $rawdata;
		}
		return $this->content_label_all;
	}
	
	/**
	 * Get Content Label List
	 */
	public function getLabelListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		$PublishParameter = new Parameter ( array (
				'key' => 'content_label_publish_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$LoginParameter = new Parameter ( array (
				'key' => 'content_label_login_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$user_id = $this->getContentUserID ();
		$type_id = $this->getContentTypeID ();
		$language_id = $this->getContentLanguageID ();
		$QLabel = $this->getDatabase ();
		$QLabel->select ();
		$QLabel->columns ( array (
				'content_label_id',
				'content_label_publish_status',
				'content_label_publish_date',
				'content_label_login_status',
				'user_access_id',
				'content_label_ref_id',
				'content_label_created_date',
				'content_label_modified_date',
				'content_label_created_by',
				'content_label_modified_by' 
		) );
		$QLabel->from ( array (
				'cd' => 'content_label' 
		) );
		$QLabel->join ( array (
				'cdd' => 'content_label_detail' 
		), 'cdd.content_label_id  = cd.content_label_id', array (
				'content_label_detail_id',
				'content_label_detail_title',
				'content_label_detail_information',
				'content_label_detail_created_date',
				'content_label_detail_created_by',
				'content_label_detail_modified_date',
				'content_label_detail_modified_by' 
		) );
		$QLabel->join ( array (
				'cdu' => 'content_label_url' 
		), 'cdu.content_label_id = cd.content_label_id', array (
				'content_label_url_id',
				'content_label_url_keyword',
				'content_label_url_created_date',
				'content_label_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_label_delete_status = 0',
				'cdd.content_label_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_label_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label', $search )) {
			$where = array_merge ( $where, $search ['content_label'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label_detail', $search )) {
			$where = array_merge ( $where, $search ['content_label_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label_url', $search )) {
			$where = array_merge ( $where, $search ['content_label_url'] );
		}
		$QLabel->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'cd.content_label_created_date' 
			);
		}
		$QLabel->order ( $order );
		if (isset ( $perpage )) {
			$QLabel->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QLabel->offset ( ( int ) $index );
		}
		$QLabel->execute ();
		if ($QLabel->hasResult ()) {
			$data = array ();
			$count = 1;
			while ( $QLabel->valid () ) {
				$rawdata = $QLabel->current ();
				$rawdata ['no'] = $count;
				
				$rawdata ['content_label_code'] = $rawdata ['content_label_ref_id'];
				$cryptID = $this->Encrypt ( $rawdata ['content_label_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['content_label_id']);
				
				$rawdata ['content_label_publish_date_format'] = "";
				if ($rawdata ['content_label_publish_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_publish_date'] );
					$rawdata ['content_label_publish_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['content_label_created_date_format'] = "";
				if ($rawdata ['content_label_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_created_date'] );
					$rawdata ['content_label_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_label_modified_date_format'] = "";
				if ($rawdata ['content_label_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_modified_date'] );
					$rawdata ['content_label_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_label_detail_created_date_format'] = "";
				if ($rawdata ['content_label_detail_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_detail_created_date'] );
					$rawdata ['content_label_detail_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_label_detail_modified_date_format'] = "";
				if ($rawdata ['content_label_detail_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_detail_modified_date'] );
					$rawdata ['content_label_detail_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_label_url_created_date_format'] = "";
				if ($rawdata ['content_label_url_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_label_url_created_date'] );
					$rawdata ['content_label_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_label_publish_status_text'] = "";
				if ($PublishParameter->hasResult ()) {
					$rawdata ['content_label_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_label_publish_status'] );
				}
				
				$rawdata ['content_label_login_status_text'] = "";
				if ($LoginParameter->hasResult ()) {
					$rawdata ['content_label_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_label_login_status'] );
				}
				
				$rawdata ['content_label_login_status'] = ($rawdata ['content_label_login_status'] == "1" ? True : False);
				
				$QLabel->next ();
				ksort ( $rawdata );
				$data [$rawdata ['content_label_id']] = $rawdata;
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
	 * Get Content Label List Total
	 */
	public function getLabelListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		
		$type_id = $this->getContentTypeID ();
		$user_id = $this->getContentUserID ();
		$language_id = $this->getContentLanguageID ();
		$QLabel = $this->getDatabase ();
		$QLabel->select ();
		$QLabel->columns ( array (
				'content_label_id',
				'content_label_publish_status',
				'content_label_publish_date',
				'content_label_login_status',
				'user_access_id',
				'content_label_ref_id',
				'content_label_created_date',
				'content_label_modified_date',
				'content_label_created_by',
				'content_label_modified_by' 
		) );
		$QLabel->from ( array (
				'cd' => 'content_label' 
		) );
		$QLabel->join ( array (
				'cdd' => 'content_label_detail' 
		), 'cdd.content_label_id  = cd.content_label_id', array (
				'content_label_detail_id',
				'content_label_detail_title',
				'content_label_detail_information',
				'content_label_detail_created_date',
				'content_label_detail_created_by' 
		) );
		$QLabel->join ( array (
				'cdu' => 'content_label_url' 
		), 'cdu.content_label_id = cd.content_label_id', array (
				'content_label_url_id',
				'content_label_url_keyword',
				'content_label_url_created_date',
				'content_label_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_label_delete_status = 0',
				'cdd.content_label_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_label_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label', $search )) {
			$where = array_merge ( $where, $search ['content_label'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label_detail', $search )) {
			$where = array_merge ( $where, $search ['content_label_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_label_url', $search )) {
			$where = array_merge ( $where, $search ['content_label_url'] );
		}
		$QLabel->where ( $where );
		$QLabel->execute ();
		$count = 0;
		if ($QLabel->hasResult ()) {
			$count = $QLabel->count ();
		}
		return $count;
	}
	
	/**
	 * Update Content Label
	 */
	public function updateLabelFactory($data) {
		$status = false;
		$rawdata = $this->generateLabelData ( $data, true );
		if (count ( $rawdata ) > 0) {
			$status = true;
			if (! $this->updateLabel ( $rawdata ['label'] )) {
				$status = false;
			}
			if ($status) {
				if (! $this->updateLabelDetail ( $rawdata ['detail'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateLabelPermission ( $rawdata ['permission'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateLabelUrl ( $rawdata ['url'] )) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Label
	 */
	public function createLabelFactory($data) {
		$status = false;
		$rawdata = $this->generateLabelData ( $data );
		if (count ( $rawdata ) > 0) {
			$label_id = $this->createLabel ( $rawdata ['label'] );
			if ($label_id != false) {
				$this->setContentLabelID ( $label_id );
				$status = true;
				if ($status) {
					if (! $this->createLabelDetail ( $rawdata ['detail'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createLabelPermission ( $rawdata ['permission'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createLabelUrl ( $rawdata ['url'] )) {
						$status = false;
					}
				}
			}
		}
		if ($status) {
			return $label_id;
		} else {
			$this->deleteLabelFactory ( true );
		}
		return $status;
	}
	
	/**
	 * Delete Content Label
	 */
	public function deleteLabelFactory($forever = false) {
		$this->deleteLabel ( $forever );
		$this->deleteLabelDetail ( $forever );
		$this->deleteLabelPermission ( $forever );
		$this->deleteLabelUrl ( $forever );
		$this->deleteLabelLink ( $forever );
		return true;
	}
	
	/**
	 * Generate Content Label Data
	 */
	public function generateLabelData($data, $update = false) {
		$rawdata = array ();
		$old_data = array ();
		if ($update) {
			$old_data = $this->getLabelComplete ();
			$old_data = $this->dataLabelArrange ( $old_data );
		}
		if (array_key_exists ( 'content_label_publish_status', $data ) && isset ( $data ['content_label_publish_status'] )) {
			$PublishParameter = new Parameter ( array (
					'key' => 'content_label_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PublishParameter->hasResult ()) {
				$publishstatus = $PublishParameter->getValueByKey ( $data ['content_label_publish_status'] );
			}
		} else {
			$data ['content_label_publish_status'] = 1;
		}
		
		if (array_key_exists ( 'content_label_login_status', $data ) && isset ( $data ['content_label_login_status'] )) {
			$LoginParameter = new Parameter ( array (
					'key' => 'content_label_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($LoginParameter->hasResult ()) {
				$loginstatus = $LoginParameter->getValueByKey ( $data ['content_label_login_status'] );
			}
		} else {
			$data ['content_label_login_status'] = 0;
		}
		
		if (! array_key_exists ( 'content_label_publish_date', $data ) || ! isset ( $data ['content_label_publish_date'] )) {
			if ($update) {
				$data ['content_label_publish_date'] = $data ['content_label_publish_date'];
			} else {
				$data ['content_label_publish_date'] = $data ['log_created_date'];
			}
		}
		
		$rawdata ['label'] = array (
				'content_label_publish_status' => $publishstatus,
				'content_label_publish_date' => $data ['content_label_publish_date'],
				'content_label_login_status' => $loginstatus,
				'timestamp' => $data ['timestamp'],
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
				if (array_key_exists ( 'content_label_detail_title_' . $locale_iso, $data )) {
					$title = $data ['content_label_detail_title_' . $locale_iso];
					$data_status = true;
				}
				if (array_key_exists ( 'content_label_detail_title_' . $locale_iso, $data )) {
					$information = $data ['content_label_detail_information_' . $locale_iso];
				}
				if ($data_status) {
					if ($DefaultLocale == $locale_id) {
						$urltitle = $title;
					}
					$rawdata ['detail'] [$locale_id] = array (
							'system_language_id' => $locale_id,
							'content_label_detail_title' => $title,
							'content_label_detail_information' => $information,
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
				'content_label_permission_visitor' => 1,
				'content_label_permission_user' => 0,
				'content_label_permission_rank' => 0,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		if (array_key_exists ( 'content_label_permission_visitor', $data )) {
			$PermissionVisitorParameter = new Parameter ( array (
					'key' => 'content_label_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PermissionVisitorParameter->hasResult ()) {
				$permissionvisitorstatus = $PermissionVisitorParameter->getValueByKey ( $data ['content_label_permission_visitor'] );
			}
			$rawdata ['permission'] [0] = array (
					'content_label_permission_visitor' => $permissionvisitorstatus,
					'content_label_permission_user' => 0,
					'content_label_permission_rank' => 0,
					'timestamp' => $data ['timestamp'],
					'log_created_by' => $data ['log_created_by'],
					'log_created_date' => $data ['log_created_date'],
					'log_modified_by' => $data ['log_modified_by'],
					'log_modified_date' => $data ['log_modified_date'] 
			);
			if ($permissionvisitorstatus == 0) {
				if (array_key_exists ( 'content_label_permission_user', $data ) && count ( $data ['content_label_permission_user'] ) > 0) {
					foreach ( $data ['content_label_permission_user'] as $user_value ) {
						$user_value = $this->Decrypt ( $user_value );
						if ($user_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_label_permission_visitor' => 0,
									'content_label_permission_user' => $user_value,
									'content_label_permission_rank' => 0,
									'timestamp' => $data ['timestamp'],
									'log_created_by' => $data ['log_created_by'],
									'log_created_date' => $data ['log_created_date'],
									'log_modified_by' => $data ['log_modified_by'],
									'log_modified_date' => $data ['log_modified_date'] 
							);
						}
					}
				}
				if (array_key_exists ( 'content_label_permission_rank', $data ) && count ( $data ['content_label_permission_rank'] ) > 0) {
					foreach ( $data ['content_label_permission_rank'] as $rank_value ) {
						$rank_value = $this->Decrypt ( $rank_value );
						if ($rank_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_label_permission_visitor' => 0,
									'content_label_permission_user' => 0,
									'content_label_permission_rank' => $rank_value,
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
		$data ['content_label_url_keyword'] = (array_key_exists ( 'content_label_url_keyword', $data ) ? $data ['content_label_url_keyword'] : $urltitle);
		$data ['content_label_url_keyword'] = strtoupper ( $data ['content_label_url_keyword'] );
		$data ['content_label_url_keyword'] = trim ( $data ['content_label_url_keyword'] );
		$urltitle = $ToUnderscore->filter ( $data ['content_label_url_keyword'] );
		$rawdata ['url'] = array (
				'content_label_url_keyword' => $urltitle,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		
		unset ( $data );
		return $rawdata;
	}
	
	/**
	 * Data LabelArrange
	 *
	 * @return Array
	 */
	public function dataLabelArrange($data = null) {
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
			$rawdata = array_merge ( $rawdata, $data ['label'] );
			$rawdata = array_merge ( $rawdata, $data ['url'] );
			$rawdata ['content_label_permission_visitor_text'] = "";
			$rawdata ['content_label_permission_rank'] = "";
			$rawdata ['content_label_permission_user'] = "";
			$rawdata ['content_label_permission_rank_options'] = array ();
			$rawdata ['content_label_permission_user_options'] = array ();
			if (is_array ( $data ['permission'] ) && count ( $data ['permission'] ) > 0) {
				$permission_rank_status = false;
				$permission_user_status = false;
				$permission_visitor_status = false;
				$content_label_permission_rank = "";
				$content_label_permission_user = "";
				$content_label_permission_rank_options = array ();
				$content_label_permission_user_options = array ();
				$content_label_permission_visitor_text = "";
				$content_label_permission_visitor = "";
				foreach ( $data ['permission'] as $permissions_key => $permissions_value ) {
					if (array_key_exists ( 'content_label_permission_rank', $permissions_value ) && ! empty ( $permissions_value ['content_label_permission_rank'] )) {
						$content_label_permission_rank .= $permissions_value ['content_label_permission_rank'] . ", ";
						$content_label_permission_rank_options [$permissions_value ['content_label_permission_rank_options']] = $permissions_value ['content_label_permission_rank'];
						$permission_rank_status = true;
					}
					if (array_key_exists ( 'content_label_permission_user', $permissions_value ) && ! empty ( $permissions_value ['content_label_permission_user'] )) {
						$content_label_permission_user .= $permissions_value ['content_label_permission_user'] . ", ";
						$content_label_permission_user_options [$permissions_value ['content_label_permission_user_options']] = $permissions_value ['content_label_permission_user'];
						$permission_user_status = true;
					}
					if (array_key_exists ( 'content_label_permission_visitor', $permissions_value )) {
						if ($permissions_value ['content_label_permission_visitor'] == 1) {
							$content_label_permission_visitor_text = $permissions_value ['content_label_permission_visitor_text'];
							$content_label_permission_visitor = $permissions_value ['content_label_permission_visitor'];
							$permission_visitor_status = true;
						}
						if (! $permission_visitor_status) {
							$content_label_permission_visitor_text = $permissions_value ['content_label_permission_visitor_text'];
							$content_label_permission_visitor = $permissions_value ['content_label_permission_visitor'];
						}
					}
				}
				$content_label_permission_rank = trim ( $content_label_permission_rank );
				if (substr ( $content_label_permission_rank, strlen ( $content_label_permission_rank ) - 1 ) == ",") {
					$content_label_permission_rank = substr ( $content_label_permission_rank, 0, (strlen ( $content_label_permission_rank ) - 1) );
				}
				$content_label_permission_user = trim ( $content_label_permission_user );
				if (substr ( $content_label_permission_user, strlen ( $content_label_permission_user ) - 1 ) == ",") {
					$content_label_permission_user = substr ( $content_label_permission_user, 0, (strlen ( $content_label_permission_user ) - 1) );
				}
				$rawdata ['content_label_permission_visitor_text'] = $content_label_permission_visitor_text;
				$rawdata ['content_label_permission_visitor'] = $content_label_permission_visitor;
				if (! $permission_visitor_status) {
					$rawdata ['content_label_permission_rank'] = $content_label_permission_rank;
					$rawdata ['content_label_permission_user'] = $content_label_permission_user;
					$rawdata ['content_label_permission_rank_options'] = $content_label_permission_rank_options;
					$rawdata ['content_label_permission_user_options'] = $content_label_permission_user_options;
				} else {
					$rawdata ['content_label_permission_rank'] = 'N/A';
					$rawdata ['content_label_permission_user'] = 'N/A';
					$rawdata ['content_label_permission_rank_options'] = 'N/A';
					$rawdata ['content_label_permission_user_options'] = 'N/A';
				}
			}
			ksort ( $rawdata );
		}
		return $rawdata;
	}
}

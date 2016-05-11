<?php

namespace Techfever\Content;

use Techfever\Exception;
use Techfever\Content\Tag\Tag as GTag;
use Techfever\Parameter\Parameter;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class Tag extends GTag {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'tag_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Tag All
	 *     
	 */
	private $content_tag_all = null;
	
	/**
	 * Initial Content Tag
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
	 * Verify Content Tag Fixed
	 *
	 * @return array()
	 */
	public function isTagFixed() {
		$status = false;
		$tag_id = $this->getContentTagID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		$rawdata = array ();
		$QTag = $this->getDatabase ();
		$QTag->select ();
		$QTag->columns ( array (
				'content_tag_fixed_status' 
		) );
		$QTag->from ( array (
				'cd' => 'content_tag' 
		) );
		$where = array (
				'cd.content_tag_id = "' . $tag_id . '"',
				'cd.content_type_id = "' . $type_id . '"',
				'cd.content_tag_delete_status = "0"' 
		);
		if (! is_null ( $user_id ) && $user_id > 0) {
			$where [] = 'cd.user_access_id = "' . $user_id . '"';
		}
		$QTag->where ( $where );
		$QTag->limit ( 1 );
		$QTag->execute ();
		if ($QTag->hasResult ()) {
			while ( $QTag->valid () ) {
				$rawdata = $QTag->current ();
				if ($rawdata ['content_tag_fixed_status'] == "1") {
					$status = true;
				}
				$QTag->next ();
			}
		}
		return $status;
	}
	
	/**
	 * Search Content Tag
	 *
	 * @return array()
	 */
	public function searchTag($search = null) {
		$id = 0;
		if (! empty ( $search )) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QTag = $this->getDatabase ();
			$QTag->select ();
			$QTag->columns ( array (
					'content_tag_id' 
			) );
			$QTag->from ( array (
					'cd' => 'content_tag' 
			) );
			$where = array (
					'cd.content_tag_ref_id = "' . $search . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_tag_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QTag->where ( $where );
			$QTag->limit ( 1 );
			$QTag->execute ();
			if ($QTag->hasResult ()) {
				while ( $QTag->valid () ) {
					$rawdata = $QTag->current ();
					$id = $rawdata ['content_tag_id'];
					$QTag->next ();
				}
			}
		}
		return ( int ) $id;
	}
	
	/**
	 * Get Content Tag
	 *
	 * @return string
	 */
	public function getTagCode($id = null) {
		$code = "";
		if (! empty ( $id ) && $id > 0) {
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$QTag = $this->getDatabase ();
			$QTag->select ();
			$QTag->columns ( array (
					'content_tag_ref_id' 
			) );
			$QTag->from ( array (
					'cd' => 'content_tag' 
			) );
			$where = array (
					'cd.content_tag_id = "' . $id . '"',
					'cd.content_type_id = "' . $type_id . '"',
					'cd.content_tag_delete_status = "0"' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QTag->where ( $where );
			$QTag->limit ( 1 );
			$QTag->execute ();
			if ($QTag->hasResult ()) {
				while ( $QTag->valid () ) {
					$rawdata = $QTag->current ();
					$code = $rawdata ['content_tag_ref_id'];
					$QTag->next ();
				}
			}
		}
		return ( string ) $code;
	}
	
	/**
	 * Get Content Tag Complete
	 *
	 * @return array()
	 */
	public function getTagComplete() {
		if (! is_array ( $this->content_tag_all ) || count ( $this->content_tag_all ) < 1) {
			$rawdata = array ();
			$tag = $this->getTag ();
			if (is_array ( $tag ) && count ( $tag ) > 0) {
				$detail = $this->getTagDetail ();
				$permission = $this->getTagPermission ();
				$url = $this->getTagUrl ();
				$link = $this->getTagLink ();
				
				$rawdata ['tag'] = $tag;
				$rawdata ['detail'] = $detail;
				$rawdata ['permission'] = $permission;
				$rawdata ['url'] = $url;
				$rawdata ['link'] = $link;
			}
			$this->content_tag_all = $rawdata;
		}
		return $this->content_tag_all;
	}
	
	/**
	 * Get Content Tag List
	 */
	public function getTagListing($search = null, $order = null, $index = 0, $perpage = 10, $encryted_id = false) {
		$orderstr = null;
		$data = array ();
		$PublishParameter = new Parameter ( array (
				'key' => 'content_tag_publish_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$LoginParameter = new Parameter ( array (
				'key' => 'content_tag_login_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$user_id = $this->getContentUserID ();
		$type_id = $this->getContentTypeID ();
		$language_id = $this->getContentLanguageID ();
		$QTag = $this->getDatabase ();
		$QTag->select ();
		$QTag->columns ( array (
				'content_tag_id',
				'content_tag_publish_status',
				'content_tag_publish_date',
				'content_tag_login_status',
				'user_access_id',
				'content_tag_ref_id',
				'content_tag_created_date',
				'content_tag_modified_date',
				'content_tag_created_by',
				'content_tag_modified_by' 
		) );
		$QTag->from ( array (
				'cd' => 'content_tag' 
		) );
		$QTag->join ( array (
				'cdd' => 'content_tag_detail' 
		), 'cdd.content_tag_id  = cd.content_tag_id', array (
				'content_tag_detail_id',
				'content_tag_detail_title',
				'content_tag_detail_created_date',
				'content_tag_detail_created_by',
				'content_tag_detail_modified_date',
				'content_tag_detail_modified_by' 
		) );
		$QTag->join ( array (
				'cdu' => 'content_tag_url' 
		), 'cdu.content_tag_id = cd.content_tag_id', array (
				'content_tag_url_id',
				'content_tag_url_keyword',
				'content_tag_url_created_date',
				'content_tag_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_tag_delete_status = 0',
				'cdd.content_tag_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_tag_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag', $search )) {
			$where = array_merge ( $where, $search ['content_tag'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag_detail', $search )) {
			$where = array_merge ( $where, $search ['content_tag_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag_url', $search )) {
			$where = array_merge ( $where, $search ['content_tag_url'] );
		}
		$QTag->where ( $where );
		if (empty ( $order )) {
			$order = array (
					'cd.content_tag_created_date' 
			);
		}
		$QTag->order ( $order );
		if (isset ( $perpage )) {
			$QTag->limit ( ( int ) $perpage );
		}
		if (isset ( $index )) {
			$QTag->offset ( ( int ) $index );
		}
		$QTag->execute ();
		if ($QTag->hasResult ()) {
			$data = array ();
			$count = 1;
			while ( $QTag->valid () ) {
				$rawdata = $QTag->current ();
				$rawdata ['no'] = $count;
				
				$rawdata ['content_tag_code'] = $rawdata ['content_tag_ref_id'];
				$cryptID = $this->Encrypt ( $rawdata ['content_tag_id'] );
				$rawdata ['id'] = ($encryted_id ? $cryptID : $rawdata ['content_tag_id']);
				
				$rawdata ['content_tag_publish_date_format'] = "";
				if ($rawdata ['content_tag_publish_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_publish_date'] );
					$rawdata ['content_tag_publish_date_format'] = $datetime->format ( 'd-F-Y' );
				}
				
				$rawdata ['content_tag_created_date_format'] = "";
				if ($rawdata ['content_tag_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_created_date'] );
					$rawdata ['content_tag_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_tag_modified_date_format'] = "";
				if ($rawdata ['content_tag_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_modified_date'] );
					$rawdata ['content_tag_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_tag_detail_created_date_format'] = "";
				if ($rawdata ['content_tag_detail_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_detail_created_date'] );
					$rawdata ['content_tag_detail_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_tag_detail_modified_date_format'] = "";
				if ($rawdata ['content_tag_detail_modified_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_detail_modified_date'] );
					$rawdata ['content_tag_detail_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_tag_url_created_date_format'] = "";
				if ($rawdata ['content_tag_url_created_date'] !== "0000-00-00 00:00:00") {
					$datetime = new \DateTime ( $rawdata ['content_tag_url_created_date'] );
					$rawdata ['content_tag_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
				}
				
				$rawdata ['content_tag_publish_status_text'] = "";
				if ($PublishParameter->hasResult ()) {
					$rawdata ['content_tag_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_tag_publish_status'] );
				}
				
				$rawdata ['content_tag_login_status_text'] = "";
				if ($LoginParameter->hasResult ()) {
					$rawdata ['content_tag_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_tag_login_status'] );
				}
				
				$rawdata ['content_tag_login_status'] = ($rawdata ['content_tag_login_status'] == "1" ? True : False);
				
				$QTag->next ();
				ksort ( $rawdata );
				$data [$rawdata ['content_tag_id']] = $rawdata;
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
	 * Get Content Tag List Total
	 */
	public function getTagListingTotal($search = null, $encryted_id = false) {
		$orderstr = null;
		
		$type_id = $this->getContentTypeID ();
		$user_id = $this->getContentUserID ();
		$language_id = $this->getContentLanguageID ();
		$QTag = $this->getDatabase ();
		$QTag->select ();
		$QTag->columns ( array (
				'content_tag_id',
				'content_tag_publish_status',
				'content_tag_publish_date',
				'content_tag_login_status',
				'user_access_id',
				'content_tag_ref_id',
				'content_tag_created_date',
				'content_tag_modified_date',
				'content_tag_created_by',
				'content_tag_modified_by' 
		) );
		$QTag->from ( array (
				'cd' => 'content_tag' 
		) );
		$QTag->join ( array (
				'cdd' => 'content_tag_detail' 
		), 'cdd.content_tag_id  = cd.content_tag_id', array (
				'content_tag_detail_id',
				'content_tag_detail_title',
				'content_tag_detail_created_date',
				'content_tag_detail_created_by' 
		) );
		$QTag->join ( array (
				'cdu' => 'content_tag_url' 
		), 'cdu.content_tag_id = cd.content_tag_id', array (
				'content_tag_url_id',
				'content_tag_url_keyword',
				'content_tag_url_created_date',
				'content_tag_url_created_by' 
		) );
		$where = array (
				'cd.content_type_id = ' . $type_id,
				'cd.content_tag_delete_status = 0',
				'cdd.content_tag_detail_delete_status = 0',
				'cdd.system_language_id = ' . $language_id,
				'cdu.content_tag_url_delete_status = 0' 
		);
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag', $search )) {
			$where = array_merge ( $where, $search ['content_tag'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag_detail', $search )) {
			$where = array_merge ( $where, $search ['content_tag_detail'] );
		}
		if (is_array ( $search ) && count ( $search ) > 0 && array_key_exists ( 'content_tag_url', $search )) {
			$where = array_merge ( $where, $search ['content_tag_url'] );
		}
		$QTag->where ( $where );
		$QTag->execute ();
		$count = 0;
		if ($QTag->hasResult ()) {
			$count = $QTag->count ();
		}
		return $count;
	}
	
	/**
	 * Update Content Tag
	 */
	public function updateTagFactory($data) {
		$status = false;
		$rawdata = $this->generateTagData ( $data, true );
		if (count ( $rawdata ) > 0) {
			$status = true;
			if (! $this->updateTag ( $rawdata ['tag'] )) {
				$status = false;
			}
			if ($status) {
				if (! $this->updateTagDetail ( $rawdata ['detail'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateTagPermission ( $rawdata ['permission'] )) {
					$status = false;
				}
			}
			if ($status) {
				if (! $this->updateTagUrl ( $rawdata ['url'] )) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Tag
	 */
	public function createTagFactory($data) {
		$status = false;
		$rawdata = $this->generateTagData ( $data );
		if (count ( $rawdata ) > 0) {
			$tag_id = $this->createTag ( $rawdata ['tag'] );
			if ($tag_id != false) {
				$this->setContentTagID ( $tag_id );
				$status = true;
				if ($status) {
					if (! $this->createTagDetail ( $rawdata ['detail'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createTagPermission ( $rawdata ['permission'] )) {
						$status = false;
					}
				}
				if ($status) {
					if (! $this->createTagUrl ( $rawdata ['url'] )) {
						$status = false;
					}
				}
			}
		}
		if ($status) {
			return $tag_id;
		} else {
			$this->deleteTagFactory ( true );
		}
		return $status;
	}
	
	/**
	 * Delete Content Tag
	 */
	public function deleteTagFactory($forever = false) {
		$this->deleteTag ( $forever );
		$this->deleteTagDetail ( $forever );
		$this->deleteTagPermission ( $forever );
		$this->deleteTagUrl ( $forever );
		$this->deleteTagLink ( $forever );
		return true;
	}
	
	/**
	 * Generate Content Tag Data
	 */
	public function generateTagData($data, $update = false) {
		$rawdata = array ();
		$old_data = array ();
		if ($update) {
			$old_data = $this->getTagComplete ();
			$old_data = $this->dataTagArrange ( $old_data );
		}
		if (array_key_exists ( 'content_tag_publish_status', $data ) && isset ( $data ['content_tag_publish_status'] )) {
			$PublishParameter = new Parameter ( array (
					'key' => 'content_tag_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PublishParameter->hasResult ()) {
				$publishstatus = $PublishParameter->getValueByKey ( $data ['content_tag_publish_status'] );
			}
		} else {
			$data ['content_tag_publish_status'] = 1;
		}
		
		if (array_key_exists ( 'content_tag_login_status', $data ) && isset ( $data ['content_tag_login_status'] )) {
			$LoginParameter = new Parameter ( array (
					'key' => 'content_tag_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($LoginParameter->hasResult ()) {
				$loginstatus = $LoginParameter->getValueByKey ( $data ['content_tag_login_status'] );
			}
		} else {
			$data ['content_tag_login_status'] = 0;
		}
		
		if (! array_key_exists ( 'content_tag_publish_date', $data ) || ! isset ( $data ['content_tag_publish_date'] )) {
			if ($update) {
				$data ['content_tag_publish_date'] = $data ['content_tag_publish_date'];
			} else {
				$data ['content_tag_publish_date'] = $data ['log_created_date'];
			}
		}
		
		$rawdata ['tag'] = array (
				'content_tag_publish_status' => $publishstatus,
				'content_tag_publish_date' => $data ['content_tag_publish_date'],
				'content_tag_login_status' => $loginstatus,
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
				if (array_key_exists ( 'content_tag_detail_title_' . $locale_iso, $data )) {
					$title = $data ['content_tag_detail_title_' . $locale_iso];
					$data_status = true;
				}
				if ($data_status) {
					if ($DefaultLocale == $locale_id) {
						$urltitle = $title;
					}
					$rawdata ['detail'] [$locale_id] = array (
							'system_language_id' => $locale_id,
							'content_tag_detail_title' => $title,
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
				'content_tag_permission_visitor' => 1,
				'content_tag_permission_user' => 0,
				'content_tag_permission_rank' => 0,
				'timestamp' => $data ['timestamp'],
				'log_created_by' => $data ['log_created_by'],
				'log_created_date' => $data ['log_created_date'],
				'log_modified_by' => $data ['log_modified_by'],
				'log_modified_date' => $data ['log_modified_date'] 
		);
		if (array_key_exists ( 'content_tag_permission_visitor', $data )) {
			$PermissionVisitorParameter = new Parameter ( array (
					'key' => 'content_tag_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			if ($PermissionVisitorParameter->hasResult ()) {
				$permissionvisitorstatus = $PermissionVisitorParameter->getValueByKey ( $data ['content_tag_permission_visitor'] );
			}
			$rawdata ['permission'] [0] = array (
					'content_tag_permission_visitor' => $permissionvisitorstatus,
					'content_tag_permission_user' => 0,
					'content_tag_permission_rank' => 0,
					'timestamp' => $data ['timestamp'],
					'log_created_by' => $data ['log_created_by'],
					'log_created_date' => $data ['log_created_date'],
					'log_modified_by' => $data ['log_modified_by'],
					'log_modified_date' => $data ['log_modified_date'] 
			);
			if ($permissionvisitorstatus == 0) {
				if (array_key_exists ( 'content_tag_permission_user', $data ) && count ( $data ['content_tag_permission_user'] ) > 0) {
					foreach ( $data ['content_tag_permission_user'] as $user_value ) {
						$user_value = $this->Decrypt ( $user_value );
						if ($user_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_tag_permission_visitor' => 0,
									'content_tag_permission_user' => $user_value,
									'content_tag_permission_rank' => 0,
									'timestamp' => $data ['timestamp'],
									'log_created_by' => $data ['log_created_by'],
									'log_created_date' => $data ['log_created_date'],
									'log_modified_by' => $data ['log_modified_by'],
									'log_modified_date' => $data ['log_modified_date'] 
							);
						}
					}
				}
				if (array_key_exists ( 'content_tag_permission_rank', $data ) && count ( $data ['content_tag_permission_rank'] ) > 0) {
					foreach ( $data ['content_tag_permission_rank'] as $rank_value ) {
						$rank_value = $this->Decrypt ( $rank_value );
						if ($rank_value > 0) {
							$rawdata ['permission'] [] = array (
									'content_tag_permission_visitor' => 0,
									'content_tag_permission_user' => 0,
									'content_tag_permission_rank' => $rank_value,
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
		$data ['content_tag_url_keyword'] = (array_key_exists ( 'content_tag_url_keyword', $data ) ? $data ['content_tag_url_keyword'] : $urltitle);
		$data ['content_tag_url_keyword'] = strtoupper ( $data ['content_tag_url_keyword'] );
		$data ['content_tag_url_keyword'] = trim ( $data ['content_tag_url_keyword'] );
		$urltitle = $ToUnderscore->filter ( $data ['content_tag_url_keyword'] );
		$rawdata ['url'] = array (
				'content_tag_url_keyword' => $urltitle,
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
	 * Data TagArrange
	 *
	 * @return Array
	 */
	public function dataTagArrange($data = null) {
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
			$rawdata = array_merge ( $rawdata, $data ['tag'] );
			$rawdata = array_merge ( $rawdata, $data ['url'] );
			$rawdata ['content_tag_permission_visitor_text'] = "";
			$rawdata ['content_tag_permission_rank'] = "";
			$rawdata ['content_tag_permission_user'] = "";
			$rawdata ['content_tag_permission_rank_options'] = array ();
			$rawdata ['content_tag_permission_user_options'] = array ();
			if (is_array ( $data ['permission'] ) && count ( $data ['permission'] ) > 0) {
				$permission_rank_status = false;
				$permission_user_status = false;
				$permission_visitor_status = false;
				$content_tag_permission_rank = "";
				$content_tag_permission_user = "";
				$content_tag_permission_rank_options = array ();
				$content_tag_permission_user_options = array ();
				$content_tag_permission_visitor_text = "";
				$content_tag_permission_visitor = "";
				foreach ( $data ['permission'] as $permissions_key => $permissions_value ) {
					if (array_key_exists ( 'content_tag_permission_rank', $permissions_value ) && ! empty ( $permissions_value ['content_tag_permission_rank'] )) {
						$content_tag_permission_rank .= $permissions_value ['content_tag_permission_rank'] . ", ";
						$content_tag_permission_rank_options [$permissions_value ['content_tag_permission_rank_options']] = $permissions_value ['content_tag_permission_rank'];
						$permission_rank_status = true;
					}
					if (array_key_exists ( 'content_tag_permission_user', $permissions_value ) && ! empty ( $permissions_value ['content_tag_permission_user'] )) {
						$content_tag_permission_user .= $permissions_value ['content_tag_permission_user'] . " , ";
						$content_tag_permission_user_options [$permissions_value ['content_tag_permission_user_options']] = $permissions_value ['content_tag_permission_user'];
						$permission_user_status = true;
					}
					if (array_key_exists ( 'content_tag_permission_visitor', $permissions_value )) {
						if ($permissions_value ['content_tag_permission_visitor'] == 1) {
							$content_tag_permission_visitor_text = $permissions_value ['content_tag_permission_visitor_text'];
							$content_tag_permission_visitor = $permissions_value ['content_tag_permission_visitor'];
							$permission_visitor_status = true;
						}
						if (! $permission_visitor_status) {
							$content_tag_permission_visitor_text = $permissions_value ['content_tag_permission_visitor_text'];
							$content_tag_permission_visitor = $permissions_value ['content_tag_permission_visitor'];
						}
					}
				}
				$content_tag_permission_rank = trim ( $content_tag_permission_rank );
				if (substr ( $content_tag_permission_rank, strlen ( $content_tag_permission_rank ) - 1 ) == ",") {
					$content_tag_permission_rank = substr ( $content_tag_permission_rank, 0, (strlen ( $content_tag_permission_rank ) - 1) );
				}
				$content_tag_permission_user = trim ( $content_tag_permission_user );
				if (substr ( $content_tag_permission_user, strlen ( $content_tag_permission_user ) - 1 ) == ",") {
					$content_tag_permission_user = substr ( $content_tag_permission_user, 0, (strlen ( $content_tag_permission_user ) - 1) );
				}
				$rawdata ['content_tag_permission_visitor_text'] = $content_tag_permission_visitor_text;
				$rawdata ['content_tag_permission_visitor'] = $content_tag_permission_visitor;
				if (! $permission_visitor_status) {
					$rawdata ['content_tag_permission_rank'] = $content_tag_permission_rank;
					$rawdata ['content_tag_permission_user'] = $content_tag_permission_user;
					$rawdata ['content_tag_permission_rank_options'] = $content_tag_permission_rank_options;
					$rawdata ['content_tag_permission_user_options'] = $content_tag_permission_user_options;
				} else {
					$rawdata ['content_tag_permission_rank'] = 'N/A';
					$rawdata ['content_tag_permission_user'] = 'N/A';
					$rawdata ['content_tag_permission_rank_options'] = 'N/A';
					$rawdata ['content_tag_permission_user_options'] = 'N/A';
				}
			}
			ksort ( $rawdata );
		}
		return $rawdata;
	}
}

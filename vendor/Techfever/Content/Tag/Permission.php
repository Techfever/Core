<?php

namespace Techfever\Content\Tag;

use Techfever\Exception;
use Techfever\Parameter\Parameter;

class Permission extends Url {
	
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
	 * @var Content Tag Permission
	 *     
	 */
	private $content_tag_permission = null;
	
	/**
	 * Initial Content Tag Permission
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
	 * Get Content Tag Permission
	 *
	 * @return array()
	 */
	public function getTagPermission() {
		if (! is_array ( $this->content_tag_permission ) || count ( $this->content_tag_permission ) < 1) {
			
			$PermissionParameter = new Parameter ( array (
					'key' => 'content_tag_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$UserManagement = $this->getUserManagement ();
			$Rank = $this->getUserRank ();
			
			$tag_id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QTagPermission = $this->getDatabase ();
			$QTagPermission->select ();
			$QTagPermission->columns ( array (
					'content_tag_permission_id',
					'user_access_id',
					'user_rank_id',
					'content_tag_permission_visitor',
					'content_tag_permission_created_date',
					'content_tag_permission_created_by',
					'content_tag_permission_modified_date',
					'content_tag_permission_modified_by' 
			) );
			$QTagPermission->from ( array (
					'cdp' => 'content_tag_permission' 
			) );
			$QTagPermission->where ( array (
					'cdp.content_tag_id' => $tag_id,
					'cdp.content_tag_permission_delete_status' => '0' 
			) );
			$QTagPermission->order ( array (
					'cdp.content_tag_permission_visitor desc',
					'cdp.user_rank_id asc',
					'cdp.user_access_id asc' 
			) );
			$QTagPermission->execute ();
			if ($QTagPermission->hasResult ()) {
				while ( $QTagPermission->valid () ) {
					$rawdata = $QTagPermission->current ();
					
					$rawdata ['content_tag_permission_visitor_text'] = "";
					if ($PermissionParameter->hasResult ()) {
						$rawdata ['content_tag_permission_visitor_text'] = $PermissionParameter->getMessageByValue ( $rawdata ['content_tag_permission_visitor'] );
					}
					
					$rawdata ['content_tag_permission_rank_options'] = ($rawdata ['user_rank_id'] > 0 ? $this->Encrypt ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_tag_permission_user_options'] = ($rawdata ['user_access_id'] > 0 ? $this->Encrypt ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_tag_permission_rank'] = ($rawdata ['user_rank_id'] > 0 ? $Rank->getMessage ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_tag_permission_user'] = ($rawdata ['user_access_id'] > 0 ? $Rank->getMessage ( $UserManagement->getRankID ( $rawdata ['user_access_id'] ) ) . " - " . $UserManagement->getUsername ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_tag_permission_created_date_format'] = "";
					if ($rawdata ['content_tag_permission_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_permission_created_date'] );
						$rawdata ['content_tag_permission_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_tag_permission_modified_date_format'] = "";
					if ($rawdata ['content_tag_permission_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_permission_modified_date'] );
						$rawdata ['content_tag_permission_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_permission_id'] );
					$rawdata ['content_tag_permission_id_modify'] = $cryptID;
					
					$this->content_tag_permission [$rawdata ['content_tag_permission_id']] = $rawdata;
					$QTagPermission->next ();
				}
			}
		}
		return $this->content_tag_permission;
	}
	
	/**
	 * Reset Content Tag Permission
	 */
	public function resetTagPermission() {
		$this->content_tag_permission = null;
	}
	
	/**
	 * Update Content Tag Permission
	 */
	public function updateTagPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteTagPermission ()) {
				if ($this->createTagPermission ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Tag Permission
	 */
	public function createTagPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$permission = array ();
			$tag_id = $this->getContentTagID ();
			foreach ( $data as $rawdata ) {
				$user_access_id = (array_key_exists ( 'content_tag_permission_user', $rawdata ) ? $rawdata ['content_tag_permission_user'] : 0);
				$user_rank_id = (array_key_exists ( 'content_tag_permission_rank', $rawdata ) ? $rawdata ['content_tag_permission_rank'] : 0);
				$visitor = (array_key_exists ( 'content_tag_permission_visitor', $rawdata ) ? $rawdata ['content_tag_permission_visitor'] : 0);
				$permission [] = array (
						'content_tag_id' => $tag_id,
						'user_access_id' => $user_access_id,
						'user_rank_id' => $user_rank_id,
						'content_tag_permission_visitor' => $visitor,
						'content_tag_permission_created_date' => $rawdata ['log_created_date'],
						'content_tag_permission_created_by' => $rawdata ['log_created_by'],
						'content_tag_permission_modified_date' => $rawdata ['log_modified_date'],
						'content_tag_permission_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $permission ) && count ( $permission ) > 0) {
				$status = true;
				$ITagPermission = $this->getDatabase ();
				$ITagPermission->insert ();
				$ITagPermission->into ( 'content_tag_permission' );
				$ITagPermission->columns ( array (
						'content_tag_id',
						'user_access_id',
						'user_rank_id',
						'content_tag_permission_visitor',
						'content_tag_permission_created_date',
						'content_tag_permission_created_by',
						'content_tag_permission_modified_date',
						'content_tag_permission_modified_by' 
				) );
				$ITagPermission->values ( $permission, 'multiple' );
				$ITagPermission->execute ();
				if (! $ITagPermission->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Tag Permission
	 *
	 * @return Boolean
	 *
	 */
	public function verifyTagPermissionID($id) {
		$tag_id = $this->getContentTagID ();
		if (! empty ( $id )) {
			$VTagPermission = $this->getDatabase ();
			$VTagPermission->select ();
			$VTagPermission->columns ( array (
					'id' => 'content_tag_permission_id' 
			) );
			$VTagPermission->from ( array (
					'cdp' => 'content_tag_permission' 
			) );
			$where = array (
					'cdp.content_tag_permission_id = ' . $id,
					'cdp.content_tag_id = ' . $tag_id 
			);
			$VTagPermission->where ( $where );
			$VTagPermission->limit ( 1 );
			$VTagPermission->execute ();
			if ($VTagPermission->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Tag Permission
	 *
	 * @return Boolean
	 *
	 */
	public function deleteTagPermission($forever = false) {
		$id = $this->getContentTagID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DTagPermission = $this->getDatabase ();
				$DTagPermission->delete ();
				$DTagPermission->from ( 'content_tag_permission' );
				$where = array (
						'content_tag_id = ' . $id 
				);
				$DTagPermission->where ( $where );
				$DTagPermission->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UTagPermission = $this->getDatabase ();
				$UTagPermission->update ();
				$UTagPermission->table ( 'content_tag_permission' );
				$UTagPermission->set ( array (
						'content_tag_permission_delete_status' => '1',
						'content_tag_permission_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_tag_permission_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UTagPermission->where ( array (
						'content_tag_id' => $id 
				) );
				$UTagPermission->execute ();
				return true;
			}
		}
		return false;
	}
}

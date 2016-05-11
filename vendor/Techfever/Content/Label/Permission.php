<?php

namespace Techfever\Content\Label;

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
			'label_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Label Permission
	 *     
	 */
	private $content_label_permission = null;
	
	/**
	 * Initial Content Label Permission
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
	 * Get Content Label Permission
	 *
	 * @return array()
	 */
	public function getLabelPermission() {
		if (! is_array ( $this->content_label_permission ) || count ( $this->content_label_permission ) < 1) {
			
			$PermissionParameter = new Parameter ( array (
					'key' => 'content_label_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$UserManagement = $this->getUserManagement ();
			$Rank = $this->getUserRank ();
			
			$label_id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QLabelPermission = $this->getDatabase ();
			$QLabelPermission->select ();
			$QLabelPermission->columns ( array (
					'content_label_permission_id',
					'user_access_id',
					'user_rank_id',
					'content_label_permission_visitor',
					'content_label_permission_created_date',
					'content_label_permission_created_by',
					'content_label_permission_modified_date',
					'content_label_permission_modified_by' 
			) );
			$QLabelPermission->from ( array (
					'cdp' => 'content_label_permission' 
			) );
			$QLabelPermission->where ( array (
					'cdp.content_label_id' => $label_id,
					'cdp.content_label_permission_delete_status' => '0' 
			) );
			$QLabelPermission->order ( array (
					'cdp.content_label_permission_visitor desc',
					'cdp.user_rank_id asc',
					'cdp.user_access_id asc' 
			) );
			$QLabelPermission->execute ();
			if ($QLabelPermission->hasResult ()) {
				while ( $QLabelPermission->valid () ) {
					$rawdata = $QLabelPermission->current ();
					
					$rawdata ['content_label_permission_visitor_text'] = "";
					if ($PermissionParameter->hasResult ()) {
						$rawdata ['content_label_permission_visitor_text'] = $PermissionParameter->getMessageByValue ( $rawdata ['content_label_permission_visitor'] );
					}
					
					$rawdata ['content_label_permission_rank_options'] = ($rawdata ['user_rank_id'] > 0 ? $this->Encrypt ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_label_permission_user_options'] = ($rawdata ['user_access_id'] > 0 ? $this->Encrypt ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_label_permission_rank'] = ($rawdata ['user_rank_id'] > 0 ? $Rank->getMessage ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_label_permission_user'] = ($rawdata ['user_access_id'] > 0 ? $Rank->getMessage ( $UserManagement->getRankID ( $rawdata ['user_access_id'] ) ) . " - " . $UserManagement->getUsername ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_label_permission_created_date_format'] = "";
					if ($rawdata ['content_label_permission_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_permission_created_date'] );
						$rawdata ['content_label_permission_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_label_permission_modified_date_format'] = "";
					if ($rawdata ['content_label_permission_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_permission_modified_date'] );
						$rawdata ['content_label_permission_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_permission_id'] );
					$rawdata ['content_label_permission_id_modify'] = $cryptID;
					
					$this->content_label_permission [$rawdata ['content_label_permission_id']] = $rawdata;
					$QLabelPermission->next ();
				}
			}
		}
		return $this->content_label_permission;
	}
	
	/**
	 * Reset Content Label Permission
	 */
	public function resetLabelPermission() {
		$this->content_label_permission = null;
	}
	
	/**
	 * Update Content Label Permission
	 */
	public function updateLabelPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteLabelPermission ()) {
				if ($this->createLabelPermission ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Label Permission
	 */
	public function createLabelPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$permission = array ();
			$label_id = $this->getContentLabelID ();
			foreach ( $data as $rawdata ) {
				$user_access_id = (array_key_exists ( 'content_label_permission_user', $rawdata ) ? $rawdata ['content_label_permission_user'] : 0);
				$user_rank_id = (array_key_exists ( 'content_label_permission_rank', $rawdata ) ? $rawdata ['content_label_permission_rank'] : 0);
				$visitor = (array_key_exists ( 'content_label_permission_visitor', $rawdata ) ? $rawdata ['content_label_permission_visitor'] : 0);
				$permission [] = array (
						'content_label_id' => $label_id,
						'user_access_id' => $user_access_id,
						'user_rank_id' => $user_rank_id,
						'content_label_permission_visitor' => $visitor,
						'content_label_permission_created_date' => $rawdata ['log_created_date'],
						'content_label_permission_created_by' => $rawdata ['log_created_by'],
						'content_label_permission_modified_date' => $rawdata ['log_modified_date'],
						'content_label_permission_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $permission ) && count ( $permission ) > 0) {
				$status = true;
				$ILabelPermission = $this->getDatabase ();
				$ILabelPermission->insert ();
				$ILabelPermission->into ( 'content_label_permission' );
				$ILabelPermission->columns ( array (
						'content_label_id',
						'user_access_id',
						'user_rank_id',
						'content_label_permission_visitor',
						'content_label_permission_created_date',
						'content_label_permission_created_by',
						'content_label_permission_modified_date',
						'content_label_permission_modified_by' 
				) );
				$ILabelPermission->values ( $permission, 'multiple' );
				$ILabelPermission->execute ();
				if (! $ILabelPermission->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Label Permission
	 *
	 * @return Boolean
	 *
	 */
	public function verifyLabelPermissionID($id) {
		$label_id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			$VLabelPermission = $this->getDatabase ();
			$VLabelPermission->select ();
			$VLabelPermission->columns ( array (
					'id' => 'content_label_permission_id' 
			) );
			$VLabelPermission->from ( array (
					'cdp' => 'content_label_permission' 
			) );
			$where = array (
					'cdp.content_label_permission_id = ' . $id,
					'cdp.content_label_id = ' . $label_id 
			);
			$VLabelPermission->where ( $where );
			$VLabelPermission->limit ( 1 );
			$VLabelPermission->execute ();
			if ($VLabelPermission->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Label Permission
	 *
	 * @return Boolean
	 *
	 */
	public function deleteLabelPermission($forever = false) {
		$id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DLabelPermission = $this->getDatabase ();
				$DLabelPermission->delete ();
				$DLabelPermission->from ( 'content_label_permission' );
				$where = array (
						'content_label_id = ' . $id 
				);
				$DLabelPermission->where ( $where );
				$DLabelPermission->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$ULabelPermission = $this->getDatabase ();
				$ULabelPermission->update ();
				$ULabelPermission->table ( 'content_label_permission' );
				$ULabelPermission->set ( array (
						'content_label_permission_delete_status' => '1',
						'content_label_permission_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_label_permission_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$ULabelPermission->where ( array (
						'content_label_id' => $id 
				) );
				$ULabelPermission->execute ();
				return true;
			}
		}
		return false;
	}
}

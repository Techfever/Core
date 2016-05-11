<?php

namespace Techfever\Content\Data;

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
			'data_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Data Permission
	 *     
	 */
	private $content_data_permission = null;
	
	/**
	 * Initial Content Data Permission
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
	 * Get Content Data Permission
	 *
	 * @return array()
	 */
	public function getDataPermission() {
		if (! is_array ( $this->content_data_permission ) || count ( $this->content_data_permission ) < 1) {
			
			$PermissionParameter = new Parameter ( array (
					'key' => 'content_data_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$UserManagement = $this->getUserManagement ();
			$Rank = $this->getUserRank ();
			
			$data_id = $this->getContentDataID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QDataPermission = $this->getDatabase ();
			$QDataPermission->select ();
			$QDataPermission->columns ( array (
					'content_data_permission_id',
					'user_access_id',
					'user_rank_id',
					'content_data_permission_visitor',
					'content_data_permission_created_date',
					'content_data_permission_created_by',
					'content_data_permission_modified_date',
					'content_data_permission_modified_by' 
			) );
			$QDataPermission->from ( array (
					'cdp' => 'content_data_permission' 
			) );
			$QDataPermission->where ( array (
					'cdp.content_data_id' => $data_id,
					'cdp.content_data_permission_delete_status' => '0' 
			) );
			$QDataPermission->order ( array (
					'cdp.content_data_permission_visitor desc',
					'cdp.user_rank_id asc',
					'cdp.user_access_id asc' 
			) );
			$QDataPermission->execute ();
			if ($QDataPermission->hasResult ()) {
				while ( $QDataPermission->valid () ) {
					$rawdata = $QDataPermission->current ();
					
					$rawdata ['content_data_permission_visitor_text'] = "";
					if ($PermissionParameter->hasResult ()) {
						$rawdata ['content_data_permission_visitor_text'] = $PermissionParameter->getMessageByValue ( $rawdata ['content_data_permission_visitor'] );
					}
					
					$rawdata ['content_data_permission_rank_options'] = ($rawdata ['user_rank_id'] > 0 ? $this->Encrypt ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_data_permission_user_options'] = ($rawdata ['user_access_id'] > 0 ? $this->Encrypt ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_data_permission_rank'] = ($rawdata ['user_rank_id'] > 0 ? $Rank->getMessage ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['content_data_permission_user'] = ($rawdata ['user_access_id'] > 0 ? $Rank->getMessage ( $UserManagement->getRankID ( $rawdata ['user_access_id'] ) ) . " - " . $UserManagement->getUsername ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['content_data_permission_created_date_format'] = "";
					if ($rawdata ['content_data_permission_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_permission_created_date'] );
						$rawdata ['content_data_permission_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_data_permission_modified_date_format'] = "";
					if ($rawdata ['content_data_permission_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_permission_modified_date'] );
						$rawdata ['content_data_permission_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_data_permission_id'] );
					$rawdata ['content_data_permission_id_modify'] = $cryptID;
					
					$this->content_data_permission [$rawdata ['content_data_permission_id']] = $rawdata;
					$QDataPermission->next ();
				}
			}
		}
		return $this->content_data_permission;
	}
	
	/**
	 * Reset Content Data Permission
	 */
	public function resetDataPermission() {
		$this->content_data_permission = null;
	}
	
	/**
	 * Update Content Data Permission
	 */
	public function updateDataPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteDataPermission ()) {
				if ($this->createDataPermission ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Data Permission
	 */
	public function createDataPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$permission = array ();
			$data_id = $this->getContentDataID ();
			foreach ( $data as $rawdata ) {
				$user_access_id = (array_key_exists ( 'content_data_permission_user', $rawdata ) ? $rawdata ['content_data_permission_user'] : 0);
				$user_rank_id = (array_key_exists ( 'content_data_permission_rank', $rawdata ) ? $rawdata ['content_data_permission_rank'] : 0);
				$visitor = (array_key_exists ( 'content_data_permission_visitor', $rawdata ) ? $rawdata ['content_data_permission_visitor'] : 0);
				$permission [] = array (
						'content_data_id' => $data_id,
						'user_access_id' => $user_access_id,
						'user_rank_id' => $user_rank_id,
						'content_data_permission_visitor' => $visitor,
						'content_data_permission_created_date' => $rawdata ['log_created_date'],
						'content_data_permission_created_by' => $rawdata ['log_created_by'],
						'content_data_permission_modified_date' => $rawdata ['log_modified_date'],
						'content_data_permission_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $permission ) && count ( $permission ) > 0) {
				$status = true;
				$IDataPermission = $this->getDatabase ();
				$IDataPermission->insert ();
				$IDataPermission->into ( 'content_data_permission' );
				$IDataPermission->columns ( array (
						'content_data_id',
						'user_access_id',
						'user_rank_id',
						'content_data_permission_visitor',
						'content_data_permission_created_date',
						'content_data_permission_created_by',
						'content_data_permission_modified_date',
						'content_data_permission_modified_by' 
				) );
				$IDataPermission->values ( $permission, 'multiple' );
				$IDataPermission->execute ();
				if (! $IDataPermission->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Data Permission
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataPermissionID($id) {
		$data_id = $this->getContentDataID ();
		if (! empty ( $id )) {
			$VDataPermission = $this->getDatabase ();
			$VDataPermission->select ();
			$VDataPermission->columns ( array (
					'id' => 'content_data_permission_id' 
			) );
			$VDataPermission->from ( array (
					'cdp' => 'content_data_permission' 
			) );
			$where = array (
					'cdp.content_data_permission_id = ' . $id,
					'cdp.content_data_id = ' . $data_id 
			);
			$VDataPermission->where ( $where );
			$VDataPermission->limit ( 1 );
			$VDataPermission->execute ();
			if ($VDataPermission->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Data Permission
	 *
	 * @return Boolean
	 *
	 */
	public function deleteDataPermission($forever = false) {
		$id = $this->getContentDataID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DDataPermission = $this->getDatabase ();
				$DDataPermission->delete ();
				$DDataPermission->from ( 'content_data_permission' );
				$where = array (
						'content_data_id = ' . $id 
				);
				$DDataPermission->where ( $where );
				$DDataPermission->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataPermission = $this->getDatabase ();
				$UDataPermission->update ();
				$UDataPermission->table ( 'content_data_permission' );
				$UDataPermission->set ( array (
						'content_data_permission_delete_status' => '1',
						'content_data_permission_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_data_permission_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataPermission->where ( array (
						'content_data_id' => $id 
				) );
				$UDataPermission->execute ();
				return true;
			}
		}
		return false;
	}
}

<?php

namespace Techfever\Document\Data;

use Techfever\Exception;
use Techfever\Parameter\Parameter;
use Techfever\Document\Type;

class Permission extends Type {
	
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
	 * @var Document Data Permission
	 *     
	 */
	private $document_data_permission = null;
	
	/**
	 * Initial Document Data Permission
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
	 * Get Document Data Permission
	 *
	 * @return array()
	 */
	public function getDataPermission() {
		if (! is_array ( $this->document_data_permission ) || count ( $this->document_data_permission ) < 1) {
			
			$PermissionParameter = new Parameter ( array (
					'key' => 'document_data_permission_visitor',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$UserManagement = $this->getUserManagement ();
			$Rank = $this->getUserRank ();
			
			$data_id = $this->getDocumentDataID ();
			$type_id = $this->getDocumentTypeID ();
			$rawdata = array ();
			$QDataPermission = $this->getDatabase ();
			$QDataPermission->select ();
			$QDataPermission->columns ( array (
					'document_data_permission_id',
					'user_access_id',
					'user_rank_id',
					'document_data_permission_visitor',
					'document_data_permission_created_date',
					'document_data_permission_created_by',
					'document_data_permission_modified_date',
					'document_data_permission_modified_by' 
			) );
			$QDataPermission->from ( array (
					'cdp' => 'document_data_permission' 
			) );
			$QDataPermission->where ( array (
					'cdp.document_data_id' => $data_id,
					'cdp.document_data_permission_delete_status' => '0' 
			) );
			$QDataPermission->order ( array (
					'cdp.document_data_permission_visitor desc',
					'cdp.user_rank_id asc',
					'cdp.user_access_id asc' 
			) );
			$QDataPermission->execute ();
			if ($QDataPermission->hasResult ()) {
				while ( $QDataPermission->valid () ) {
					$rawdata = $QDataPermission->current ();
					
					$rawdata ['document_data_permission_visitor_text'] = "";
					if ($PermissionParameter->hasResult ()) {
						$rawdata ['document_data_permission_visitor_text'] = $PermissionParameter->getMessageByValue ( $rawdata ['document_data_permission_visitor'] );
					}
					
					$rawdata ['document_data_permission_rank_options'] = ($rawdata ['user_rank_id'] > 0 ? $this->Encrypt ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['document_data_permission_user_options'] = ($rawdata ['user_access_id'] > 0 ? $this->Encrypt ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['document_data_permission_rank'] = ($rawdata ['user_rank_id'] > 0 ? $Rank->getMessage ( $rawdata ['user_rank_id'] ) : null);
					$rawdata ['document_data_permission_user'] = ($rawdata ['user_access_id'] > 0 ? $Rank->getMessage ( $UserManagement->getRankID ( $rawdata ['user_access_id'] ) ) . " - " . $UserManagement->getUsername ( $rawdata ['user_access_id'] ) : null);
					
					$rawdata ['document_data_permission_created_date_format'] = "";
					if ($rawdata ['document_data_permission_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_permission_created_date'] );
						$rawdata ['document_data_permission_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['document_data_permission_modified_date_format'] = "";
					if ($rawdata ['document_data_permission_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_permission_modified_date'] );
						$rawdata ['document_data_permission_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['document_data_permission_id'] );
					$rawdata ['document_data_permission_id_modify'] = $cryptID;
					
					$this->document_data_permission [$rawdata ['document_data_permission_id']] = $rawdata;
					$QDataPermission->next ();
				}
			}
		}
		return $this->document_data_permission;
	}
	
	/**
	 * Reset Document Data Permission
	 */
	public function resetDataPermission() {
		$this->document_data_permission = null;
	}
	
	/**
	 * Update Document Data Permission
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
	 * Create Document Data Permission
	 */
	public function createDataPermission($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$permission = array ();
			$data_id = $this->getDocumentDataID ();
			foreach ( $data as $rawdata ) {
				$user_access_id = (array_key_exists ( 'document_data_permission_user', $rawdata ) ? $rawdata ['document_data_permission_user'] : 0);
				$user_rank_id = (array_key_exists ( 'document_data_permission_rank', $rawdata ) ? $rawdata ['document_data_permission_rank'] : 0);
				$visitor = (array_key_exists ( 'document_data_permission_visitor', $rawdata ) ? $rawdata ['document_data_permission_visitor'] : 0);
				$permission [] = array (
						'document_data_id' => $data_id,
						'user_access_id' => $user_access_id,
						'user_rank_id' => $user_rank_id,
						'document_data_permission_visitor' => $visitor,
						'document_data_permission_created_date' => $rawdata ['log_created_date'],
						'document_data_permission_created_by' => $rawdata ['log_created_by'],
						'document_data_permission_modified_date' => $rawdata ['log_modified_date'],
						'document_data_permission_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $permission ) && count ( $permission ) > 0) {
				$status = true;
				$IDataPermission = $this->getDatabase ();
				$IDataPermission->insert ();
				$IDataPermission->into ( 'document_data_permission' );
				$IDataPermission->columns ( array (
						'document_data_id',
						'user_access_id',
						'user_rank_id',
						'document_data_permission_visitor',
						'document_data_permission_created_date',
						'document_data_permission_created_by',
						'document_data_permission_modified_date',
						'document_data_permission_modified_by' 
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
	 * Verify Document Data Permission
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataPermissionID($id) {
		$data_id = $this->getDocumentDataID ();
		if (! empty ( $id )) {
			$VDataPermission = $this->getDatabase ();
			$VDataPermission->select ();
			$VDataPermission->columns ( array (
					'id' => 'document_data_permission_id' 
			) );
			$VDataPermission->from ( array (
					'cdp' => 'document_data_permission' 
			) );
			$where = array (
					'cdp.document_data_permission_id = ' . $id,
					'cdp.document_data_id = ' . $data_id 
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
	 * Delete Document Data Permission
	 *
	 * @return Boolean
	 *
	 */
	public function deleteDataPermission($forever = false) {
		$id = $this->getDocumentDataID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DDataPermission = $this->getDatabase ();
				$DDataPermission->delete ();
				$DDataPermission->from ( 'document_data_permission' );
				$where = array (
						'document_data_id = ' . $id 
				);
				$DDataPermission->where ( $where );
				$DDataPermission->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataPermission = $this->getDatabase ();
				$UDataPermission->update ();
				$UDataPermission->table ( 'document_data_permission' );
				$UDataPermission->set ( array (
						'document_data_permission_delete_status' => '1',
						'document_data_permission_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'document_data_permission_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataPermission->where ( array (
						'document_data_id' => $id 
				) );
				$UDataPermission->execute ();
				return true;
			}
		}
		return false;
	}
}

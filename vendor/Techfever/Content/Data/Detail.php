<?php

namespace Techfever\Content\Data;

use Techfever\Exception;

class Detail extends Permission {
	
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
	 * @var Content Data Detail
	 *     
	 */
	private $content_data_detail = null;
	
	/**
	 * Initial Content Data Detail
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
	 * Get Content Data Detail
	 *
	 * @return array()
	 */
	public function getDataDetail() {
		if (! is_array ( $this->content_data_detail ) || count ( $this->content_data_detail ) < 1) {
			$data_id = $this->getContentDataID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QDataDetail = $this->getDatabase ();
			$QDataDetail->select ();
			$QDataDetail->columns ( array (
					'content_data_detail_id',
					'system_language_id',
					'content_data_detail_title',
					'content_data_detail_information',
					'content_data_detail_created_date',
					'content_data_detail_created_by',
					'content_data_detail_modified_date',
					'content_data_detail_modified_by' 
			) );
			$QDataDetail->from ( array (
					'cdd' => 'content_data_detail' 
			) );
			$QDataDetail->where ( array (
					'cdd.content_data_id' => $data_id,
					'cdd.content_data_detail_delete_status' => '0' 
			) );
			$QDataDetail->execute ();
			if ($QDataDetail->hasResult ()) {
				while ( $QDataDetail->valid () ) {
					$rawdata = $QDataDetail->current ();
					
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
					
					$cryptID = $this->Encrypt ( $rawdata ['content_data_detail_id'] );
					$rawdata ['content_data_detail_id_modify'] = $cryptID;
					
					$this->content_data_detail [$rawdata ['system_language_id']] = $rawdata;
					$QDataDetail->next ();
				}
			}
		}
		return $this->content_data_detail;
	}
	
	/**
	 * Reset Content Data Detail
	 */
	public function resetDataDetail() {
		$this->content_data_detail = null;
	}
	
	/**
	 * Update Content Data Detail
	 */
	public function updateDataDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteDataDetail ()) {
				if ($this->createDataDetail ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Data Detail
	 */
	public function createDataDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$detail = array ();
			$data_id = $this->getContentDataID ();
			foreach ( $data as $rawdata ) {
				$system_language_id = (array_key_exists ( 'system_language_id', $rawdata ) ? $rawdata ['system_language_id'] : 0);
				$content_data_detail_title = (array_key_exists ( 'content_data_detail_title', $rawdata ) ? $rawdata ['content_data_detail_title'] : null);
				$content_data_detail_information = (array_key_exists ( 'content_data_detail_information', $rawdata ) ? $rawdata ['content_data_detail_information'] : null);
				$detail [] = array (
						'content_data_id' => $data_id,
						'system_language_id' => $system_language_id,
						'content_data_detail_title' => $content_data_detail_title,
						'content_data_detail_information' => $content_data_detail_information,
						'content_data_detail_created_date' => $rawdata ['log_created_date'],
						'content_data_detail_created_by' => $rawdata ['log_created_by'],
						'content_data_detail_modified_date' => $rawdata ['log_modified_date'],
						'content_data_detail_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $detail ) && count ( $detail ) > 0) {
				$status = true;
				$IDataDetail = $this->getDatabase ();
				$IDataDetail->insert ();
				$IDataDetail->into ( 'content_data_detail' );
				$IDataDetail->columns ( array (
						'content_data_id',
						'system_language_id',
						'content_data_detail_title',
						'content_data_detail_information',
						'content_data_detail_created_date',
						'content_data_detail_created_by',
						'content_data_detail_modified_date',
						'content_data_detail_modified_by' 
				) );
				$IDataDetail->values ( $detail, 'multiple' );
				$IDataDetail->execute ();
				if (! $IDataDetail->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Data Detail
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataDetailID($id) {
		$data_id = $this->getContentDataID ();
		if (! empty ( $id )) {
			$VDataDetail = $this->getDatabase ();
			$VDataDetail->select ();
			$VDataDetail->columns ( array (
					'id' => 'content_data_detail_id' 
			) );
			$VDataDetail->from ( array (
					'cdd' => 'content_data_detail' 
			) );
			$where = array (
					'cdd.content_data_detail_id = ' . $id,
					'cdd.content_data_id = ' . $data_id 
			);
			$VDataDetail->where ( $where );
			$VDataDetail->limit ( 1 );
			$VDataDetail->execute ();
			if ($VDataDetail->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Data Detail
	 *
	 * @return Boolean
	 *
	 */
	public function deleteDataDetail($forever = false) {
		$id = $this->getContentDataID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DDataDetail = $this->getDatabase ();
				$DDataDetail->delete ();
				$DDataDetail->from ( 'content_data_detail' );
				$where = array (
						'content_data_id = ' . $id 
				);
				$DDataDetail->where ( $where );
				$DDataDetail->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataDetail = $this->getDatabase ();
				$UDataDetail->update ();
				$UDataDetail->table ( 'content_data_detail' );
				$UDataDetail->set ( array (
						'content_data_detail_delete_status' => '1',
						'content_data_detail_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_data_detail_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataDetail->where ( array (
						'content_data_id' => $id 
				) );
				$UDataDetail->execute ();
				return true;
			}
		}
		return false;
	}
}

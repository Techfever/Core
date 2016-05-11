<?php

namespace Techfever\Content\Data;

use Techfever\Exception;

class LinkLabel extends LinkTag {
	
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
	 * @var Content Label Link
	 *     
	 */
	private $content_label_to_data = null;
	
	/**
	 * Initial Content Label Link
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
	 * Get Content Label Link
	 *
	 * @return array()
	 */
	public function getDataLinkLabel() {
		if (! is_array ( $this->content_label_to_data ) || count ( $this->content_label_to_data ) < 1) {
			$data_id = $this->getContentDataID ();
			$language_id = $this->getContentLanguageID ();
			$rawdata = array ();
			$QDataLinkLabel = $this->getDatabase ();
			$QDataLinkLabel->select ();
			$QDataLinkLabel->columns ( array (
					'content_label_to_data_id',
					'content_data_id',
					'content_label_id',
					'content_label_to_data_created_date',
					'content_label_to_data_created_by',
					'content_label_to_data_modified_date',
					'content_label_to_data_modified_by' 
			) );
			$QDataLinkLabel->from ( array (
					'cdl' => 'content_label_to_data' 
			) );
			$QDataLinkLabel->join ( array (
					'cdd' => 'content_label_detail' 
			), 'cdd.content_label_id  = cdl.content_label_id', array (
					'content_label_detail_id',
					'system_language_id',
					'content_label_detail_title' 
			) );
			$QDataLinkLabel->where ( array (
					'cdl.content_data_id' => $data_id,
					'cdd.system_language_id' => $language_id,
					'cdd.content_label_detail_delete_status' => '0' 
			) );
			$QDataLinkLabel->execute ();
			if ($QDataLinkLabel->hasResult ()) {
				while ( $QDataLinkLabel->valid () ) {
					$rawdata = $QDataLinkLabel->current ();
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_to_data_id'] );
					$rawdata ['content_label_to_data_id_modify'] = $cryptID;
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_id'] );
					$rawdata ['modify_value'] = $cryptID;
					
					$rawdata ['content_label_to_data_created_date_format'] = "";
					if ($rawdata ['content_label_to_data_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_to_data_created_date'] );
						$rawdata ['content_label_to_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_label_to_data_modified_date_format'] = "";
					if ($rawdata ['content_label_to_data_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_to_data_modified_date'] );
						$rawdata ['content_label_to_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$this->content_label_to_data [$rawdata ['content_label_to_data_id']] = $rawdata;
					$QDataLinkLabel->next ();
				}
			}
		}
		return $this->content_label_to_data;
	}
	
	/**
	 * Reset Content Label Link
	 */
	public function resetDataLinkLabel() {
		$this->content_label_to_data = null;
	}
	
	/**
	 * Update Content Data Detail
	 */
	public function updateDataLinkLabel($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteDataLinkLabel ()) {
				if ($this->createDataLinkLabel ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Label Link
	 */
	public function createDataLinkLabel($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$link = array ();
			$data_id = $this->getContentDataID ();
			foreach ( $data as $rawdata ) {
				$label_id = (array_key_exists ( 'content_data_label', $rawdata ) ? $this->Decrypt ( $rawdata ['content_data_label'] ) : 0);
				$link [] = array (
						'content_label_id' => $label_id,
						'content_data_id' => $data_id,
						'content_label_to_data_created_date' => $rawdata ['log_created_date'],
						'content_label_to_data_created_by' => $rawdata ['log_created_by'],
						'content_label_to_data_modified_date' => $rawdata ['log_modified_date'],
						'content_label_to_data_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $link ) && count ( $link ) > 0) {
				$status = true;
				$IDataLinkLabel = $this->getDatabase ();
				$IDataLinkLabel->insert ();
				$IDataLinkLabel->into ( 'content_label_to_data' );
				$IDataLinkLabel->columns ( array (
						'content_label_id',
						'content_data_id',
						'content_label_to_data_created_date',
						'content_label_to_data_created_by',
						'content_label_to_data_modified_date',
						'content_label_to_data_modified_by' 
				) );
				$IDataLinkLabel->values ( $link, 'multiple' );
				$IDataLinkLabel->execute ();
				if (! $IDataLinkLabel->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Label Link
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataLinkLabelID($id) {
		$label_id = $this->getContentLabelID ();
		$data_id = $this->getContentDataID ();
		if (! empty ( $id )) {
			$VDataLinkLabel = $this->getDatabase ();
			$VDataLinkLabel->select ();
			$VDataLinkLabel->columns ( array (
					'id' => 'content_label_to_data_id' 
			) );
			$VDataLinkLabel->from ( array (
					'cdl' => 'content_label_to_data' 
			) );
			$where = array (
					'cdl.content_label_to_data_id = ' . $label_id,
					'cdl.content_data_id = ' . $data_id 
			);
			$VDataLinkLabel->where ( $where );
			$VDataLinkLabel->limit ( 1 );
			$VDataLinkLabel->execute ();
			if ($VDataLinkLabel->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Label Link
	 *
	 * @return Boolean
	 *
	 */
	public function deleteDataLinkLabel($forever = false) {
		$data_id = $this->getContentDataID ();
		if (! empty ( $data_id )) {
			if ($forever) {
				$DDataLinkLabel = $this->getDatabase ();
				$DDataLinkLabel->delete ();
				$DDataLinkLabel->from ( 'content_label_to_data' );
				$where = array (
						'content_data_id = ' . $data_id 
				);
				$DDataLinkLabel->where ( $where );
				$DDataLinkLabel->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataLinkLabel = $this->getDatabase ();
				$UDataLinkLabel->update ();
				$UDataLinkLabel->table ( 'content_label_to_data' );
				$UDataLinkLabel->set ( array (
						'content_label_to_data_delete_status' => '1',
						'content_label_to_data_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_label_to_data_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataLinkLabel->where ( array (
						'content_data_id' => $data_id 
				) );
				$UDataLinkLabel->execute ();
				return true;
			}
		}
		return false;
	}
}

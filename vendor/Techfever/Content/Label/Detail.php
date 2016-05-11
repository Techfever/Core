<?php

namespace Techfever\Content\Label;

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
			'label_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Label Detail
	 *     
	 */
	private $content_label_detail = null;
	
	/**
	 * Initial Content Label Detail
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
	 * Get Content Label Detail
	 *
	 * @return array()
	 */
	public function getLabelDetail() {
		if (! is_array ( $this->content_label_detail ) || count ( $this->content_label_detail ) < 1) {
			$label_id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QLabelDetail = $this->getDatabase ();
			$QLabelDetail->select ();
			$QLabelDetail->columns ( array (
					'content_label_detail_id',
					'system_language_id',
					'content_label_detail_title',
					'content_label_detail_information',
					'content_label_detail_created_date',
					'content_label_detail_created_by',
					'content_label_detail_modified_date',
					'content_label_detail_modified_by' 
			) );
			$QLabelDetail->from ( array (
					'cdd' => 'content_label_detail' 
			) );
			$QLabelDetail->where ( array (
					'cdd.content_label_id' => $label_id,
					'cdd.content_label_detail_delete_status' => '0' 
			) );
			$QLabelDetail->execute ();
			if ($QLabelDetail->hasResult ()) {
				while ( $QLabelDetail->valid () ) {
					$rawdata = $QLabelDetail->current ();
					
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
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_detail_id'] );
					$rawdata ['content_label_detail_id_modify'] = $cryptID;
					
					$this->content_label_detail [$rawdata ['system_language_id']] = $rawdata;
					$QLabelDetail->next ();
				}
			}
		}
		return $this->content_label_detail;
	}
	
	/**
	 * Reset Content Label Detail
	 */
	public function resetLabelDetail() {
		$this->content_label_detail = null;
	}
	
	/**
	 * Update Content Label Detail
	 */
	public function updateLabelDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteLabelDetail ()) {
				if ($this->createLabelDetail ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Label Detail
	 */
	public function createLabelDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$detail = array ();
			$label_id = $this->getContentLabelID ();
			foreach ( $data as $rawdata ) {
				$system_language_id = (array_key_exists ( 'system_language_id', $rawdata ) ? $rawdata ['system_language_id'] : 0);
				$content_label_detail_title = (array_key_exists ( 'content_label_detail_title', $rawdata ) ? $rawdata ['content_label_detail_title'] : null);
				$content_label_detail_information = (array_key_exists ( 'content_label_detail_information', $rawdata ) ? $rawdata ['content_label_detail_information'] : null);
				$detail [] = array (
						'content_label_id' => $label_id,
						'system_language_id' => $system_language_id,
						'content_label_detail_title' => $content_label_detail_title,
						'content_label_detail_information' => $content_label_detail_information,
						'content_label_detail_created_date' => $rawdata ['log_created_date'],
						'content_label_detail_created_by' => $rawdata ['log_created_by'],
						'content_label_detail_modified_date' => $rawdata ['log_modified_date'],
						'content_label_detail_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $detail ) && count ( $detail ) > 0) {
				$status = true;
				$ILabelDetail = $this->getDatabase ();
				$ILabelDetail->insert ();
				$ILabelDetail->into ( 'content_label_detail' );
				$ILabelDetail->columns ( array (
						'content_label_id',
						'system_language_id',
						'content_label_detail_title',
						'content_label_detail_information',
						'content_label_detail_created_date',
						'content_label_detail_created_by',
						'content_label_detail_modified_date',
						'content_label_detail_modified_by' 
				) );
				$ILabelDetail->values ( $detail, 'multiple' );
				$ILabelDetail->execute ();
				if (! $ILabelDetail->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Label Detail
	 *
	 * @return Boolean
	 *
	 */
	public function verifyLabelDetailID($id) {
		$label_id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			$VLabelDetail = $this->getDatabase ();
			$VLabelDetail->select ();
			$VLabelDetail->columns ( array (
					'id' => 'content_label_detail_id' 
			) );
			$VLabelDetail->from ( array (
					'cdd' => 'content_label_detail' 
			) );
			$where = array (
					'cdd.content_label_detail_id = ' . $id,
					'cdd.content_label_id = ' . $label_id 
			);
			$VLabelDetail->where ( $where );
			$VLabelDetail->limit ( 1 );
			$VLabelDetail->execute ();
			if ($VLabelDetail->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Label Detail
	 *
	 * @return Boolean
	 *
	 */
	public function deleteLabelDetail($forever = false) {
		$id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DLabelDetail = $this->getDatabase ();
				$DLabelDetail->delete ();
				$DLabelDetail->from ( 'content_label_detail' );
				$where = array (
						'content_label_id = ' . $id 
				);
				$DLabelDetail->where ( $where );
				$DLabelDetail->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$ULabelDetail = $this->getDatabase ();
				$ULabelDetail->update ();
				$ULabelDetail->table ( 'content_label_detail' );
				$ULabelDetail->set ( array (
						'content_label_detail_delete_status' => '1',
						'content_label_detail_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_label_detail_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$ULabelDetail->where ( array (
						'content_label_id' => $id 
				) );
				$ULabelDetail->execute ();
				return true;
			}
		}
		return false;
	}
}

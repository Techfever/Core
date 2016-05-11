<?php

namespace Techfever\Content\Label;

use Techfever\Exception;

class Url extends Link {
	
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
	 * @var Content Label Url
	 *     
	 */
	private $content_label_url = null;
	
	/**
	 * Initial Content Label Url
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
	 * Get Content Label Url
	 *
	 * @return array()
	 */
	public function getLabelUrl() {
		if (! is_array ( $this->content_label_url ) || count ( $this->content_label_url ) < 1) {
			$label_id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QLabelUrl = $this->getDatabase ();
			$QLabelUrl->select ();
			$QLabelUrl->columns ( array (
					'content_label_url_id',
					'content_label_url_keyword',
					'content_label_url_created_date',
					'content_label_url_created_by',
					'content_label_url_modified_date',
					'content_label_url_modified_by' 
			) );
			$QLabelUrl->from ( array (
					'cdu' => 'content_label_url' 
			) );
			$QLabelUrl->where ( array (
					'cdu.content_label_id' => $label_id,
					'cdu.content_label_url_delete_status' => '0' 
			) );
			$QLabelUrl->limit ( 1 );
			$QLabelUrl->execute ();
			if ($QLabelUrl->hasResult ()) {
				while ( $QLabelUrl->valid () ) {
					$rawdata = $QLabelUrl->current ();
					
					$rawdata ['content_label_url_created_date_format'] = "";
					if ($rawdata ['content_label_url_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_url_created_date'] );
						$rawdata ['content_label_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_label_url_modified_date_format'] = "";
					if ($rawdata ['content_label_url_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_url_modified_date'] );
						$rawdata ['content_label_url_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_url_id'] );
					$rawdata ['content_label_url_id_modify'] = $cryptID;
					
					$this->content_label_url = $rawdata;
					$QLabelUrl->next ();
				}
			}
		}
		return $this->content_label_url;
	}
	
	/**
	 * Reset Content Label Url
	 */
	public function resetLabelUrl() {
		$this->content_label_url = null;
	}
	
	/**
	 * Update Content Label Url
	 */
	public function updateLabelUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteLabelUrl ()) {
				if ($this->createLabelUrl ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Label Url
	 */
	public function createLabelUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$label_id = $this->getContentLabelID ();
			$ILabelUrl = $this->getDatabase ();
			$ILabelUrl->insert ();
			$ILabelUrl->into ( 'content_label_url' );
			$ILabelUrl->values ( array (
					'content_label_id' => $label_id,
					'content_label_url_keyword' => $data ['content_label_url_keyword'],
					'content_label_url_created_date' => $data ['log_created_date'],
					'content_label_url_created_by' => $data ['log_created_by'],
					'content_label_url_modified_date' => $data ['log_modified_date'],
					'content_label_url_modified_by' => $data ['log_modified_by'] 
			) );
			$ILabelUrl->execute ();
			if ($ILabelUrl->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Label Url
	 *
	 * @return Boolean
	 *
	 */
	public function verifyLabelUrlID($id) {
		$label_id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			$VLabelUrl = $this->getDatabase ();
			$VLabelUrl->select ();
			$VLabelUrl->columns ( array (
					'id' => 'content_label_url_id' 
			) );
			$VLabelUrl->from ( array (
					'cdu' => 'content_label_url' 
			) );
			$where = array (
					'cdu.content_label_url_id = ' . $id,
					'cdu.content_label_id = ' . $label_id 
			);
			$VLabelUrl->where ( $where );
			$VLabelUrl->limit ( 1 );
			$VLabelUrl->execute ();
			if ($VLabelUrl->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Label Url
	 *
	 * @return Boolean
	 *
	 */
	public function deleteLabelUrl($forever = false) {
		$id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DLabelUrl = $this->getDatabase ();
				$DLabelUrl->delete ();
				$DLabelUrl->from ( 'content_label_url' );
				$where = array (
						'content_label_id = ' . $id 
				);
				$DLabelUrl->where ( $where );
				$DLabelUrl->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$ULabelUrl = $this->getDatabase ();
				$ULabelUrl->update ();
				$ULabelUrl->table ( 'content_label_url' );
				$ULabelUrl->set ( array (
						'content_label_url_delete_status' => '1',
						'content_label_url_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_label_url_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$ULabelUrl->where ( array (
						'content_label_id' => $id 
				) );
				$ULabelUrl->execute ();
				return true;
			}
		}
		return false;
	}
}

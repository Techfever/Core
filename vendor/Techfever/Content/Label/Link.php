<?php

namespace Techfever\Content\Label;

use Techfever\Exception;
use Techfever\Content\Type;

class Link extends Type {
	
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
	public function getLabelLink() {
		if (! is_array ( $this->content_label_to_data ) || count ( $this->content_label_to_data ) < 1) {
			$label_id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QLabelLink = $this->getDatabase ();
			$QLabelLink->select ();
			$QLabelLink->columns ( array (
					'content_label_to_data_id',
					'content_label_id',
					'content_label_id' 
			) );
			$QLabelLink->from ( array (
					'cdl' => 'content_label_to_data' 
			) );
			$QLabelLink->where ( array (
					'cdl.content_label_id' => $label_id 
			) );
			$QLabelLink->limit ( 1 );
			$QLabelLink->execute ();
			if ($QLabelLink->hasResult ()) {
				while ( $QLabelLink->valid () ) {
					$rawdata = $QLabelLink->current ();
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_to_data_id'] );
					$rawdata ['content_label_to_data_id_modify'] = $cryptID;
					
					$this->content_label_to_data [$rawdata ['id']] = $rawdata;
					$QLabelLink->next ();
				}
			}
		}
		return $this->content_label_to_data;
	}
	
	/**
	 * Reset Content Label Link
	 */
	public function resetLabelLink() {
		$this->content_label_to_data = null;
	}
	
	/**
	 * Create Content Label Link
	 */
	public function createLabelLink($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$link = array ();
			$label_id = $this->getContentLabelID ();
			foreach ( $data as $rawdata ) {
				$content_label_id = (array_key_exists ( 'content_label_id', $rawdata ) ? $rawdata ['content_label_id'] : 0);
				$link [] = array (
						'content_label_id' => $label_id,
						'content_label_id' => $content_label_id 
				);
			}
			if (is_array ( $link ) && count ( $link ) > 0) {
				$status = true;
				$ILabelLink = $this->getDatabase ();
				$ILabelLink->insert ();
				$ILabelLink->into ( 'content_label_to_data' );
				$ILabelLink->columns ( array (
						'content_label_id',
						'content_label_id' 
				) );
				$ILabelLink->values ( $link, 'multiple' );
				$ILabelLink->execute ();
				if (! $ILabelLink->affectedRows ()) {
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
	public function verifyLabelLinkID($id) {
		$label_id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			$VLabelLink = $this->getDatabase ();
			$VLabelLink->select ();
			$VLabelLink->columns ( array (
					'id' => 'content_label_to_data_id' 
			) );
			$VLabelLink->from ( array (
					'cdl' => 'content_label_to_data' 
			) );
			$where = array (
					'cdl.content_label_to_data_id = ' . $id,
					'cdl.content_label_id = ' . $label_id 
			);
			$VLabelLink->where ( $where );
			$VLabelLink->limit ( 1 );
			$VLabelLink->execute ();
			if ($VLabelLink->hasResult ()) {
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
	public function deleteLabelLink($forever = false) {
		$id = $this->getContentLabelID ();
		if (! empty ( $id )) {
			$DLabelLink = $this->getDatabase ();
			$DLabelLink->delete ();
			$DLabelLink->from ( 'content_label_to_data' );
			$where = array (
					'content_label_id = ' . $id 
			);
			$DLabelLink->where ( $where );
			$DLabelLink->execute ();
			return true;
		}
		return false;
	}
}

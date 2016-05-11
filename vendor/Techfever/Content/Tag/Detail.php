<?php

namespace Techfever\Content\Tag;

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
			'tag_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Tag Detail
	 *     
	 */
	private $content_tag_detail = null;
	
	/**
	 * Initial Content Tag Detail
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
	 * Get Content Tag Detail
	 *
	 * @return array()
	 */
	public function getTagDetail() {
		if (! is_array ( $this->content_tag_detail ) || count ( $this->content_tag_detail ) < 1) {
			$tag_id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QTagDetail = $this->getDatabase ();
			$QTagDetail->select ();
			$QTagDetail->columns ( array (
					'content_tag_detail_id',
					'system_language_id',
					'content_tag_detail_title',
					'content_tag_detail_created_date',
					'content_tag_detail_created_by',
					'content_tag_detail_modified_date',
					'content_tag_detail_modified_by' 
			) );
			$QTagDetail->from ( array (
					'cdd' => 'content_tag_detail' 
			) );
			$QTagDetail->where ( array (
					'cdd.content_tag_id' => $tag_id,
					'cdd.content_tag_detail_delete_status' => '0' 
			) );
			$QTagDetail->execute ();
			if ($QTagDetail->hasResult ()) {
				while ( $QTagDetail->valid () ) {
					$rawdata = $QTagDetail->current ();
					
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
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_detail_id'] );
					$rawdata ['content_tag_detail_id_modify'] = $cryptID;
					
					$this->content_tag_detail [$rawdata ['system_language_id']] = $rawdata;
					$QTagDetail->next ();
				}
			}
		}
		return $this->content_tag_detail;
	}
	
	/**
	 * Reset Content Tag Detail
	 */
	public function resetTagDetail() {
		$this->content_tag_detail = null;
	}
	
	/**
	 * Update Content Tag Detail
	 */
	public function updateTagDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteTagDetail ()) {
				if ($this->createTagDetail ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Tag Detail
	 */
	public function createTagDetail($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$detail = array ();
			$tag_id = $this->getContentTagID ();
			foreach ( $data as $rawdata ) {
				$system_language_id = (array_key_exists ( 'system_language_id', $rawdata ) ? $rawdata ['system_language_id'] : 0);
				$content_tag_detail_title = (array_key_exists ( 'content_tag_detail_title', $rawdata ) ? $rawdata ['content_tag_detail_title'] : null);
				$detail [] = array (
						'content_tag_id' => $tag_id,
						'system_language_id' => $system_language_id,
						'content_tag_detail_title' => $content_tag_detail_title,
						'content_tag_detail_created_date' => $rawdata ['log_created_date'],
						'content_tag_detail_created_by' => $rawdata ['log_created_by'],
						'content_tag_detail_modified_date' => $rawdata ['log_modified_date'],
						'content_tag_detail_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $detail ) && count ( $detail ) > 0) {
				$status = true;
				$ITagDetail = $this->getDatabase ();
				$ITagDetail->insert ();
				$ITagDetail->into ( 'content_tag_detail' );
				$ITagDetail->columns ( array (
						'content_tag_id',
						'system_language_id',
						'content_tag_detail_title',
						'content_tag_detail_created_date',
						'content_tag_detail_created_by',
						'content_tag_detail_modified_date',
						'content_tag_detail_modified_by' 
				) );
				$ITagDetail->values ( $detail, 'multiple' );
				$ITagDetail->execute ();
				if (! $ITagDetail->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Tag Detail
	 *
	 * @return Boolean
	 *
	 */
	public function verifyTagDetailID($id) {
		$tag_id = $this->getContentTagID ();
		if (! empty ( $id )) {
			$VTagDetail = $this->getDatabase ();
			$VTagDetail->select ();
			$VTagDetail->columns ( array (
					'id' => 'content_tag_detail_id' 
			) );
			$VTagDetail->from ( array (
					'cdd' => 'content_tag_detail' 
			) );
			$where = array (
					'cdd.content_tag_detail_id = ' . $id,
					'cdd.content_tag_id = ' . $tag_id 
			);
			$VTagDetail->where ( $where );
			$VTagDetail->limit ( 1 );
			$VTagDetail->execute ();
			if ($VTagDetail->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Tag Detail
	 *
	 * @return Boolean
	 *
	 */
	public function deleteTagDetail($forever = false) {
		$id = $this->getContentTagID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DTagDetail = $this->getDatabase ();
				$DTagDetail->delete ();
				$DTagDetail->from ( 'content_tag_detail' );
				$where = array (
						'content_tag_id = ' . $id 
				);
				$DTagDetail->where ( $where );
				$DTagDetail->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UTagDetail = $this->getDatabase ();
				$UTagDetail->update ();
				$UTagDetail->table ( 'content_tag_detail' );
				$UTagDetail->set ( array (
						'content_tag_detail_delete_status' => '1',
						'content_tag_detail_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_tag_detail_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UTagDetail->where ( array (
						'content_tag_id' => $id 
				) );
				$UTagDetail->execute ();
				return true;
			}
		}
		return false;
	}
}

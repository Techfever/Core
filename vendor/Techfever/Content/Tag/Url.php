<?php

namespace Techfever\Content\Tag;

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
			'tag_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Tag Url
	 *     
	 */
	private $content_tag_url = null;
	
	/**
	 * Initial Content Tag Url
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
	 * Get Content Tag Url
	 *
	 * @return array()
	 */
	public function getTagUrl() {
		if (! is_array ( $this->content_tag_url ) || count ( $this->content_tag_url ) < 1) {
			$tag_id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QTagUrl = $this->getDatabase ();
			$QTagUrl->select ();
			$QTagUrl->columns ( array (
					'content_tag_url_id',
					'content_tag_url_keyword',
					'content_tag_url_created_date',
					'content_tag_url_created_by',
					'content_tag_url_modified_date',
					'content_tag_url_modified_by' 
			) );
			$QTagUrl->from ( array (
					'cdu' => 'content_tag_url' 
			) );
			$QTagUrl->where ( array (
					'cdu.content_tag_id' => $tag_id,
					'cdu.content_tag_url_delete_status' => '0' 
			) );
			$QTagUrl->limit ( 1 );
			$QTagUrl->execute ();
			if ($QTagUrl->hasResult ()) {
				while ( $QTagUrl->valid () ) {
					$rawdata = $QTagUrl->current ();
					
					$rawdata ['content_tag_url_created_date_format'] = "";
					if ($rawdata ['content_tag_url_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_url_created_date'] );
						$rawdata ['content_tag_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_tag_url_modified_date_format'] = "";
					if ($rawdata ['content_tag_url_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_url_modified_date'] );
						$rawdata ['content_tag_url_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_url_id'] );
					$rawdata ['content_tag_url_id_modify'] = $cryptID;
					
					$this->content_tag_url = $rawdata;
					$QTagUrl->next ();
				}
			}
		}
		return $this->content_tag_url;
	}
	
	/**
	 * Reset Content Tag Url
	 */
	public function resetTagUrl() {
		$this->content_tag_url = null;
	}
	
	/**
	 * Update Content Tag Url
	 */
	public function updateTagUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteTagUrl ()) {
				if ($this->createTagUrl ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Tag Url
	 */
	public function createTagUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$tag_id = $this->getContentTagID ();
			$ITagUrl = $this->getDatabase ();
			$ITagUrl->insert ();
			$ITagUrl->into ( 'content_tag_url' );
			$ITagUrl->values ( array (
					'content_tag_id' => $tag_id,
					'content_tag_url_keyword' => $data ['content_tag_url_keyword'],
					'content_tag_url_created_date' => $data ['log_created_date'],
					'content_tag_url_created_by' => $data ['log_created_by'],
					'content_tag_url_modified_date' => $data ['log_modified_date'],
					'content_tag_url_modified_by' => $data ['log_modified_by'] 
			) );
			$ITagUrl->execute ();
			if ($ITagUrl->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Tag Url
	 *
	 * @return Boolean
	 *
	 */
	public function verifyTagUrlID($id) {
		$tag_id = $this->getContentTagID ();
		if (! empty ( $id )) {
			$VTagUrl = $this->getDatabase ();
			$VTagUrl->select ();
			$VTagUrl->columns ( array (
					'id' => 'content_tag_url_id' 
			) );
			$VTagUrl->from ( array (
					'cdu' => 'content_tag_url' 
			) );
			$where = array (
					'cdu.content_tag_url_id = ' . $id,
					'cdu.content_tag_id = ' . $tag_id 
			);
			$VTagUrl->where ( $where );
			$VTagUrl->limit ( 1 );
			$VTagUrl->execute ();
			if ($VTagUrl->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Tag Url
	 *
	 * @return Boolean
	 *
	 */
	public function deleteTagUrl($forever = false) {
		$id = $this->getContentTagID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DTagUrl = $this->getDatabase ();
				$DTagUrl->delete ();
				$DTagUrl->from ( 'content_tag_url' );
				$where = array (
						'content_tag_id = ' . $id 
				);
				$DTagUrl->where ( $where );
				$DTagUrl->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UTagUrl = $this->getDatabase ();
				$UTagUrl->update ();
				$UTagUrl->table ( 'content_tag_url' );
				$UTagUrl->set ( array (
						'content_tag_url_delete_status' => '1',
						'content_tag_url_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_tag_url_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UTagUrl->where ( array (
						'content_tag_id' => $id 
				) );
				$UTagUrl->execute ();
				return true;
			}
		}
		return false;
	}
}

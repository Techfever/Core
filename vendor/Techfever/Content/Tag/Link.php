<?php

namespace Techfever\Content\Tag;

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
			'tag_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Tag Link
	 *     
	 */
	private $content_tag_to_data = null;
	
	/**
	 * Initial Content Tag Link
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
	 * Get Content Tag Link
	 *
	 * @return array()
	 */
	public function getTagLink() {
		if (! is_array ( $this->content_tag_to_data ) || count ( $this->content_tag_to_data ) < 1) {
			$tag_id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QTagLink = $this->getDatabase ();
			$QTagLink->select ();
			$QTagLink->columns ( array (
					'content_tag_to_data_id',
					'content_tag_id',
					'content_tag_id' 
			) );
			$QTagLink->from ( array (
					'cdl' => 'content_tag_to_data' 
			) );
			$QTagLink->where ( array (
					'cdl.content_tag_id' => $tag_id 
			) );
			$QTagLink->limit ( 1 );
			$QTagLink->execute ();
			if ($QTagLink->hasResult ()) {
				while ( $QTagLink->valid () ) {
					$rawdata = $QTagLink->current ();
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_to_data_id'] );
					$rawdata ['content_tag_to_data_id_modify'] = $cryptID;
					
					$this->content_tag_to_data [$rawdata ['id']] = $rawdata;
					$QTagLink->next ();
				}
			}
		}
		return $this->content_tag_to_data;
	}
	
	/**
	 * Reset Content Tag Link
	 */
	public function resetTagLink() {
		$this->content_tag_to_data = null;
	}
	
	/**
	 * Create Content Tag Link
	 */
	public function createTagLink($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$link = array ();
			$tag_id = $this->getContentTagID ();
			foreach ( $data as $rawdata ) {
				$content_tag_id = (array_key_exists ( 'content_tag_id', $rawdata ) ? $rawdata ['content_tag_id'] : 0);
				$link [] = array (
						'content_tag_id' => $tag_id,
						'content_tag_id' => $content_tag_id 
				);
			}
			if (is_array ( $link ) && count ( $link ) > 0) {
				$status = true;
				$ITagLink = $this->getDatabase ();
				$ITagLink->insert ();
				$ITagLink->into ( 'content_tag_to_data' );
				$ITagLink->columns ( array (
						'content_tag_id',
						'content_tag_id' 
				) );
				$ITagLink->values ( $link, 'multiple' );
				$ITagLink->execute ();
				if (! $ITagLink->affectedRows ()) {
					$status = false;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Tag Link
	 *
	 * @return Boolean
	 *
	 */
	public function verifyTagLinkID($id) {
		$tag_id = $this->getContentTagID ();
		if (! empty ( $id )) {
			$VTagLink = $this->getDatabase ();
			$VTagLink->select ();
			$VTagLink->columns ( array (
					'id' => 'content_tag_to_data_id' 
			) );
			$VTagLink->from ( array (
					'cdl' => 'content_tag_to_data' 
			) );
			$where = array (
					'cdl.content_tag_to_data_id = ' . $id,
					'cdl.content_tag_id = ' . $tag_id 
			);
			$VTagLink->where ( $where );
			$VTagLink->limit ( 1 );
			$VTagLink->execute ();
			if ($VTagLink->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Tag Link
	 *
	 * @return Boolean
	 *
	 */
	public function deleteTagLink($forever = false) {
		$id = $this->getContentTagID ();
		if (! empty ( $id )) {
			$DTagLink = $this->getDatabase ();
			$DTagLink->delete ();
			$DTagLink->from ( 'content_tag_to_data' );
			$where = array (
					'content_tag_id = ' . $id 
			);
			$DTagLink->where ( $where );
			$DTagLink->execute ();
			return true;
		}
		return false;
	}
}

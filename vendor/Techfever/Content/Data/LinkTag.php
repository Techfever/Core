<?php

namespace Techfever\Content\Data;

use Techfever\Exception;
use Techfever\Content\Type;

class LinkTag extends Type {
	
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
	public function getDataLinkTag() {
		if (! is_array ( $this->content_tag_to_data ) || count ( $this->content_tag_to_data ) < 1) {
			$data_id = $this->getContentDataID ();
			$language_id = $this->getContentLanguageID ();
			$rawdata = array ();
			$QDataLinkTag = $this->getDatabase ();
			$QDataLinkTag->select ();
			$QDataLinkTag->columns ( array (
					'content_tag_to_data_id',
					'content_data_id',
					'content_tag_id',
					'content_tag_to_data_created_date',
					'content_tag_to_data_created_by',
					'content_tag_to_data_modified_date',
					'content_tag_to_data_modified_by' 
			) );
			$QDataLinkTag->from ( array (
					'cdl' => 'content_tag_to_data' 
			) );
			$QDataLinkTag->join ( array (
					'cdd' => 'content_tag_detail' 
			), 'cdd.content_tag_id  = cdl.content_tag_id', array (
					'content_tag_detail_id',
					'system_language_id',
					'content_tag_detail_title' 
			) );
			$QDataLinkTag->where ( array (
					'cdl.content_data_id' => $data_id,
					'cdd.system_language_id' => $language_id,
					'cdd.content_tag_detail_delete_status' => '0' 
			) );
			$QDataLinkTag->execute ();
			if ($QDataLinkTag->hasResult ()) {
				while ( $QDataLinkTag->valid () ) {
					$rawdata = $QDataLinkTag->current ();
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_to_data_id'] );
					$rawdata ['content_tag_to_data_id_modify'] = $cryptID;
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_id'] );
					$rawdata ['modify_value'] = $cryptID;
					
					$rawdata ['content_tag_to_data_created_date_format'] = "";
					if ($rawdata ['content_tag_to_data_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_to_data_created_date'] );
						$rawdata ['content_tag_to_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_tag_to_data_modified_date_format'] = "";
					if ($rawdata ['content_tag_to_data_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_to_data_modified_date'] );
						$rawdata ['content_tag_to_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$this->content_tag_to_data [$rawdata ['content_tag_to_data_id']] = $rawdata;
					$QDataLinkTag->next ();
				}
			}
		}
		return $this->content_tag_to_data;
	}
	
	/**
	 * Reset Content Tag Link
	 */
	public function resetDataLinkTag() {
		$this->content_tag_to_data = null;
	}
	
	/**
	 * Update Content Data Detail
	 */
	public function updateDataLinkTag($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteDataLinkTag ()) {
				if ($this->createDataLinkTag ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Tag Link
	 */
	public function createDataLinkTag($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$link = array ();
			$data_id = $this->getContentDataID ();
			foreach ( $data as $rawdata ) {
				$tag_id = (array_key_exists ( 'content_data_tag', $rawdata ) ? $this->Decrypt ( $rawdata ['content_data_tag'] ) : 0);
				$link [] = array (
						'content_tag_id' => $tag_id,
						'content_data_id' => $data_id,
						'content_tag_to_data_created_date' => $rawdata ['log_created_date'],
						'content_tag_to_data_created_by' => $rawdata ['log_created_by'],
						'content_tag_to_data_modified_date' => $rawdata ['log_modified_date'],
						'content_tag_to_data_modified_by' => $rawdata ['log_modified_by'] 
				);
			}
			if (is_array ( $link ) && count ( $link ) > 0) {
				$status = true;
				$IDataLinkTag = $this->getDatabase ();
				$IDataLinkTag->insert ();
				$IDataLinkTag->into ( 'content_tag_to_data' );
				$IDataLinkTag->columns ( array (
						'content_tag_id',
						'content_data_id',
						'content_tag_to_data_created_date',
						'content_tag_to_data_created_by',
						'content_tag_to_data_modified_date',
						'content_tag_to_data_modified_by' 
				) );
				$IDataLinkTag->values ( $link, 'multiple' );
				$IDataLinkTag->execute ();
				if (! $IDataLinkTag->affectedRows ()) {
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
	public function verifyDataLinkTagID($id) {
		$tag_id = $this->getContentTagID ();
		$data_id = $this->getContentDataID ();
		if (! empty ( $id )) {
			$VDataLinkTag = $this->getDatabase ();
			$VDataLinkTag->select ();
			$VDataLinkTag->columns ( array (
					'id' => 'content_tag_to_data_id' 
			) );
			$VDataLinkTag->from ( array (
					'cdl' => 'content_tag_to_data' 
			) );
			$where = array (
					'cdl.content_tag_to_data_id = ' . $tag_id,
					'cdl.content_data_id = ' . $data_id 
			);
			$VDataLinkTag->where ( $where );
			$VDataLinkTag->limit ( 1 );
			$VDataLinkTag->execute ();
			if ($VDataLinkTag->hasResult ()) {
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
	public function deleteDataLinkTag($forever = false) {
		$data_id = $this->getContentDataID ();
		if (! empty ( $data_id )) {
			if ($forever) {
				$DDataLinkTag = $this->getDatabase ();
				$DDataLinkTag->delete ();
				$DDataLinkTag->from ( 'content_tag_to_data' );
				$where = array (
						'content_data_id = ' . $data_id 
				);
				$DDataLinkTag->where ( $where );
				$DDataLinkTag->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataLinkTag = $this->getDatabase ();
				$UDataLinkTag->update ();
				$UDataLinkTag->table ( 'content_tag_to_data' );
				$UDataLinkTag->set ( array (
						'content_tag_to_data_delete_status' => '1',
						'content_tag_to_data_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_tag_to_data_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataLinkTag->where ( array (
						'content_data_id' => $data_id 
				) );
				$UDataLinkTag->execute ();
				return true;
			}
		}
		return false;
	}
}

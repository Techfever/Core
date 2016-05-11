<?php

namespace Techfever\Content\Data;

use Techfever\Exception;

class Url extends LinkLabel {
	
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
	 * @var Content Data Url
	 *     
	 */
	private $content_data_url = null;
	
	/**
	 * Initial Content Data Url
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
	 * Get Content Data Url
	 *
	 * @return array()
	 */
	public function getDataUrl() {
		if (! is_array ( $this->content_data_url ) || count ( $this->content_data_url ) < 1) {
			$data_id = $this->getContentDataID ();
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QDataUrl = $this->getDatabase ();
			$QDataUrl->select ();
			$QDataUrl->columns ( array (
					'content_data_url_id',
					'content_data_url_keyword',
					'content_data_url_created_date',
					'content_data_url_created_by',
					'content_data_url_modified_date',
					'content_data_url_modified_by' 
			) );
			$QDataUrl->from ( array (
					'cdu' => 'content_data_url' 
			) );
			$QDataUrl->where ( array (
					'cdu.content_data_id' => $data_id,
					'cdu.content_data_url_delete_status' => '0' 
			) );
			$QDataUrl->limit ( 1 );
			$QDataUrl->execute ();
			if ($QDataUrl->hasResult ()) {
				while ( $QDataUrl->valid () ) {
					$rawdata = $QDataUrl->current ();
					
					$rawdata ['content_data_url_created_date_format'] = "";
					if ($rawdata ['content_data_url_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_url_created_date'] );
						$rawdata ['content_data_url_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_data_url_modified_date_format'] = "";
					if ($rawdata ['content_data_url_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_url_modified_date'] );
						$rawdata ['content_data_url_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$cryptID = $this->Encrypt ( $rawdata ['content_data_url_id'] );
					$rawdata ['content_data_url_id_modify'] = $cryptID;
					
					$this->content_data_url = $rawdata;
					$QDataUrl->next ();
				}
			}
		}
		return $this->content_data_url;
	}
	
	/**
	 * Reset Content Data Url
	 */
	public function resetDataUrl() {
		$this->content_data_url = null;
	}
	
	/**
	 * Update Content Data Url
	 */
	public function updateDataUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			if ($this->deleteDataUrl ()) {
				if ($this->createDataUrl ( $data )) {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Create Content Data Url
	 */
	public function createDataUrl($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$data_id = $this->getContentDataID ();
			$IDataUrl = $this->getDatabase ();
			$IDataUrl->insert ();
			$IDataUrl->into ( 'content_data_url' );
			$IDataUrl->values ( array (
					'content_data_id' => $data_id,
					'content_data_url_keyword' => $data ['content_data_url_keyword'],
					'content_data_url_created_date' => $data ['log_created_date'],
					'content_data_url_created_by' => $data ['log_created_by'],
					'content_data_url_modified_date' => $data ['log_modified_date'],
					'content_data_url_modified_by' => $data ['log_modified_by'] 
			) );
			$IDataUrl->execute ();
			if ($IDataUrl->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Data Url
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataUrlID($id) {
		$data_id = $this->getContentDataID ();
		if (! empty ( $id )) {
			$VDataUrl = $this->getDatabase ();
			$VDataUrl->select ();
			$VDataUrl->columns ( array (
					'id' => 'content_data_url_id' 
			) );
			$VDataUrl->from ( array (
					'cdu' => 'content_data_url' 
			) );
			$where = array (
					'cdu.content_data_url_id = ' . $id,
					'cdu.content_data_id = ' . $data_id 
			);
			$VDataUrl->where ( $where );
			$VDataUrl->limit ( 1 );
			$VDataUrl->execute ();
			if ($VDataUrl->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Data Url
	 *
	 * @return Boolean
	 *
	 */
	public function deleteDataUrl($forever = false) {
		$id = $this->getContentDataID ();
		if (! empty ( $id )) {
			if ($forever) {
				$DDataUrl = $this->getDatabase ();
				$DDataUrl->delete ();
				$DDataUrl->from ( 'content_data_url' );
				$where = array (
						'content_data_id = ' . $id 
				);
				$DDataUrl->where ( $where );
				$DDataUrl->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UDataUrl = $this->getDatabase ();
				$UDataUrl->update ();
				$UDataUrl->table ( 'content_data_url' );
				$UDataUrl->set ( array (
						'content_data_url_delete_status' => '1',
						'content_data_url_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_data_url_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$UDataUrl->where ( array (
						'content_data_id' => $id 
				) );
				$UDataUrl->execute ();
				return true;
			}
		}
		return false;
	}
}

<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Status extends Configuration {

	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array(
			'user_id' => 0,
			'configuration' => '',
			'action' => '',
			'from_wallet_type' => '',
			'to_wallet_type' => '',
			'from_user_rank' => '',
			'to_user_rank' => '',
	);

	/**
	 * @var Wallet status Data
	 **/
	private $wallet_status_data = null;

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		parent::__construct($options);
		unset($options['servicelocator']);
		$this->setOptions($options);
	}

	/**
	 * Get Status Data
	 * 
	 * @return array data
	 **/
	public function getStatusData() {
		if (!is_array($this->wallet_status_data) || count($this->wallet_status_data) < 1) {
			$rawdata = array();
			$QStatus = $this->getDatabase();
			$QStatus->select();
			$QStatus
					->columns(
							array(
									'id' => 'wallet_status_id',
									'key' => 'wallet_status_key',
									'created_date' => 'wallet_status_created_date',
									'modified_date' => 'wallet_status_modified_date',
									'created_by' => 'wallet_status_created_by',
									'modified_by' => 'wallet_status_modified_by',
							));
			$QStatus->from(array(
							'ws' => 'wallet_status'
					));
			$QStatus->setCacheName('wallet_status');
			$QStatus->execute();
			if ($QStatus->hasResult()) {
				while ($QStatus->valid()) {
					$rawdata = $QStatus->current();
					$this->wallet_status_data[$rawdata['id']] = $rawdata;
					$QStatus->next();
				}
			}
		}
		return $this->wallet_status_data;
	}

	/**
	 * Get Status
	 */
	public function getStatus($id = null) {
		$data = $this->getStatusData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Status Message
	 */
	public function getStatusMessage($id = null) {
		$data = $this->getStatus($id);
		$key = $data['wallet_status_key'];
		$name = "";
		if (strlen($key) > 0) {
			$name = $this->getTranslate('text_wallet_status_' . $key);
		}
		return $name;
	}
}

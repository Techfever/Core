<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Type extends Transaction {

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
	 * @var Wallet type Data
	 **/
	private $wallet_type_data = null;

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
	 * Get Type Data
	 * 
	 * @return array data
	 **/
	public function getTypeData() {
		if (!is_array($this->wallet_type_data) || count($this->wallet_type_data) < 1) {
			$rawdata = array();
			$QType = $this->getDatabase();
			$QType->select();
			$QType
					->columns(
							array(
									'id' => 'wallet_type_id',
									'key' => 'wallet_type_key',
									'register' => 'wallet_type_register_status',
									'transfer' => 'wallet_type_transfer_status',
									'exchange' => 'wallet_type_exchange_status',
									'withdraw' => 'wallet_type_withdraw_status',
									'sponsor' => 'wallet_type_sponsor_status',
									'pairing' => 'wallet_type_pairing_status',
									'matching' => 'wallet_type_matching_status',
									'created_date' => 'wallet_type_created_date',
									'modified_date' => 'wallet_type_modified_date',
									'created_by' => 'wallet_type_created_by',
									'modified_by' => 'wallet_type_modified_by',
							));
			$QType->from(array(
							'wt' => 'wallet_type'
					));
			$QType->where(array(
							'wt.wallet_type_status' => '1'
					));
			$QType->setCacheName('wallet_type');
			$QType->execute();
			if ($QType->hasResult()) {
				while ($QType->valid()) {
					$rawdata = $QType->current();
					$this->wallet_type_data[$rawdata['id']] = $rawdata;
					$QType->next();
				}
			}
		}
		return $this->wallet_type_data;
	}

	/**
	 * Get Type
	 */
	public function getType($id = null) {
		$data = $this->getTypeData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Type Message
	 */
	public function getTypeMessage($id = null) {
		$data = $this->getType($id);
		$key = $data['key'];
		$name = "";
		if (strlen($key) > 0) {
			$name = $this->getTranslate('text_wallet_type_' . $key);
		}
		return $name;
	}
}

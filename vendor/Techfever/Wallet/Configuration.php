<?php

namespace Techfever\Wallet;

use Techfever\Exception;

class Configuration extends Withdraw {

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
	 * @var Wallet configuration Data
	 **/
	private $wallet_configuration_data = null;

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
	 * Get Configuration Data
	 * 
	 * @return array data
	 **/
	public function getConfigurationData() {
		if (!is_array($this->wallet_configuration_data) || count($this->wallet_configuration_data) < 1) {
			$rawdata = array();
			$QConfiguration = $this->getDatabase();
			$QConfiguration->select();
			$QConfiguration
					->columns(
							array(
									'id' => 'wallet_configuration_id',
									'user_rank_from' => 'user_rank_id_from',
									'user_rank_to' => 'user_rank_id_to',
									'wallet_type_from' => 'wallet_type_id_from',
									'wallet_type_to' => 'wallet_type_id_to',
									'action' => 'wallet_configuration_action',
									'comisssion' => 'wallet_configuration_comisssion',
									'created_date' => 'wallet_configuration_created_date',
									'modified_date' => 'wallet_configuration_modified_date',
									'created_by' => 'wallet_configuration_created_by',
									'modified_by' => 'wallet_configuration_modified_by',
							));
			$QConfiguration->from(array(
							'wc' => 'wallet_configuration'
					));
			$QConfiguration->where(array(
							'wc.wallet_configuration_action' => $this->getOption('configuration'),
							'wc.wallet_configuration_status' => '1'
					));
			$QConfiguration->setCacheName('wallet_configuration_' . $this->getOption('configuration'));
			$QConfiguration->execute();
			if ($QConfiguration->hasResult()) {
				while ($QConfiguration->valid()) {
					$rawdata = $QConfiguration->current();
					$this->wallet_configuration_data[$rawdata['id']] = $rawdata;
					$QConfiguration->next();
				}
			}
		}
		return $this->wallet_configuration_data;
	}

	/**
	 * Get Configuration
	 */
	public function getConfiguration($id = null) {
		$data = $this->getConfigurationData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}
}

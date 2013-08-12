<?php

namespace Techfever\Wallet;

use Techfever\Functions\Crypt\Encode as Encrypt;
use Techfever\Exception;

class Wallet extends Type {

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
	 * @var Wallet Data
	 **/
	private $user_wallet_data = null;

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
	 * Get User Wallet Data
	 * 
	 * @return array data
	 **/
	public function getWalletData() {
		if (!is_array($this->user_wallet_data) || count($this->user_wallet_data) < 1) {
			if ($this->getOption('user_id') > 0) {
				$QWallet = $this->getDatabase();
				$QWallet->select();
				$QWallet->columns(array(
								'type' => 'wallet_type_id',
								'amount_unlocked' => 'user_wallet_amount',
								'amount_locked' => 'user_wallet_amount_locked'
						));
				$QWallet->from(array(
								'uw' => 'user_wallet'
						));
				$QWallet->where(array(
								'uw.user_access_id' => $this->getOption('user_id'),
								'uw.user_wallet_status' => '1'
						));
				$QWallet->setCacheName('user_wallet_' . $this->getOption('user_id'));
				$QWallet->execute();
				if ($QWallet->hasResult()) {
					while ($QWallet->valid()) {
						$rawdata = $QWallet->current();
						$amount = ($rawdata['amount_unlocked'] - $rawdata['amount_locked']);
						$rawdata['amount_total'] = $amount;
						$rawdata['title'] = $this->getTypeMessage($rawdata['type']);
						$rawdata['amount'] = $this->formatNumber($amount);
						$rawdata['currency'] = $this->formatCurrency($amount, 'USD');
						$this->user_wallet_data[$rawdata['type']] = $rawdata;
						$QWallet->next();
					}
				}
			}
		}
		return $this->user_wallet_data;
	}

	/**
	 * Get User Wallet
	 */
	public function getWallet($id = null) {
		$data = $this->getWalletData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get User Total
	 */
	public function getPocket() {
		$data = $this->getWalletData();
		$rawdata = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $data_key => $data_value) {
				$rawdata[$data_key] = $data_value['amount'];
			}
		}
		return $rawdata;
	}

	/**
	 * Get User Total by ID
	 */
	public function getPocketByID($id = null) {
		$data = $this->getPocket();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get User History List
	 */
	public function getHistoryListing($search = null, $order = null, $index = 0, $perpage = 10, $cache = 'user_wallet_history', $encryted_id = false) {
		$orderstr = null;
		$data = array();

		$QHistory = $this->getDatabase();
		$QHistory->select();
		$QHistory->columns(array(
						'*'
				));
		$QHistory->from(array(
						'uwh' => 'user_wallet_history'
				));
		$where = array(
				'uwh.user_wallet_history_visible_status' => '1',
				'uwh.user_wallet_history_deleted_status' => '0',
		);
		if (is_array($search) && count($search) > 0 && array_key_exists('user_wallet_history', $search)) {
			$where = array_merge($where, $search['user_wallet_history']);
		}
		$QHistory->where($where);
		if (empty($order)) {
			$QHistory->order(array(
							'uwh.user_wallet_history_id'
					));
		} else {
			$QHistory->order($order);
		}
		if (isset($perpage)) {
			$QHistory->limit((int) $perpage);
		}
		if (isset($index)) {
			$QHistory->offset((int) $index);
		}
		$QHistory->setCacheName('user_wallet_history_' . $cache);
		$QHistory->execute();
		if ($QHistory->hasResult()) {
			$data = array();
			$count = 1;
			while ($QHistory->valid()) {
				$rawdata = $QHistory->current();
				$rawdata['no'] = $count;

				$cryptID = new Encrypt($rawdata['user_wallet_history_id']);
				$cryptID = $cryptID->__toString();
				$rawdata['id'] = ($encryted_id ? $cryptID : $rawdata['user_wallet_history_id']);

				$datetime = new \DateTime($rawdata['user_wallet_history_modified_date']);
				$rawdata['user_wallet_history_modified_date'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_wallet_history_created_date']);
				$rawdata['user_wallet_history_created_date'] = $datetime->format('H:i:s d-m-Y');

				$datetime = new \DateTime($rawdata['user_wallet_history_approved_date']);
				$rawdata['user_wallet_history_approved_date'] = $datetime->format('H:i:s d-m-Y');

				$QHistory->next();
				ksort($rawdata);
				$data[$rawdata['user_wallet_history_id']] = $rawdata;
				$count++;
			}
		}
		if (count($data) > 0) {
			return $data;
		} else {
			return false;
		}
	}
}

<?php

namespace Techfever\Wallet;

use Techfever\Exception;
use Techfever\Functions\Crypt\Encode as Encrypt;
use Techfever\Functions\General as GeneralBase;

class Withdraw extends GeneralBase {

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
	 * @var Wallet withdraw Data
	 **/
	private $wallet_withdraw_data = null;

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
	 * Get Withdraw Data
	 * 
	 * @return array data
	 **/
	public function getWithdrawData() {
		if (!is_array($this->wallet_withdraw_data) || count($this->wallet_withdraw_data) < 1) {
			$rawdata = array();
			$QWithdraw = $this->getDatabase();
			$QWithdraw->select();
			$QWithdraw
					->columns(
							array(
									'id' => 'wallet_withdraw_id',
									'user_rank' => 'user_rank_id',
									'wallet_type' => 'wallet_type_id',
									'date' => 'wallet_withdraw_date',
									'flag' => 'wallet_withdraw_flag',
									'created_date' => 'wallet_withdraw_created_date',
									'modified_date' => 'wallet_withdraw_modified_date',
									'created_by' => 'wallet_withdraw_created_by',
									'modified_by' => 'wallet_withdraw_modified_by',
							));
			$QWithdraw->from(array(
							'ww' => 'wallet_withdraw'
					));
			$QWithdraw->where(array(
							'ww.wallet_withdraw_status' => '1'
					));
			$QWithdraw->setCacheName('wallet_withdraw');
			$QWithdraw->execute();
			if ($QWithdraw->hasResult()) {
				while ($QWithdraw->valid()) {
					$rawdata = $QWithdraw->current();
					$this->wallet_withdraw_data[$rawdata['id']] = $rawdata;
					$QWithdraw->next();
				}
			}
		}
		return $this->wallet_withdraw_data;
	}

	/**
	 * Get User Withdraw History List
	 */
	public function getWithdrawHistoryListing($search = null, $order = null, $index = 0, $perpage = 10, $cache = 'user_wallet_history', $encryted_id = false) {
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
		$QHistory->join(array(
						'uwhb' => 'user_wallet_history_bank'
				), 'uwhb.user_wallet_history_id  = uwh.user_wallet_history_id', array(
						'*'
				));
		$where = array(
				'uwh.user_wallet_history_visible_status' => '1',
				'uwh.user_wallet_history_deleted_status' => '0',
		);
		if (is_array($search) && count($search) > 0 && array_key_exists('user_wallet_history', $search)) {
			$where = array_merge($where, $search['user_wallet_history']);
		}
		if (is_array($search) && count($search) > 0 && array_key_exists('user_wallet_history_bank', $search)) {
			$where = array_merge($where, $search['user_wallet_history_bank']);
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
		$QHistory->setCacheName('user_wallet_history_withdraw_' . $cache);
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

	/**
	 * Get Withdraw
	 */
	public function getWithdraw($id = null) {
		$data = $this->getWithdrawData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}
}

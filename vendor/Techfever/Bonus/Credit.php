<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Credit extends Calculate {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'user_access_id' => 0,
			'user_rank_id' => 0,
			'amount' => 0,
			'user_sponsor_id' => 0,
			'user_sponsor_username' => null,
			'user_sponsor_rank_id' => 0,
			'user_placement_id' => 0,
			'user_placement_username' => null,
			'user_placement_rank_id' => 0,
			'execute_date' => null 
	);
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
	 * Credit User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function creditBonus($action, $to_user, $to_rank, $profit, $transaction) {
		$status = false;
		if ($to_user > 0 && $to_rank > 0 && ! empty ( $profit ) && is_numeric ( $profit ) && $profit > 0) {
			$wallet_type = $this->getUserWallet ()->getActionType ( $action );
			if (is_array ( $wallet_type ) && array_key_exists ( 0, $wallet_type )) {
				$wallet_type = $wallet_type [0];
			} else {
				$wallet_type = 0;
			}
			$walletoption = array (
					'action' => $action,
					'from_user' => 1,
					'to_user' => $to_user,
					'from_wallet_type' => $wallet_type,
					'to_wallet_type' => $wallet_type,
					'from_user_rank' => 8888,
					'to_user_rank' => $to_rank,
					'transaction_status' => 3,
					'transaction' => $transaction 
			);
			$this->getUserWallet ()->setOptions ( $walletoption );
			$datetime = new \DateTime ();
			$walletdata = array (
					'ignore_from' => true,
					'user_wallet_amount' => $profit,
					'user_wallet_comment' => '',
					'timestamp' => $datetime->getTimestamp (),
					'log_created_by' => 'Management',
					'log_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00',
					'log_modified_by' => 'Management',
					'log_modified_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
			);
			$type_data = $this->getUserWallet ()->getTypeBonus ( $action );
			if (is_array ( $type_data ) && count ( $type_data ) > 0) {
				foreach ( $type_data as $type_value ) {
					$walletoption ['to_wallet_type'] = $type_value ['id'];
					$walletoption ['from_wallet_type'] = $type_value ['id'];
					$this->getUserWallet ()->setOption ( 'from_wallet_type', $walletoption ['from_wallet_type'] );
					$this->getUserWallet ()->setOption ( 'to_wallet_type', $walletoption ['to_wallet_type'] );
					$walletdata ['user_wallet_amount'] = number_format ( $profit * $type_value ['percentage'] / 100, 2 );
					$history_from = $this->getUserWallet ()->insertUserHistory ( $walletdata, '-' );
					if ($history_from !== false && $history_from > 0) {
						$history_to = $this->getUserWallet ()->insertUserHistory ( $walletdata, '+' );
						if ($history_from !== false && $history_from > 0) {
							$pocket_to = $this->getUserWallet ()->updateUserPocket ( $walletoption ['to_user'], $walletoption ['to_wallet_type'], $walletdata ['user_wallet_amount'], '+' );
							if ($pocket_to) {
								$status = true;
							}
						}
					}
				}
			}
		}
		return $status;
	}
}

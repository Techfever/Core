<?php

namespace Techfever\Bonus;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Calculate extends GeneralBase {
	
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
	 * Calculated User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function isCalculated($action) {
		$status = true;
		if (! empty ( $action )) {
			$QBonus = $this->getDatabase ();
			$QBonus->select ();
			$QBonus->columns ( array (
					'id' => 'user_bonus_id',
					'sponsor' => 'user_bonus_sponsor_calculated',
					'pairing' => 'user_bonus_pairing_calculated',
					'matching' => 'user_bonus_matching_calculated',
					'roi' => 'user_bonus_roi_calculated' 
			) );
			$QBonus->from ( array (
					'ub' => 'user_bonus' 
			) );
			$QBonus->where ( array (
					'ub.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '"',
					'DATE(ub.user_bonus_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' 
			) );
			$QBonus->execute ();
			if ($QBonus->hasResult ()) {
				$rawdata = $QBonus->current ();
				if (array_key_exists ( $action, $rawdata ) && $rawdata [$action] == 0) {
					$status = false;
				}
			} else {
				$this->createCalculate ();
				$status = false;
			}
		}
		return $status;
	}
	
	/**
	 * Create User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function createCalculate() {
		$IBonus = $this->getDatabase ();
		$IBonus->insert ();
		$IBonus->into ( 'user_bonus' );
		$IBonus->values ( array (
				'user_access_id' => $this->getOption ( 'user_access_id' ),
				'user_bonus_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
		) );
		$IBonus->execute ();
	}
	
	/**
	 * Update User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function updateCalculated($action) {
		$status = false;
		if (! empty ( $action )) {
			$UBonus = $this->getDatabase ();
			$UBonus->update ();
			$UBonus->table ( 'user_bonus' );
			$UBonus->set ( array (
					'user_bonus_' . $action . '_calculated' => 1 
			) );
			$UBonus->where ( array (
					'user_access_id = "' . $this->getOption ( 'user_access_id' ) . '"',
					'DATE(user_bonus_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' 
			) );
			$UBonus->execute ();
			if ($UBonus->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
}

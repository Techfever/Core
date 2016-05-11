<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Matching extends Level {
	
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
	
	/**
	 *
	 * @var Bonus Matching Data
	 *     
	 */
	private $user_matching_setting = array ();
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
	 * Credit User Bonus Matching
	 *
	 * @return boolean status
	 *        
	 */
	public function creditMatching($start_date = null, $end_date = null) {
		$status = false;
		if (empty ( $start_date )) {
			$datetime = new \DateTime ();
			$start_date = $datetime->format ( 'Y-m-d' );
		}
		if (empty ( $end_date )) {
			$datetime = new \DateTime ();
			$end_date = $datetime->format ( 'Y-m-d' );
		}
		
		$QBonusMatching = $this->getDatabase ();
		$QBonusMatching->select ();
		$QBonusMatching->columns ( array (
				'id' => 'user_bonus_matching_id',
				'user' => 'user_access_id',
				'profit' => 'user_bonus_matching_profit' 
		) );
		$QBonusMatching->from ( array (
				'ubs' => 'user_bonus_matching' 
		) );
		$QBonusMatching->join ( array (
				'ua' => 'user_access' 
		), 'ua.user_access_id = ubs.user_access_id', array (
				'rank' => 'user_rank_id' 
		) );
		$QBonusMatching->where ( array (
				'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_bonus_matching_status = 0 and ( DATE(ubs.user_bonus_matching_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_matching_created_date) <= "' . $end_date . '")' 
		) );
		$QBonusMatching->execute ();
		if ($QBonusMatching->hasResult ()) {
			$user_id = array ();
			while ( $QBonusMatching->valid () ) {
				$rawdata = $QBonusMatching->current ();
				if ($this->creditBonus ( 'matching', $rawdata ['user'], $rawdata ['rank'], $rawdata ['profit'], 8030 )) {
					$status = true;
					$user_id [] = $rawdata ['id'];
				}
				$QBonusMatching->next ();
			}
			if (is_array ( $user_id ) && sizeof ( $user_id ) > 0) {
				$UMatching = $this->getDatabase ();
				$UMatching->update ();
				$UMatching->table ( 'user_bonus_matching' );
				$UMatching->set ( array (
						'user_bonus_matching_status' => 1 
				) );
				$UMatching->where ( array (
						'user_bonus_matching_id in ( ' . implode ( ', ', $user_id ) . ')' 
				) );
				$UMatching->execute ();
			}
		}
		return $status;
	}
	
	/**
	 * Get User Bonus Matching Setting
	 *
	 * @return array data
	 *        
	 */
	public function getMatchingSetting($id = null) {
		$data = null;
		if (! array_key_exists ( $id, $this->user_matching_setting )) {
			$rawdata = 0;
			$this->user_matching_setting [$id] = $rawdata;
			if (is_numeric ( $id ) && $id > 0) {
				$QMatchingSetting = $this->getDatabase ();
				$QMatchingSetting->select ();
				$QMatchingSetting->columns ( array (
						'bonus_matching_id' => 'user_rank_matching_id',
						'bonus_matching' => 'user_rank_matching_percentage',
						'bonus_matching_sponsor' => 'user_rank_matching_sponsor',
						'bonus_matching_unique' => 'user_rank_matching_sponsor_unique' 
				) );
				$QMatchingSetting->from ( array (
						'ub' => 'user_rank_matching' 
				) );
				$QMatchingSetting->where ( array (
						'ub.user_rank_id' => $id 
				) );
				$QMatchingSetting->execute ();
				if ($QMatchingSetting->hasResult ()) {
					$rawdata = $QMatchingSetting->current ();
					$this->user_matching_setting [$id] = $rawdata;
				}
			}
		}
		$data = $this->user_matching_setting [$id];
		return $data;
	}
	
	/**
	 * Calculate User Bonus Matching
	 *
	 * @return boolean status
	 *        
	 */
	public function calculateMatching() {
		$matching_status = false;
		if (BONUS_MATCHING_PERCENTAGE_USE_OWN == "True") {
			$id = $this->getOption ( 'user_rank_id' );
		} elseif (BONUS_MATCHING_PERCENTAGE_USE_ROOT == "True") {
			$id = $this->getOption ( 'user_sponsor_rank_id' );
		}
		$bonus_setting = $this->getMatchingSetting ( $id );
		if (is_array ( $bonus_setting ) && count ( $bonus_setting ) > 0) {
			$matching_percentage = $bonus_setting ['bonus_matching'];
			$matching_sponsor = $bonus_setting ['bonus_matching_sponsor'];
			$matching_percentage = $matching_percentage / 100;
			$matching_percentage = number_format ( $matching_percentage, 2 );
			if ($matching_sponsor > 0) {
				$QVerify = $this->getDatabase ();
				$QVerify->select ();
				$QVerify->columns ( array (
						'user_access_id' 
				) );
				$QVerify->from ( array (
						'ubm' => 'user_bonus_matching' 
				) );
				$QVerify->where ( array (
						'ubm.user_access_id = ' . $this->getOption ( 'user_sponsor_id' ) 
				) );
				$QVerify->execute ();
				if (! $QVerify->hasResult ()) {
					$username = $this->getOption ( 'user_sponsor_username' );
					$QHierarchy = $this->getDatabase ();
					$QHierarchy->select ();
					$QHierarchy->columns ( array (
							'user_access_id' 
					) );
					$QHierarchy->from ( array (
							'uh' => 'user_hierarchy' 
					) );
					$QHierarchy->where ( array (
							'uh.user_hierarchy_sponsor_username = "' . $username . '"' 
					) );
					$QHierarchy->execute ();
					if ($QHierarchy->hasResult ()) {
						$total_sponsor = $QHierarchy->count ();
						if ($total_sponsor >= $matching_sponsor) {
							$matching_status = true;
						}
					}
				}
			} else {
				$matching_status = true;
			}
		}
		if ($matching_status) {
			$matching_amount = $this->getOption ( 'amount' );
			$matching_profit = $matching_amount * $matching_percentage;
			if ($matching_profit > 0) {
				$datetime = new \DateTime ();
				$IMatchingBonus = $this->getDatabase ();
				$IMatchingBonus->insert ();
				$IMatchingBonus->into ( 'user_bonus_matching' );
				$IMatchingBonus->values ( array (
						'user_access_id' => $this->getOption ( 'user_sponsor_id' ),
						'user_access_id_from' => 0,
						'user_bonus_matching_amount' => $matching_amount,
						'user_bonus_matching_precentage' => $matching_percentage,
						'user_bonus_matching_profit' => $matching_profit,
						'user_bonus_matching_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
				) );
				$IMatchingBonus->execute ();
				
				$this->updateCalculated ( 'matching' );
			}
		}
	}
	
	/**
	 * Get User Bonus Matching
	 *
	 * @return array data
	 *        
	 */
	public function getMatchingData() {
		$details = array ();
		$summary = array (
				'start_date' => '',
				'end_date' => '',
				'total_amount' => '' 
		);
		$QBonusMatching = $this->getDatabase ();
		$QBonusMatching->select ();
		$QBonusMatching->columns ( array (
				'id' => 'user_bonus_matching_id',
				'user_to_id' => 'user_access_id',
				'amount' => 'user_bonus_matching_amount',
				'percentage' => 'user_bonus_matching_precentage',
				'profit' => 'user_bonus_matching_profit',
				'created_date' => 'user_bonus_matching_created_date' 
		) );
		$QBonusMatching->from ( array (
				'ubs' => 'user_bonus_matching' 
		) );
		$QBonusMatching->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id = ubs.user_access_id', array (
				'username_to' => 'user_access_username' 
		) );
		$QBonusMatching->where ( array (
				'ubs.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '" and ubs.user_bonus_matching_status = 1 and ( DATE(ubs.user_bonus_matching_created_date) >= "' . $this->getOption ( 'start_date' ) . '" and DATE(ubs.user_bonus_matching_created_date) <= "' . $this->getOption ( 'end_date' ) . '")' 
		) );
		$QBonusMatching->order ( array (
				'ubs.user_bonus_matching_created_date ASC' 
		) );
		$QBonusMatching->execute ();
		$count = 0;
		$amount = 0;
		$rank = array ();
		if ($QBonusMatching->hasResult ()) {
			while ( $QBonusMatching->valid () ) {
				$rawdata = $QBonusMatching->current ();
				$datetime = new \DateTime ( $rawdata ['created_date'] );
				$rawdata ['created_date_format'] = $datetime->format ( 'd-m-Y' );
				$rawdata ['percentage_format'] = $rawdata ['percentage'] * 100;
				$details [$rawdata ['id']] = $rawdata;
				$amount += $rawdata ['profit'];
				$count ++;
				$QBonusMatching->next ();
			}
		}
		
		$start_datetime = new \DateTime ( $this->getOption ( 'start_date' ) );
		$summary ['start_date'] = $start_datetime->format ( 'd-m-Y' );
		$end_datetime = new \DateTime ( $this->getOption ( 'end_date' ) );
		$summary ['end_date'] = $end_datetime->format ( 'd-m-Y' );
		$summary ['total_amount'] = $amount;
		$data = array (
				'summary' => $summary,
				'details' => $details 
		);
		return $data;
	}
}

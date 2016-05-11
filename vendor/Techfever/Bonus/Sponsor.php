<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Sponsor extends Pairing {
	
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
	 * @var Bonus Sponsor
	 *     
	 */
	private $user_sponsor_setting = array ();
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
	 * Credit User Bonus Sponsor
	 *
	 * @return boolean status
	 *        
	 */
	public function creditSponsor($start_date = null, $end_date = null) {
		$status = false;
		if (empty ( $start_date )) {
			$datetime = new \DateTime ();
			$start_date = $datetime->format ( 'Y-m-d' );
		}
		if (empty ( $end_date )) {
			$datetime = new \DateTime ();
			$end_date = $datetime->format ( 'Y-m-d' );
		}
		
		$QBonusSponsor = $this->getDatabase ();
		$QBonusSponsor->select ();
		$QBonusSponsor->columns ( array (
				'id' => 'user_bonus_sponsor_id',
				'user' => 'user_access_id',
				'profit' => 'user_bonus_sponsor_profit' 
		) );
		$QBonusSponsor->from ( array (
				'ubs' => 'user_bonus_sponsor' 
		) );
		$QBonusSponsor->join ( array (
				'ua' => 'user_access' 
		), 'ua.user_access_id = ubs.user_access_id', array (
				'rank' => 'user_rank_id' 
		) );
		/*
		 * $QBonusSponsor->where ( array ( 'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_access_id_from = ' . $this->getOption ( 'user_access_id' ) . ' and ubs.user_bonus_sponsor_status = 0 and ubs.user_access_id = ' . $this->getOption ( 'user_sponsor_id' ) . ' and ( DATE(ubs.user_bonus_sponsor_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_sponsor_created_date) <= "' . $end_date . '")' ) );
		 */
		$QBonusSponsor->where ( array (
				'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_bonus_sponsor_status = 0 and ( DATE(ubs.user_bonus_sponsor_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_sponsor_created_date) <= "' . $end_date . '")' 
		) );
		$QBonusSponsor->execute ();
		if ($QBonusSponsor->hasResult ()) {
			$user_id = array ();
			while ( $QBonusSponsor->valid () ) {
				$rawdata = $QBonusSponsor->current ();
				if ($this->creditBonus ( 'sponsor', $rawdata ['user'], $rawdata ['rank'], $rawdata ['profit'], 8010 )) {
					$status = true;
					$user_id [] = $rawdata ['id'];
				}
				$QBonusSponsor->next ();
			}
			if (is_array ( $user_id ) && sizeof ( $user_id ) > 0) {
				$USponsor = $this->getDatabase ();
				$USponsor->update ();
				$USponsor->table ( 'user_bonus_sponsor' );
				$USponsor->set ( array (
						'user_bonus_sponsor_status' => 1 
				) );
				$USponsor->where ( array (
						'user_bonus_sponsor_id in ( ' . implode ( ', ', $user_id ) . ')' 
				) );
				$USponsor->execute ();
			}
		}
		return $status;
	}
	
	/**
	 * Credit User Bonus Sponsor Rebate
	 *
	 * @return boolean status
	 *        
	 */
	public function creditSponsorRebate($start_date = null, $end_date = null) {
		$status = false;
		if (empty ( $start_date )) {
			$datetime = new \DateTime ();
			$start_date = $datetime->format ( 'Y-m-d' );
		}
		if (empty ( $end_date )) {
			$datetime = new \DateTime ();
			$end_date = $datetime->format ( 'Y-m-d' );
		}
		
		$QBonusSponsor = $this->getDatabase ();
		$QBonusSponsor->select ();
		$QBonusSponsor->columns ( array (
				'id' => 'user_bonus_sponsor_rebate_id',
				'user' => 'user_access_id',
				'profit' => 'user_bonus_sponsor_rebate_profit' 
		) );
		$QBonusSponsor->from ( array (
				'ubs' => 'user_bonus_sponsor_rebate' 
		) );
		$QBonusSponsor->join ( array (
				'ua' => 'user_access' 
		), 'ua.user_access_id = ubs.user_access_id', array (
				'rank' => 'user_rank_id' 
		) );
		/*
		 * $QBonusSponsor->where ( array ( 'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_access_id_from = ' . $this->getOption ( 'user_access_id' ) . ' and ubs.user_bonus_sponsor_rebate_status = 0 and ubs.user_access_id = ' . $this->getOption ( 'user_sponsor_id' ) . ' and ( DATE(ubs.user_bonus_sponsor_rebate_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_sponsor_rebate_created_date) <= "' . $end_date . '")' ) );
		 */
		$QBonusSponsor->where ( array (
				'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_bonus_sponsor_rebate_status = 0 and ( DATE(ubs.user_bonus_sponsor_rebate_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_sponsor_rebate_created_date) <= "' . $end_date . '")' 
		) );
		$QBonusSponsor->execute ();
		if ($QBonusSponsor->hasResult ()) {
			$user_id = array ();
			while ( $QBonusSponsor->valid () ) {
				$rawdata = $QBonusSponsor->current ();
				if ($this->creditBonus ( 'sponsor_rebate', $rawdata ['user'], $rawdata ['rank'], $rawdata ['profit'], 8040 )) {
					$status = true;
					$user_id [] = $rawdata ['id'];
				}
				$QBonusSponsor->next ();
			}
			if (is_array ( $user_id ) && sizeof ( $user_id ) > 0) {
				$USponsor = $this->getDatabase ();
				$USponsor->update ();
				$USponsor->table ( 'user_bonus_sponsor_rebate' );
				$USponsor->set ( array (
						'user_bonus_sponsor_rebate_status' => 1 
				) );
				$USponsor->where ( array (
						'user_bonus_sponsor_rebate_id in ( ' . implode ( ', ', $user_id ) . ')' 
				) );
				$USponsor->execute ();
			}
		}
		return $status;
	}
	
	/**
	 * Get User Bonus Sponsor Setting
	 *
	 * @return array data
	 *        
	 */
	public function getSponsorSetting($id = null) {
		$data = null;
		if (! array_key_exists ( $id, $this->user_sponsor_setting )) {
			$rawdata = 0;
			$this->user_sponsor_setting [$id] = $rawdata;
			if (is_numeric ( $id ) && $id > 0) {
				$QSponsorSetting = $this->getDatabase ();
				$QSponsorSetting->select ();
				$QSponsorSetting->columns ( array (
						'bonus_sponsor_id' => 'user_rank_sponsor_id',
						'bonus_sponsor_percentage' => 'user_rank_sponsor_percentage',
						'bonus_sponsor_rebate_status' => 'user_rank_sponsor_rebate_status',
						'bonus_sponsor_rebate_percentage' => 'user_rank_sponsor_rebate_percentage' 
				) );
				$QSponsorSetting->from ( array (
						'ub' => 'user_rank_sponsor' 
				) );
				$QSponsorSetting->where ( array (
						'ub.user_rank_id' => $id 
				) );
				$QSponsorSetting->execute ();
				if ($QSponsorSetting->hasResult ()) {
					$rawdata = $QSponsorSetting->current ();
					$this->user_sponsor_setting [$id] = $rawdata;
				}
			}
		}
		$data = $this->user_sponsor_setting [$id];
		return $data;
	}
	
	/**
	 * Calculate User Bonus Sponsor
	 *
	 * @return boolean status
	 *        
	 */
	public function calculateSponsor() {
		if (BONUS_SPONSOR_PERCENTAGE_USE_OWN == "True") {
			$id = $this->getOption ( 'user_rank_id' );
		} elseif (BONUS_SPONSOR_PERCENTAGE_USE_ROOT == "True") {
			$id = $this->getOption ( 'user_sponsor_rank_id' );
		}
		$sponsor_setting = $this->getSponsorSetting ( $id );
		$percentage = $sponsor_setting ['bonus_sponsor_percentage'];
		$sponsor_percentage = $percentage / 100;
		$sponsor_percentage = number_format ( $sponsor_percentage, 2 );
		if ($sponsor_percentage > 0) {
			$sponsor_amount = $this->getOption ( 'amount' );
			$sponsor_profit = $sponsor_amount * $sponsor_percentage;
			if ($sponsor_profit > 0) {
				$datetime = new \DateTime ();
				$ISponsorBonus = $this->getDatabase ();
				$ISponsorBonus->insert ();
				$ISponsorBonus->into ( 'user_bonus_sponsor' );
				$ISponsorBonus->values ( array (
						'user_access_id' => $this->getOption ( 'user_sponsor_id' ),
						'user_access_id_from' => $this->getOption ( 'user_access_id' ),
						'user_bonus_sponsor_amount' => $sponsor_amount,
						'user_bonus_sponsor_precentage' => $sponsor_percentage,
						'user_bonus_sponsor_profit' => $sponsor_profit,
						'user_bonus_sponsor_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
				) );
				$ISponsorBonus->execute ();
				
				$this->updateCalculated ( 'sponsor' );
			}
		}
	}
	
	/**
	 * Calculate User Bonus Sponsor Rebate
	 *
	 * @return boolean status
	 *        
	 */
	public function calculateSponsorRebate() {
		if (BONUS_SPONSOR_PERCENTAGE_USE_OWN == "True") {
			$id = $this->getOption ( 'user_rank_id' );
		} elseif (BONUS_SPONSOR_PERCENTAGE_USE_ROOT == "True") {
			$id = $this->getOption ( 'user_sponsor_rank_id' );
		}
		$sponsor_setting = $this->getSponsorSetting ( $id );
		$status = $sponsor_setting ['bonus_sponsor_rebate_status'];
		if ($status == 1) {
			$percentage = $sponsor_setting ['bonus_sponsor_rebate_percentage'];
			$sponsor_percentage = $percentage / 100;
			$sponsor_percentage = number_format ( $sponsor_percentage, 2 );
			if ($sponsor_percentage > 0) {
				$sponsor_amount = $this->getOption ( 'amount' );
				$sponsor_profit = $sponsor_amount * $sponsor_percentage;
				if ($sponsor_profit > 0) {
					$datetime = new \DateTime ();
					$ISponsorBonus = $this->getDatabase ();
					$ISponsorBonus->insert ();
					$ISponsorBonus->into ( 'user_bonus_sponsor_rebate' );
					$ISponsorBonus->values ( array (
							'user_access_id' => $this->getOption ( 'user_sponsor_id' ),
							'user_access_id_from' => $this->getOption ( 'user_access_id' ),
							'user_bonus_sponsor_rebate_amount' => $sponsor_amount,
							'user_bonus_sponsor_rebate_precentage' => $sponsor_percentage,
							'user_bonus_sponsor_rebate_profit' => $sponsor_profit,
							'user_bonus_sponsor_rebate_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
					) );
					$ISponsorBonus->execute ();
					$this->updateCalculated ( 'sponsor' );
				}
			}
		}
	}
	
	/**
	 * Get User Bonus Sponsor
	 *
	 * @return array data
	 *        
	 */
	public function getSponsorData() {
		$details = array ();
		$summary = array (
				'start_date' => '',
				'end_date' => '',
				'total_user' => '',
				'total_amount' => '',
				'rank' => '' 
		);
		$QBonusSponsor = $this->getDatabase ();
		$QBonusSponsor->select ();
		$QBonusSponsor->columns ( array (
				'id' => 'user_bonus_sponsor_id',
				'user_to_id' => 'user_access_id',
				'user_from_id' => 'user_access_id_from',
				'amount' => 'user_bonus_sponsor_amount',
				'percentage' => 'user_bonus_sponsor_precentage',
				'profit' => 'user_bonus_sponsor_profit',
				'created_date' => 'user_bonus_sponsor_created_date' 
		) );
		$QBonusSponsor->from ( array (
				'ubs' => 'user_bonus_sponsor' 
		) );
		$QBonusSponsor->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id = ubs.user_access_id', array (
				'username_to' => 'user_access_username' 
		) );
		$QBonusSponsor->join ( array (
				'uaf' => 'user_access' 
		), 'uaf.user_access_id = ubs.user_access_id_from', array (
				'username_from' => 'user_access_username',
				'username_from_rank' => 'user_rank_id' 
		) );
		$QBonusSponsor->where ( array (
				'ubs.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '" and ubs.user_bonus_sponsor_status = 1 and ( DATE(ubs.user_bonus_sponsor_created_date) >= "' . $this->getOption ( 'start_date' ) . '" and DATE(ubs.user_bonus_sponsor_created_date) <= "' . $this->getOption ( 'end_date' ) . '")' 
		) );
		$QBonusSponsor->order ( array (
				'ubs.user_bonus_sponsor_created_date ASC',
				'uaf.user_rank_id ASC' 
		) );
		$QBonusSponsor->execute ();
		$count = 0;
		$amount = 0;
		$rank = array ();
		if ($QBonusSponsor->hasResult ()) {
			while ( $QBonusSponsor->valid () ) {
				$rawdata = $QBonusSponsor->current ();
				$datetime = new \DateTime ( $rawdata ['created_date'] );
				$rawdata ['created_date_format'] = $datetime->format ( 'd-m-Y' );
				$rawdata ['percentage_format'] = $rawdata ['percentage'] * 100;
				
				if (! array_key_exists ( $rawdata ['username_from_rank'], $details )) {
					$details [$rawdata ['username_from_rank']] = array ();
				}
				$details [$rawdata ['username_from_rank']] [$rawdata ['user_from_id']] = $rawdata;
				$amount += $rawdata ['profit'];
				$count ++;
				if (! array_key_exists ( $rawdata ['username_from_rank'], $rank )) {
					$rank_text = $this->getUserRank ()->getMessage ( $rawdata ['username_from_rank'] );
					$rank [$rawdata ['username_from_rank']] = array (
							'rank' => $rank_text,
							'total_user' => 0,
							'total_amount' => 0 
					);
				}
				$total_user = $rank [$rawdata ['username_from_rank']] ['total_user'];
				$total_amount = $rank [$rawdata ['username_from_rank']] ['total_amount'];
				$rank [$rawdata ['username_from_rank']] ['total_user'] = $total_user + 1;
				$rank [$rawdata ['username_from_rank']] ['total_amount'] = $total_amount + $rawdata ['profit'];
				$QBonusSponsor->next ();
			}
		}
		
		$start_datetime = new \DateTime ( $this->getOption ( 'start_date' ) );
		$summary ['start_date'] = $start_datetime->format ( 'd-m-Y' );
		$end_datetime = new \DateTime ( $this->getOption ( 'end_date' ) );
		$summary ['end_date'] = $end_datetime->format ( 'd-m-Y' );
		$summary ['total_user'] = $count;
		$summary ['total_amount'] = $amount;
		$summary ['rank'] = $rank;
		$data = array (
				'summary' => $summary,
				'details' => $details 
		);
		return $data;
	}
	
	/**
	 * Get User Bonus Sponsor Rebate
	 *
	 * @return array data
	 *        
	 */
	public function getSponsorRebateData() {
		$details = array ();
		$summary = array (
				'start_date' => '',
				'end_date' => '',
				'total_user' => '',
				'total_amount' => '',
				'rank' => '' 
		);
		$QBonusSponsor = $this->getDatabase ();
		$QBonusSponsor->select ();
		$QBonusSponsor->columns ( array (
				'id' => 'user_bonus_sponsor_rebate_id',
				'user_to_id' => 'user_access_id',
				'user_from_id' => 'user_access_id_from',
				'amount' => 'user_bonus_sponsor_rebate_amount',
				'percentage' => 'user_bonus_sponsor_rebate_precentage',
				'profit' => 'user_bonus_sponsor_rebate_profit',
				'created_date' => 'user_bonus_sponsor_rebate_created_date' 
		) );
		$QBonusSponsor->from ( array (
				'ubs' => 'user_bonus_sponsor_rebate' 
		) );
		$QBonusSponsor->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id = ubs.user_access_id', array (
				'username_to' => 'user_access_username' 
		) );
		$QBonusSponsor->join ( array (
				'uaf' => 'user_access' 
		), 'uaf.user_access_id = ubs.user_access_id_from', array (
				'username_from' => 'user_access_username',
				'username_from_rank' => 'user_rank_id' 
		) );
		$QBonusSponsor->where ( array (
				'ubs.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '" and ubs.user_bonus_sponsor_rebate_status = 1 and ( DATE(ubs.user_bonus_sponsor_rebate_created_date) >= "' . $this->getOption ( 'start_date' ) . '" and DATE(ubs.user_bonus_sponsor_rebate_created_date) <= "' . $this->getOption ( 'end_date' ) . '")' 
		) );
		$QBonusSponsor->order ( array (
				'ubs.user_bonus_sponsor_rebate_created_date ASC',
				'uaf.user_rank_id ASC' 
		) );
		$QBonusSponsor->execute ();
		$count = 0;
		$amount = 0;
		$rank = array ();
		if ($QBonusSponsor->hasResult ()) {
			while ( $QBonusSponsor->valid () ) {
				$rawdata = $QBonusSponsor->current ();
				$datetime = new \DateTime ( $rawdata ['created_date'] );
				$rawdata ['created_date_format'] = $datetime->format ( 'd-m-Y' );
				$rawdata ['percentage_format'] = $rawdata ['percentage'] * 100;
				
				if (! array_key_exists ( $rawdata ['username_from_rank'], $details )) {
					$details [$rawdata ['username_from_rank']] = array ();
				}
				$details [$rawdata ['username_from_rank']] [$rawdata ['user_from_id']] = $rawdata;
				$amount += $rawdata ['profit'];
				$count ++;
				if (! array_key_exists ( $rawdata ['username_from_rank'], $rank )) {
					$rank_text = $this->getUserRank ()->getMessage ( $rawdata ['username_from_rank'] );
					$rank [$rawdata ['username_from_rank']] = array (
							'rank' => $rank_text,
							'total_user' => 0,
							'total_amount' => 0 
					);
				}
				$total_user = $rank [$rawdata ['username_from_rank']] ['total_user'];
				$total_amount = $rank [$rawdata ['username_from_rank']] ['total_amount'];
				$rank [$rawdata ['username_from_rank']] ['total_user'] = $total_user + 1;
				$rank [$rawdata ['username_from_rank']] ['total_amount'] = $total_amount + $rawdata ['profit'];
				$QBonusSponsor->next ();
			}
		}
		
		$start_datetime = new \DateTime ( $this->getOption ( 'start_date' ) );
		$summary ['start_date'] = $start_datetime->format ( 'd-m-Y' );
		$end_datetime = new \DateTime ( $this->getOption ( 'end_date' ) );
		$summary ['end_date'] = $end_datetime->format ( 'd-m-Y' );
		$summary ['total_user'] = $count;
		$summary ['total_amount'] = $amount;
		$summary ['rank'] = $rank;
		$data = array (
				'summary' => $summary,
				'details' => $details 
		);
		return $data;
	}
}

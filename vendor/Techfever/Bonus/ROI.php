<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class ROI extends Credit {
	
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
	 * @var Bonus ROI Setting
	 *     
	 */
	private $user_roi_setting = array ();
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
	 * Credit User Bonus ROI
	 *
	 * @return boolean status
	 *        
	 */
	public function creditROI($start_date = null, $end_date = null) {
		$status = false;
		if (empty ( $start_date )) {
			$datetime = new \DateTime ();
			$start_date = $datetime->format ( 'Y-m-d' );
		}
		if (empty ( $end_date )) {
			$datetime = new \DateTime ();
			$end_date = $datetime->format ( 'Y-m-d' );
		}
		
		$QBonusROI = $this->getDatabase ();
		$QBonusROI->select ();
		$QBonusROI->columns ( array (
				'id' => 'user_bonus_roi_id',
				'user' => 'user_access_id',
				'profit' => 'user_bonus_roi_profit' 
		) );
		$QBonusROI->from ( array (
				'ubr' => 'user_bonus_roi' 
		) );
		$QBonusROI->join ( array (
				'ua' => 'user_access' 
		), 'ua.user_access_id = ubr.user_access_id', array (
				'rank' => 'user_rank_id' 
		) );
		$QBonusROI->where ( array (
				'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubr.user_bonus_roi_status = 0 and ( DATE(ubr.user_bonus_roi_created_date) >= "' . $start_date . '" and DATE(ubr.user_bonus_roi_created_date) <= "' . $end_date . '")' 
		) );
		$QBonusROI->execute ();
		if ($QBonusROI->hasResult ()) {
			$user_id = array ();
			while ( $QBonusROI->valid () ) {
				$rawdata = $QBonusROI->current ();
				if ($this->creditBonus ( 'roi', $rawdata ['user'], $rawdata ['rank'], $rawdata ['profit'], 8050 )) {
					$status = true;
					$user_id [] = $rawdata ['id'];
				}
				$QBonusROI->next ();
			}
			if (is_array ( $user_id ) && sizeof ( $user_id ) > 0) {
				$UROI = $this->getDatabase ();
				$UROI->update ();
				$UROI->table ( 'user_bonus_roi' );
				$UROI->set ( array (
						'user_bonus_roi_status' => 1 
				) );
				$UROI->where ( array (
						'user_bonus_roi_id in ( ' . implode ( ', ', $user_id ) . ')' 
				) );
				$UROI->execute ();
			}
		}
		return $status;
	}
	
	/**
	 * Get User Bonus ROI Setting
	 *
	 * @return array data
	 *        
	 */
	public function getROISetting($id = null) {
		$data = null;
		if (! array_key_exists ( $id, $this->user_roi_setting )) {
			$rawdata = array ();
			$this->user_roi_setting [$id] = $rawdata;
			if (is_numeric ( $id ) && $id > 0) {
				$QROISetting = $this->getDatabase ();
				$QROISetting->select ();
				$QROISetting->columns ( array (
						'bonus_roi_id' => 'user_rank_roi_id',
						'bonus_roi_level' => 'user_rank_roi_level',
						'bonus_roi_type' => 'user_rank_roi_type',
						'bonus_roi_percentage' => 'user_rank_roi_percentage',
						'bonus_roi_amount' => 'user_rank_roi_amount' 
				) );
				$QROISetting->from ( array (
						'ub' => 'user_rank_roi' 
				) );
				$QROISetting->where ( array (
						'ub.user_rank_id' => $id 
				) );
				$QROISetting->execute ();
				if ($QROISetting->hasResult ()) {
					$data2 = array ();
					while ( $QROISetting->valid () ) {
						$data2 = $QROISetting->current ();
						$rawdata [$data2 ['bonus_roi_level']] = $data2;
						$QROISetting->next ();
					}
					$this->user_roi_setting [$id] = $rawdata;
				}
			}
		}
		$data = $this->user_roi_setting [$id];
		return $data;
	}
	
	/**
	 * Calculate User Bonus ROI
	 *
	 * @return boolean status
	 *        
	 */
	public function calculateROI() {
		$roi_bonus = array ();
		$id = $this->getOption ( 'user_rank_id' );
		$roi_setting = $this->getROISetting ( $id );
		if (is_array ( $roi_setting ) && count ( $roi_setting ) > 0) {
			$turn_count = 0;
			foreach ( $roi_setting as $roi_setting_value ) {
				$roi_level = $roi_setting_value ['bonus_roi_level'];
				$roi_type = $roi_setting_value ['bonus_roi_type'];
				$roi_amount = $roi_setting_value ['bonus_roi_amount'];
				$percentage = $roi_setting_value ['bonus_roi_percentage'];
				$roi_percentage = $percentage / 100;
				$roi_percentage = number_format ( $roi_percentage, 2 );
				$roi_profit = $roi_amount * $roi_percentage;
				if ($roi_profit > 0) {
					$turn_count += $roi_type;
					$datetime = new \DateTime ();
					$datetime->add ( new \DateInterval ( 'P' . $turn_count . 'D' ) );
					$start_date = $datetime->format ( 'Y-m-d' );
					
					$roi_bonus [] = array (
							'user_access_id' => $this->getOption ( 'user_access_id' ),
							'user_bonus_roi_status' => 0,
							'user_bonus_roi_level' => $roi_level,
							'user_bonus_roi_amount' => $roi_amount,
							'user_bonus_roi_precentage' => $roi_percentage,
							'user_bonus_roi_profit' => $roi_profit,
							'user_bonus_roi_created_date' => $start_date . ' 00:00:00' 
					);
				}
			}
		}
		if (is_array ( $roi_bonus ) && count ( $roi_bonus ) > 0) {
			$IBonus = $this->getDatabase ();
			$IBonus->insert ();
			$IBonus->into ( 'user_bonus_roi' );
			$IBonus->columns ( array (
					'user_access_id',
					'user_bonus_roi_status',
					'user_bonus_roi_level',
					'user_bonus_roi_amount',
					'user_bonus_roi_precentage',
					'user_bonus_roi_profit',
					'user_bonus_roi_created_date' 
			) );
			$IBonus->values ( $roi_bonus, 'multiple' );
			$IBonus->execute ();
			
			$this->updateCalculated ( 'roi' );
		}
	}
	
	/**
	 * Get User Bonus ROI
	 *
	 * @return array data
	 *        
	 */
	public function getROIData() {
		$details = array ();
		$summary = array (
				'start_date' => '',
				'end_date' => '',
				'total_level' => '',
				'total_done' => '',
				'total_amount' => '' 
		);
		$count = 0;
		$done = 0;
		$amount = 0;
		$start_date = "";
		$end_date = "";
		
		$QBonusROI = $this->getDatabase ();
		$QBonusROI->select ();
		$QBonusROI->columns ( array (
				'user_id' => 'user_access_id',
				'status' => 'user_bonus_roi_status',
				'level' => 'user_bonus_roi_level',
				'amount' => 'user_bonus_roi_amount',
				'percentage' => 'user_bonus_roi_precentage',
				'profit' => 'user_bonus_roi_profit',
				'created_date' => 'user_bonus_roi_created_date' 
		) );
		$QBonusROI->from ( array (
				'ubr' => 'user_bonus_roi' 
		) );
		$QBonusROI->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id = ubr.user_access_id', array (
				'username' => 'user_access_username' 
		) );
		$QBonusROI->where ( array (
				'ubr.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '"' 
		) );
		$QBonusROI->order ( array (
				'ubr.user_bonus_roi_level ASC' 
		) );
		$QBonusROI->execute ();
		if ($QBonusROI->hasResult ()) {
			while ( $QBonusROI->valid () ) {
				$rawdata = $QBonusROI->current ();
				$datetime = new \DateTime ( $rawdata ['created_date'] );
				$rawdata ['created_date_format'] = $datetime->format ( 'd-m-Y' );
				if ($rawdata ['status'] == "1") {
					$done ++;
					$amount += $rawdata ['profit'];
				}
				if ($rawdata ['level'] == "1") {
					$start_date = $rawdata ['created_date_format'];
				}
				$rawdata ['percentage_format'] = $rawdata ['percentage'] * 100;
				$end_date = $rawdata ['created_date_format'];
				$details [$rawdata ['level']] = $rawdata;
				$count ++;
				$QBonusROI->next ();
			}
		}
		
		$summary ['start_date'] = $start_date;
		$summary ['end_date'] = $end_date;
		$summary ['total_level'] = $count;
		$summary ['total_done'] = $done;
		$summary ['total_amount'] = $amount;
		$data = array (
				'summary' => $summary,
				'details' => $details 
		);
		return $data;
	}
}

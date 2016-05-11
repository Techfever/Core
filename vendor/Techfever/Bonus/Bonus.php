<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Bonus extends Sponsor {
	
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
	 * @var Bonus
	 *
	 */
	private $user_bonus_data = null;
	
	/**
	 *
	 * @var Structure
	 *
	 */
	private $user_structure = null;
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		
		$this->getUserStructure ();
		$this->getUserStructure ()->setOption ( 'user', $options ['user_access_id'] );
		$options ['user_placement_id'] = $this->getUserStructure ()->getPlacementID ();
		$options ['user_placement_username'] = $this->getUserStructure ()->getPlacementUsername ();
		$options ['user_placement_rank_id'] = $this->getUserManagement ()->getRankID ( $options ['user_placement_id'] );
		$options ['user_sponsor_id'] = $this->getUserStructure ()->getSponsorID ();
		$options ['user_sponsor_username'] = $this->getUserStructure ()->getSponsorUsername ();
		$options ['user_sponsor_rank_id'] = $this->getUserManagement ()->getRankID ( $options ['user_sponsor_id'] );
		$options ['user_rank_id'] = $this->getUserManagement ()->getRankID ( $options ['user_access_id'] );
		if (! array_key_exists ( 'execute_date', $options )) {
			$datetime = new \DateTime ();
			$options ['execute_date'] = $datetime->format ( 'Y-m-d' );
		}
		$RankData = $this->getUserRank ()->getRank ( $options ['user_rank_id'] );
		$data ['user_rank_price_pv'] = $RankData ['price_pv'];
		$options ['amount'] = $data ['user_rank_price_pv'];
		parent::__construct ( $options );
		$this->setOptions ( $options );
	}
	
	/**
	 * Calculate User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function calculateBonus() {
		if (BONUS_SPONSOR_ENABLE == "True") {
			if (! $this->isCalculated ( 'sponsor' )) {
				$this->calculateSponsor ();
				$this->calculateSponsorRebate ();
			}
		}
		if (BONUS_PAIRING_ENABLE == "True") {
			if (! $this->isCalculated ( 'pairing' )) {
				$this->calculatePairing ();
			}
		}
		if (BONUS_MATCHING_ENABLE == "True") {
			if (! $this->isCalculated ( 'matching' )) {
				$this->calculateMatching ();
			}
		}
		if (BONUS_ROI_ENABLE == "True") {
			if (! $this->isCalculated ( 'roi' )) {
				$this->calculateROI ();
			}
		}
	}
	
	/**
	 * Credit User Bonus
	 *
	 * @return boolean status
	 *        
	 */
	public function startCredit() {
		$datetime = new \DateTime ( $this->getOption ( 'execute_date' ) );
		if (BONUS_SPONSOR_ENABLE == "True") {
			$sponsor_status = false;
			$start_date = null;
			$end_date = null;
			if (BONUS_SPONSOR_REALTIME_CREDIT_ENABLE == "True") {
				$sponsor_status = true;
				$start_date = $this->getOption ( 'execute_date' );
				$end_date = $this->getOption ( 'execute_date' );
			} elseif (BONUS_SPONSOR_DAILY_CREDIT_ENABLE == "True") {
				$sponsor_status = true;
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$start_date = $datetime->format ( 'Y-m-d' );
				$end_date = $datetime->format ( 'Y-m-d' );
			} elseif (BONUS_SPONSOR_WEEKLY_CREDIT_ENABLE == "True") {
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$week = $datetime->format ( 'N' );
				if ($week == 7) {
					$end_date = $datetime->format ( 'Y-m-d' );
					$datetime->sub ( new \DateInterval ( 'P7D' ) );
					$start_date = $datetime->format ( 'Y-m-d' );
					$sponsor_status = true;
				}
			} elseif (BONUS_SPONSOR_MONTHLY_CREDIT_ENABLE == "True") {
				$current_date = $datetime->format ( 'j' );
				if ($current_date == 1) {
					$datetime->sub ( new \DateInterval ( 'P1D' ) );
					$end_date = $datetime->format ( 'Y-m-d' );
					$start_date = $datetime->format ( 'Y-m-01' );
					$sponsor_status = true;
				}
			}
			if ($sponsor_status) {
				$this->creditSponsor ( $start_date, $end_date );
				$this->creditSponsorRebate ( $start_date, $end_date );
			}
		}
		if (BONUS_PAIRING_ENABLE == "True") {
			$pairing_status = false;
			$start_date = null;
			$end_date = null;
			if (BONUS_PAIRING_REALTIME_CREDIT_ENABLE == "True") {
				$pairing_status = true;
				$start_date = $this->getOption ( 'execute_date' );
				$end_date = $this->getOption ( 'execute_date' );
			} elseif (BONUS_PAIRING_DAILY_CREDIT_ENABLE == "True") {
				$pairing_status = true;
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$start_date = $datetime->format ( 'Y-m-d' );
				$end_date = $datetime->format ( 'Y-m-d' );
			} elseif (BONUS_PAIRING_WEEKLY_CREDIT_ENABLE == "True") {
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$week = $datetime->format ( 'N' );
				if ($week == 7) {
					$end_date = $datetime->format ( 'Y-m-d' );
					$datetime->sub ( new \DateInterval ( 'P6D' ) );
					$start_date = $datetime->format ( 'Y-m-d' );
					$pairing_status = true;
				}
			} elseif (BONUS_PAIRING_MONTHLY_CREDIT_ENABLE == "True") {
				$current_date = $datetime->format ( 'j' );
				if ($current_date == 1) {
					$datetime->sub ( new \DateInterval ( 'P1D' ) );
					$end_date = $datetime->format ( 'Y-m-d' );
					$start_date = $datetime->format ( 'Y-m-01' );
					$pairing_status = true;
				}
			}
			
			if ($pairing_status) {
				$this->creditPairing ( $start_date, $end_date );
			}
		}
		if (BONUS_MATCHING_ENABLE == "True") {
			$matching_status = false;
			$start_date = null;
			$end_date = null;
			if (BONUS_MATCHING_REALTIME_CREDIT_ENABLE == "True") {
				$matching_status = true;
				$start_date = $this->getOption ( 'execute_date' );
				$end_date = $this->getOption ( 'execute_date' );
			} elseif (BONUS_MATCHING_DAILY_CREDIT_ENABLE == "True") {
				$matching_status = true;
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$start_date = $datetime->format ( 'Y-m-d' );
				$end_date = $datetime->format ( 'Y-m-d' );
			} elseif (BONUS_MATCHING_WEEKLY_CREDIT_ENABLE == "True") {
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$week = $datetime->format ( 'N' );
				if ($week == 7) {
					$end_date = $datetime->format ( 'Y-m-d' );
					$datetime->sub ( new \DateInterval ( 'P7D' ) );
					$start_date = $datetime->format ( 'Y-m-d' );
					$matching_status = true;
				}
			} elseif (BONUS_MATCHING_MONTHLY_CREDIT_ENABLE == "True") {
				$current_date = $datetime->format ( 'j' );
				if ($current_date == 1) {
					$datetime->sub ( new \DateInterval ( 'P1D' ) );
					$end_date = $datetime->format ( 'Y-m-d' );
					$start_date = $datetime->format ( 'Y-m-01' );
					$matching_status = true;
				}
			}
			if ($matching_status) {
				$this->creditMatching ( $start_date, $end_date );
			}
		}
		if (BONUS_ROI_ENABLE == "True") {
			$roi_status = false;
			$start_date = null;
			$end_date = null;
			if (BONUS_ROI_REALTIME_CREDIT_ENABLE == "True") {
				$roi_status = true;
				$start_date = $this->getOption ( 'execute_date' );
				$end_date = $this->getOption ( 'execute_date' );
			} elseif (BONUS_ROI_DAILY_CREDIT_ENABLE == "True") {
				$roi_status = true;
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$start_date = $datetime->format ( 'Y-m-d' );
				$end_date = $datetime->format ( 'Y-m-d' );
			} elseif (BONUS_ROI_WEEKLY_CREDIT_ENABLE == "True") {
				$datetime->sub ( new \DateInterval ( 'P1D' ) );
				$week = $datetime->format ( 'N' );
				if ($week == 7) {
					$end_date = $datetime->format ( 'Y-m-d' );
					$datetime->sub ( new \DateInterval ( 'P7D' ) );
					$start_date = $datetime->format ( 'Y-m-d' );
					$roi_status = true;
				}
			} elseif (BONUS_ROI_MONTHLY_CREDIT_ENABLE == "True") {
				$current_date = $datetime->format ( 'j' );
				if ($current_date == 1) {
					$datetime->sub ( new \DateInterval ( 'P1D' ) );
					$end_date = $datetime->format ( 'Y-m-d' );
					$start_date = $datetime->format ( 'Y-m-01' );
					$roi_status = true;
				}
			}
			if ($roi_status) {
				$this->creditROI ( $start_date, $end_date );
			}
		}
	}
}

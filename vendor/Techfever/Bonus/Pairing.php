<?php

namespace Techfever\Bonus;

use Techfever\Exception;

class Pairing extends Matching {
	
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
	 * @var Bonus Pairing
	 *     
	 */
	private $user_pairing_setting = array ();
	
	/**
	 *
	 * @var Placement Backward Id
	 *     
	 */
	private $placement_backward_id = null;
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
	 * Credit User Bonus Pairing
	 *
	 * @return boolean status
	 *        
	 */
	public function creditPairing($start_date = null, $end_date = null) {
		$status = false;
		if (empty ( $start_date )) {
			$datetime = new \DateTime ();
			$start_date = $datetime->format ( 'Y-m-d' );
		}
		if (empty ( $end_date )) {
			$datetime = new \DateTime ();
			$end_date = $datetime->format ( 'Y-m-d' );
		}
		
		$QBonusPairing = $this->getDatabase ();
		$QBonusPairing->select ();
		$QBonusPairing->columns ( array (
				'id' => 'user_bonus_pairing_id',
				'user' => 'user_access_id',
				'profit' => 'user_bonus_pairing_profit' 
		) );
		$QBonusPairing->from ( array (
				'ubs' => 'user_bonus_pairing' 
		) );
		$QBonusPairing->join ( array (
				'ua' => 'user_access' 
		), 'ua.user_access_id = ubs.user_access_id', array (
				'rank' => 'user_rank_id' 
		) );
		$QBonusPairing->where ( array (
				'DATE(ua.user_access_activated_date) <= "' . $end_date . '" and ubs.user_bonus_pairing_status = 0 and ( DATE(ubs.user_bonus_pairing_created_date) >= "' . $start_date . '" and DATE(ubs.user_bonus_pairing_created_date) <= "' . $end_date . '")' 
		) );
		$QBonusPairing->execute ();
		if ($QBonusPairing->hasResult ()) {
			$user_id = array ();
			while ( $QBonusPairing->valid () ) {
				$rawdata = $QBonusPairing->current ();
				$current_username = $this->getUserManagement ()->getUsername ( $rawdata ['user'] );
				$qualified_status = $this->verifyQualified ( $current_username );
				if ($qualified_status) {
					if ($this->creditBonus ( 'pairing', $rawdata ['user'], $rawdata ['rank'], $rawdata ['profit'], 8020 )) {
						$user_id [] = $rawdata ['id'];
						$status = true;
					}
				}
				$QBonusPairing->next ();
			}
			if (is_array ( $user_id ) && sizeof ( $user_id ) > 0) {
				$UPairing = $this->getDatabase ();
				$UPairing->update ();
				$UPairing->table ( 'user_bonus_pairing' );
				$UPairing->set ( array (
						'user_bonus_pairing_status' => 1 
				) );
				$UPairing->where ( array (
						'user_bonus_pairing_id in ( ' . implode ( ', ', $user_id ) . ')' 
				) );
				$UPairing->execute ();
			}
		}
		return $status;
	}
	
	/**
	 * Get User Bonus Pairing Setting
	 *
	 * @return array data
	 *        
	 */
	public function getPairingSetting($id = null) {
		$data = null;
		if (! array_key_exists ( $id, $this->user_pairing_setting )) {
			$rawdata = 0;
			$this->user_pairing_setting [$id] = $rawdata;
			if (is_numeric ( $id ) && $id > 0) {
				$QPairingSetting = $this->getDatabase ();
				$QPairingSetting->select ();
				$QPairingSetting->columns ( array (
						'bonus_pairing_id' => 'user_rank_pairing_id',
						'bonus_pairing_daily_flush_small' => 'user_rank_pairing_daily_flush_small',
						'bonus_pairing_daily_flush_big' => 'user_rank_pairing_daily_flush_big',
						'bonus_pairing_daily_flush_min' => 'user_rank_pairing_daily_min',
						'bonus_pairing_daily_flush_max' => 'user_rank_pairing_daily_max',
						'bonus_pairing_weekly_flush_small' => 'user_rank_pairing_weekly_flush_small',
						'bonus_pairing_weekly_flush_big' => 'user_rank_pairing_weekly_flush_big',
						'bonus_pairing_weekly_flush_min' => 'user_rank_pairing_weekly_min',
						'bonus_pairing_weekly_flush_max' => 'user_rank_pairing_weekly_max',
						'bonus_pairing_monthly_flush_small' => 'user_rank_pairing_monthly_flush_small',
						'bonus_pairing_monthly_flush_big' => 'user_rank_pairing_monthly_flush_big',
						'bonus_pairing_monthly_flush_min' => 'user_rank_pairing_monthly_min',
						'bonus_pairing_monthly_flush_max' => 'user_rank_pairing_monthly_max',
						'bonus_pairing_perlot_amount' => 'user_rank_pairing_perlot_amount',
						'bonus_pairing_qualification_hierachy_status' => 'user_rank_pairing_qualification_hierachy_status',
						'bonus_pairing_qualification_hierachy_total' => 'user_rank_pairing_qualification_hierachy_total',
						'bonus_pairing_qualification_hierachy_type' => 'user_rank_pairing_qualification_hierachy_type' 
				) );
				$QPairingSetting->from ( array (
						'up' => 'user_rank_pairing' 
				) );
				$QPairingSetting->where ( array (
						'up.user_rank_id' => $id 
				) );
				$QPairingSetting->execute ();
				if ($QPairingSetting->hasResult ()) {
					$rawdata = $QPairingSetting->current ();
					$QPairingBatch = $this->getDatabase ();
					$QPairingBatch->select ();
					$QPairingBatch->columns ( array (
							'bonus_pairing_batch_start' => 'user_rank_pairing_batch_start',
							'bonus_pairing_batch_end' => 'user_rank_pairing_batch_end',
							'bonus_pairing_batch_percentage' => 'user_rank_pairing_batch_percentage' 
					) );
					$QPairingBatch->from ( array (
							'upb' => 'user_rank_pairing_batch' 
					) );
					$QPairingBatch->where ( array (
							'upb.user_rank_pairing_id' => $rawdata ['bonus_pairing_id'] 
					) );
					$QPairingBatch->order ( array (
							'bonus_pairing_batch_start ASC' 
					) );
					$QPairingBatch->execute ();
					if ($QPairingBatch->hasResult ()) {
						$rawdatabatch = $QPairingBatch->toArray ();
						$rawdata ['bonus_pairing_batch'] = $rawdatabatch;
					}
					$this->user_pairing_setting [$id] = $rawdata;
				}
			}
		}
		$data = $this->user_pairing_setting [$id];
		return $data;
	}
	
	/**
	 * Calculate User Bonus Pairing
	 *
	 * @return boolean status
	 *        
	 */
	public function calculatePairing() {
		$this->throwBackward ();
		$exist_id = $this->getPlacementBackward ( true );
		if (BONUS_PAIRING_USE_SPONSOR == "True") {
			$type = 'sponsor';
		} elseif (BONUS_PAIRING_USE_PLACEMENT == "True") {
			$type = 'placement';
		}
		$structure_data = array ();
		if (is_array ( $exist_id ) && count ( $exist_id ) > 0) {
			$pairing_bonus = array ();
			$datetime = new \DateTime ();
			
			foreach ( $exist_id as $exist_value ) {
				$username = $this->getUserManagement ()->getUsername ( $exist_value );
				$rank_id = $this->getUserManagement ()->getRankID ( $exist_value );
				$bonus_pairing_setting = $this->getPairingSetting ( $rank_id );
				$calculation_continue = true;
				if (is_array ( $bonus_pairing_setting ) && count ( $bonus_pairing_setting ) > 0) {
					$pairing_perlot_amount = $bonus_pairing_setting ['bonus_pairing_perlot_amount'];
					$pairing_qualification_hierachy_status = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_status'];
					$pairing_qualification_hierachy_type = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_type'];
					$pairing_qualification_hierachy_total = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_total'];
					if ($pairing_qualification_hierachy_status == 1) {
						$username = $this->getOption ( 'user_sponsor_username' );
						$QHierarchyVerify = $this->getDatabase ();
						$QHierarchyVerify->select ();
						$QHierarchyVerify->columns ( array (
								'user_access_id' 
						) );
						$QHierarchyVerify->from ( array (
								'uh' => 'user_hierarchy' 
						) );
						$QHierarchyVerify->where ( array (
								'uh.user_hierarchy_' . $pairing_qualification_hierachy_type . '_username = "' . $username . '"',
								'uh.user_access_id <= ' . $this->getOption ( 'user_access_id' ) 
						) );
						$QHierarchyVerify->execute ();
						if ($QHierarchyVerify->hasResult ()) {
							$total_hierarchy = $QHierarchyVerify->count ();
							if ($total_hierarchy <= $pairing_qualification_hierachy_total) {
								$calculation_continue = false;
								if ($total_hierarchy == $pairing_qualification_hierachy_total) {
									$UPairing = $this->getDatabase ();
									$UPairing->update ();
									$UPairing->table ( 'user_pairing' );
									$UPairing->set ( array (
											'user_pairing_calculate_status' => ( int ) 1 
									) );
									$UPairing->where ( array (
											'user_access_id = ' . $exist_value,
											'DATE(user_pairing_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' 
									) );
									$UPairing->execute ();
									
									$percentage = (20 / 100);
									$amount = $pairing_perlot_amount;
									$profit = $amount * $percentage;
									$pairing_bonus [] = array (
											'user_access_id' => $exist_value,
											'user_bonus_pairing_amount' => $amount,
											'user_bonus_pairing_precentage' => $percentage,
											'user_bonus_pairing_profit' => $profit,
											'user_bonus_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
									);
									$pairing_bonus [] = array (
											'user_access_id' => $exist_value,
											'user_bonus_pairing_amount' => $amount,
											'user_bonus_pairing_precentage' => $percentage,
											'user_bonus_pairing_profit' => $profit,
											'user_bonus_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
									);
								}
							} else {
							}
						} else {
							$calculation_continue = false;
						}
					}
					if ($calculation_continue) {
						$username = $this->getUserManagement ()->getUsername ( $exist_value );
						$rank_id = $this->getUserManagement ()->getRankID ( $exist_value );
						$QHierarchy = $this->getDatabase ();
						$QHierarchy->select ();
						$QHierarchy->columns ( array (
								'*' 
						) );
						$QHierarchy->from ( array (
								'uh' => 'user_hierarchy' 
						) );
						$hierarchy_join_expression = new \Zend\Db\Sql\Expression ( 'up.user_access_id  = uh.user_access_id and DATE(up.user_pairing_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' );
						$QHierarchy->join ( array (
								'up' => 'user_pairing' 
						), $hierarchy_join_expression, array (
								'*' 
						) );
						$QHierarchy->where ( array (
								'uh.user_hierarchy_' . $type . '_username = "' . $username . '"',
								'up.user_pairing_match_max = 0' 
						) );
						$QHierarchy->order ( array (
								'user_hierarchy_' . $type . ' ASC' 
						) );
						$QHierarchy->execute ();
						if ($QHierarchy->hasResult ()) {
							$data = array ();
							$count = $QHierarchy->count ();
							$pairing_min = 0;
							$pairing_match_status = true;
							if ($count < 1) {
								$pairing_match_status = false;
							}
							if ($count > 1) {
								while ( $QHierarchy->valid () ) {
									$rawdata = $QHierarchy->current ();
									
									$position = substr ( $rawdata ['user_hierarchy_' . $type], - 1 );
									$rawdata ['user_hierarchy_' . $type . '_position'] = $position;
									$rawdata ['user_pairing_balance'] = $rawdata ['user_pairing_current'] - $rawdata ['user_pairing_match'];
									if ($pairing_min == 0) {
										$pairing_min = $rawdata ['user_pairing_balance'];
									}
									if ($rawdata ['user_pairing_balance'] < $pairing_min && $rawdata ['user_pairing_balance'] > 0) {
										$pairing_min = $rawdata ['user_pairing_balance'];
									}
									if ($rawdata ['user_pairing_balance'] == 0) {
										$pairing_match_status = false;
									}
									$rawdata ['user_pairing_match_current'] = 0;
									$data [$position] = $rawdata;
									$QHierarchy->next ();
								}
								if ($pairing_match_status && array_key_exists ( 'bonus_pairing_batch', $bonus_pairing_setting )) {
									$total_lot = $pairing_min / $pairing_perlot_amount;
									if ($total_lot > 0) {
										$pairing_balance = $total_lot;
										$pairing_match_total_amount = $pairing_min + $rawdata ['user_pairing_match'];
										$pairing_match_total_lot = ($pairing_min + $rawdata ['user_pairing_match']) / $pairing_perlot_amount;
										$pairing_match_percentage = 0;
										$pairing_match_max_lot = 0;
										$pairing_match_max_percentage = 0;
										$pairing_match_max_status = false;
										$pairing_max_status = false;
										foreach ( $bonus_pairing_setting ['bonus_pairing_batch'] as $bonus_pairing_value ) {
											$pairing_match_max_lot = $bonus_pairing_value ['bonus_pairing_batch_end'];
											$pairing_match_max_percentage = $bonus_pairing_value ['bonus_pairing_batch_percentage'];
											if ($pairing_match_total_lot >= $bonus_pairing_value ['bonus_pairing_batch_start'] && $pairing_match_total_lot <= $bonus_pairing_value ['bonus_pairing_batch_end']) {
												$pairing_match_percentage = $bonus_pairing_value ['bonus_pairing_batch_percentage'];
												$pairing_match_max_status = true;
											}
											$pairing_total_lot = $bonus_pairing_value ['bonus_pairing_batch_end'] - $bonus_pairing_value ['bonus_pairing_batch_start'] + 1;
											$pairing_balance_before = $pairing_balance;
											$pairing_balance = $pairing_balance - $pairing_total_lot;
											$percentage = ($bonus_pairing_value ['bonus_pairing_batch_percentage'] / 100);
											if ($pairing_balance > 0) {
												if ($pairing_balance_before > $pairing_total_lot) {
													$amount = ($pairing_total_lot * $pairing_perlot_amount);
													$profit = $amount * $percentage;
													$pairing_bonus [] = array (
															'user_access_id' => $exist_value,
															'user_bonus_pairing_amount' => $amount,
															'user_bonus_pairing_precentage' => $percentage,
															'user_bonus_pairing_profit' => $profit,
															'user_bonus_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
													);
												} elseif ($pairing_balance_before < $pairing_total_lot) {
													$amount = ($pairing_balance_before * $pairing_perlot_amount);
													$profit = $amount * $percentage;
													$pairing_bonus [] = array (
															'user_access_id' => $exist_value,
															'user_bonus_pairing_amount' => $amount,
															'user_bonus_pairing_precentage' => $percentage,
															'user_bonus_pairing_profit' => $profit,
															'user_bonus_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
													);
												}
											} elseif ($pairing_balance_before > 0) {
												$amount = ($pairing_balance_before * $pairing_perlot_amount);
												$profit = $amount * $percentage;
												$pairing_bonus [] = array (
														'user_access_id' => $exist_value,
														'user_bonus_pairing_amount' => $amount,
														'user_bonus_pairing_precentage' => $percentage,
														'user_bonus_pairing_profit' => $profit,
														'user_bonus_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
												);
											}
										}
										if (! $pairing_match_max_status) {
											$pairing_min = $pairing_match_max_lot * $pairing_perlot_amount;
											$pairing_match_percentage = $pairing_match_max_percentage;
											$pairing_match_total_amount = $pairing_min;
											$pairing_max_status = true;
										}
										if ($pairing_match_percentage > 0 && $pairing_match_total_amount > 0) {
											$pairing_profit = $pairing_match_total_amount * ($pairing_match_percentage / 100);
											foreach ( $data as $data_key => $data_value ) {
												$data_value ['user_pairing_match_current'] = $pairing_min;
												$this->updateUserPairingMatch ( $data_value ['user_access_id'], $pairing_min, $pairing_max_status );
												$data [$data_key] = $data_value;
											}
										}
									}
								}
								$structure_data [$exist_value] = $data;
							}
						}
					}
				}
			}
			if (is_array ( $pairing_bonus ) && count ( $pairing_bonus ) > 0) {
				$IBonus = $this->getDatabase ();
				$IBonus->insert ();
				$IBonus->into ( 'user_bonus_pairing' );
				$IBonus->columns ( array (
						'user_access_id',
						'user_bonus_pairing_amount',
						'user_bonus_pairing_precentage',
						'user_bonus_pairing_profit',
						'user_bonus_pairing_created_date' 
				) );
				$IBonus->values ( $pairing_bonus, 'multiple' );
				$IBonus->execute ();
				
				$this->updateCalculated ( 'pairing' );
			}
		}
	}
	/**
	 * verifyQualified
	 *
	 * @return boolean status
	 *        
	 */
	public function verifyQualified($username) {
		$continue_cal = false;
		$user_id = $this->getUserManagement ()->getID ( $username );
		$rank_id = $this->getUserManagement ()->getRankID ( $user_id );
		$bonus_pairing_setting = $this->getPairingSetting ( $rank_id );
		$pairing_qualification_hierachy_status = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_status'];
		$pairing_qualification_hierachy_type = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_type'];
		$pairing_qualification_hierachy_total = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_total'];
		if ($pairing_qualification_hierachy_status == 1) {
			if (is_array ( $bonus_pairing_setting ) && count ( $bonus_pairing_setting ) > 0) {
				$pairing_qualification_hierachy_total = $bonus_pairing_setting ['bonus_pairing_qualification_hierachy_total'];
				
				$QHierarchyVerify = $this->getDatabase ();
				$QHierarchyVerify->select ();
				$QHierarchyVerify->columns ( array (
						'user_access_id' 
				) );
				$QHierarchyVerify->from ( array (
						'uh' => 'user_hierarchy' 
				) );
				$QHierarchyVerify->where ( array (
						'uh.user_hierarchy_' . $pairing_qualification_hierachy_type . '_username = "' . $username . '" and DATE(uh.user_hierarchy_created_date) <= "' . $this->getOption ( 'execute_date' ) . '"' 
				) );
				$QHierarchyVerify->execute ();
				if ($QHierarchyVerify->count () >= $pairing_qualification_hierachy_total) {
					$continue_cal = true;
				}
			}
		} else {
			$continue_cal = true;
		}
		return $continue_cal;
	}
	/**
	 * getPlacementBackward
	 *
	 * @return array id
	 *        
	 */
	public function getPlacementBackward($ignore_qualification = false) {
		if (! is_array ( $this->placement_backward_id ) && count ( $this->placement_backward_id ) < 1) {
			$exist_id = array ();
			$path = null;
			if (BONUS_PAIRING_USE_SPONSOR == "True") {
				$path = $this->getUserStructure ()->getSponsorPath ();
			} elseif (BONUS_PAIRING_USE_PLACEMENT == "True") {
				$path = $this->getUserStructure ()->getPlacementPath ();
			}
			$this->generatePairingLine ();
			if (is_string ( $path ) && strlen ( $path ) > 0) {
				$path_raw = explode ( '|', $path );
				if (is_array ( $path_raw ) && count ( $path_raw ) > 0) {
					$path_array = array ();
					$path_join = null;
					foreach ( $path_raw as $path_raw_value ) {
						$path_join .= $path_raw_value . '|';
						$path_array [] = substr ( $path_join, 0, - 1 );
					}
					if (is_array ( $path_array ) && count ( $path_array ) > 0) {
						$path_own = array_pop ( $path_array );
					}
					if (is_array ( $path_array ) && count ( $path_array ) > 0) {
						$QUserHierarchy = $this->getDatabase ();
						$QUserHierarchy->select ();
						$QUserHierarchy->columns ( array (
								'id' => 'user_access_id' 
						) );
						$QUserHierarchy->from ( array (
								'ub' => 'user_hierarchy' 
						) );
						if (BONUS_PAIRING_USE_SPONSOR == "True") {
							$QUserHierarchy->where ( array (
									'ub.user_hierarchy_sponsor in ("' . implode ( '", "', $path_array ) . '")' 
							) );
						} elseif (BONUS_PAIRING_USE_PLACEMENT == "True") {
							$QUserHierarchy->where ( array (
									'ub.user_hierarchy_placement in ("' . implode ( '", "', $path_array ) . '")' 
							) );
						}
						$QUserHierarchy->execute ();
						if ($QUserHierarchy->hasResult ()) {
							while ( $QUserHierarchy->valid () ) {
								$rawdata = $QUserHierarchy->current ();
								$qualified_status = false;
								if (! $ignore_qualification) {
									$current_username = $this->getUserManagement ()->getUsername ( $rawdata ['id'] );
									$qualified_status = $this->verifyQualified ( $current_username );
								} else {
									$qualified_status = true;
								}
								if ($qualified_status) {
									$exist_id [] = $rawdata ['id'];
								}
								$QUserHierarchy->next ();
							}
							$this->placement_backward_id = $exist_id;
						}
					}
				}
			}
		}
		return $this->placement_backward_id;
	}
	
	/**
	 * Update User Pairing Match
	 *
	 * @return boolean status
	 *        
	 */
	public function updateUserPairingMatch($user_id, $pairing_matching = 0, $pairing_matching_max_status = false) {
		$status = false;
		if (is_numeric ( $user_id ) && $user_id > 0) {
			$UPairing = $this->getDatabase ();
			$UPairing->update ();
			$UPairing->table ( 'user_pairing' );
			$UPairing->set ( array (
					'user_pairing_match' => new \Zend\Db\Sql\Expression ( "user_pairing_match + " . ($pairing_matching) ),
					'user_pairing_match_max' => ( int ) $pairing_matching_max_status 
			) );
			$UPairing->where ( array (
					'user_access_id = ' . $user_id 
			) );
			$UPairing->execute ();
			if ($UPairing->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Throw Backward Amount
	 *
	 * @return boolean status
	 *        
	 */
	public function throwBackward() {
		$status = true;
		$exist_id = $this->getPlacementBackward ();
		$exist_id [] = $this->getOption ( 'user_access_id' );
		
		$user_amount = $this->getOption ( 'amount' );
		$UPairing = $this->getDatabase ();
		$UPairing->update ();
		$UPairing->table ( 'user_pairing' );
		$UPairing->set ( array (
				'user_pairing_current' => new \Zend\Db\Sql\Expression ( "user_pairing_current + " . ($user_amount) ) 
		) );
		$UPairing->where ( array (
				'user_access_id in (' . implode ( ', ', $exist_id ) . ')',
				'DATE(user_pairing_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' 
		) );
		$UPairing->execute ();
		return $status;
	}
	
	/**
	 * generate Pairing line
	 *
	 * @return boolean status
	 *        
	 */
	public function generatePairingLine() {
		$exist_id = null;
		$Result = null;
		$QPairing = $this->getDatabase ();
		$QPairing->select ();
		$QPairing->columns ( array (
				'id' => 'user_access_id' 
		) );
		$QPairing->from ( array (
				'up' => 'user_pairing' 
		) );
		$QPairing->where ( array (
				'DATE(up.user_pairing_created_date) = "' . $this->getOption ( 'execute_date' ) . '"' 
		) );
		$QPairing->execute ();
		if ($QPairing->hasResult ()) {
			$exist_id = array ();
			while ( $QPairing->valid () ) {
				$rawdata = $QPairing->current ();
				$exist_id [] = $rawdata ['id'];
				$QPairing->next ();
			}
		}
		$QUser = $this->getDatabase ();
		$QUser->select ();
		$QUser->columns ( array (
				'id' => 'user_access_id' 
		) );
		$QUser->from ( array (
				'ua' => 'user_access' 
		) );
		$join_expression = new \Zend\Db\Sql\Expression ( 'up.user_access_id  = ua.user_access_id and DATE(up.user_pairing_created_date) = DATE_SUB(' . $this->getOption ( 'execute_date' ) . ',INTERVAL 1 DAY)' );
		$QUser->join ( array (
				'up' => 'user_pairing' 
		), $join_expression, array (
				'user_pairing_current',
				'user_pairing_match',
				'user_pairing_calculate_status',
				'user_access_id' 
		), 'right' );
		if (is_array ( $exist_id ) && count ( $exist_id ) > 0) {
			$QUser->where ( array (
					'ua.user_access_id not in (' . implode ( ', ', $exist_id ) . ')',
					'DATE(ua.user_access_activated_date) <= "' . $this->getOption ( 'execute_date' ) . '"' 
			) );
		}
		$QUser->order ( array (
				'ua.user_access_id ASC' 
		) );
		$QUser->execute ();
		$Result = $QUser;
		if (! $QUser->hasResult ()) {
			$QUser2 = $this->getDatabase ();
			$QUser2->select ();
			$QUser2->columns ( array (
					'user_access_id' => 'user_access_id' 
			) );
			$QUser2->from ( array (
					'ua' => 'user_access' 
			) );
			$join_expression = new \Zend\Db\Sql\Expression ( 'up.user_access_id  = ua.user_access_id and DATE(up.user_pairing_created_date) = DATE_SUB(' . $this->getOption ( 'execute_date' ) . ',INTERVAL 1 DAY)' );
			$QUser2->join ( array (
					'up' => 'user_pairing' 
			), $join_expression, array (
					'user_pairing_current',
					'user_pairing_match',
					'user_pairing_calculate_status' 
			), 'left' );
			if (is_array ( $exist_id ) && count ( $exist_id ) > 0) {
				$QUser2->where ( array (
						'ua.user_access_id not in (' . implode ( ', ', $exist_id ) . ')',
						'DATE(ua.user_access_activated_date) <= "' . $this->getOption ( 'execute_date' ) . '"' 
				) );
			}
			$QUser2->order ( array (
					'ua.user_access_id ASC' 
			) );
			$QUser2->execute ();
			$Result = $QUser2;
		}
		if ($Result->hasResult ()) {
			$create_line = array ();
			$carry_forward = 0;
			while ( $Result->valid () ) {
				$rawdata = $Result->current ();
				$carry_forward = ($rawdata ['user_pairing_current'] - $rawdata ['user_pairing_match']);
				$create_line [$rawdata ['user_access_id']] = array (
						'user_access_id' => $rawdata ['user_access_id'],
						'user_pairing_calculate_status' => ($rawdata ['user_pairing_calculate_status'] != 1 ? 0 : $rawdata ['user_pairing_calculate_status']),
						'user_pairing_current' => ($carry_forward < 1 ? 0 : $carry_forward),
						'user_pairing_created_date' => $this->getOption ( 'execute_date' ) . ' 00:00:00' 
				);
				$Result->next ();
			}
			$ILine = $this->getDatabase ();
			$ILine->insert ();
			$ILine->into ( 'user_pairing' );
			$ILine->columns ( array (
					'user_access_id',
					'user_pairing_calculate_status',
					'user_pairing_current',
					'user_pairing_created_date' 
			) );
			$ILine->values ( $create_line, 'multiple' );
			$ILine->execute ();
		}
	}
	
	/**
	 * Get User Bonus Pairing
	 *
	 * @return array data
	 *        
	 */
	public function getPairingData() {
		$details = array ();
		$summary = array (
				'start_date' => '',
				'end_date' => '',
				'total_pair' => '',
				'total_amount' => '',
				'date' => '' 
		);
		$QBonusPairing = $this->getDatabase ();
		$QBonusPairing->select ();
		$QBonusPairing->columns ( array (
				'id' => 'user_bonus_pairing_id',
				'user_id' => 'user_access_id',
				'amount' => 'user_bonus_pairing_amount',
				'percentage' => 'user_bonus_pairing_precentage',
				'profit' => 'user_bonus_pairing_profit',
				'created_date' => 'user_bonus_pairing_created_date' 
		) );
		$QBonusPairing->from ( array (
				'ubp' => 'user_bonus_pairing' 
		) );
		$QBonusPairing->join ( array (
				'uat' => 'user_access' 
		), 'uat.user_access_id = ubp.user_access_id', array (
				'username' => 'user_access_username' 
		) );
		$QBonusPairing->where ( array (
				'ubp.user_access_id = "' . $this->getOption ( 'user_access_id' ) . '" and ubp.user_bonus_pairing_status = 1 and ( DATE(ubp.user_bonus_pairing_created_date) >= "' . $this->getOption ( 'start_date' ) . '" and DATE(ubp.user_bonus_pairing_created_date) <= "' . $this->getOption ( 'end_date' ) . '")' 
		) );
		$QBonusPairing->order ( array (
				'ubp.user_bonus_pairing_created_date ASC' 
		) );
		$QBonusPairing->execute ();
		$count = 0;
		$amount = 0;
		$date = array ();
		if ($QBonusPairing->hasResult ()) {
			while ( $QBonusPairing->valid () ) {
				$rawdata = $QBonusPairing->current ();
				$datetime = new \DateTime ( $rawdata ['created_date'] );
				$rawdata ['created_date_format'] = $datetime->format ( 'd-m-Y' );
				$rawdata ['percentage_format'] = $rawdata ['percentage'] * 100;
				
				if (! array_key_exists ( $rawdata ['created_date_format'], $details )) {
					$details [$rawdata ['created_date_format']] = array ();
				}
				$details [$rawdata ['created_date_format']] [$rawdata ['id']] = $rawdata;
				$amount += $rawdata ['profit'];
				$count ++;
				if (! array_key_exists ( $rawdata ['created_date_format'], $date )) {
					$date [$rawdata ['created_date_format']] = array (
							'date' => $rawdata ['created_date_format'],
							'total_pair' => 0,
							'total_amount' => 0 
					);
				}
				$total_user = $date [$rawdata ['created_date_format']] ['total_pair'];
				$total_amount = $date [$rawdata ['created_date_format']] ['total_amount'];
				$rank [$rawdata ['created_date_format']] ['total_pair'] = $total_user + 1;
				$rank [$rawdata ['created_date_format']] ['total_amount'] = $total_amount + $rawdata ['profit'];
				$QBonusPairing->next ();
			}
		}
		
		$start_datetime = new \DateTime ( $this->getOption ( 'start_date' ) );
		$summary ['start_date'] = $start_datetime->format ( 'd-m-Y' );
		$end_datetime = new \DateTime ( $this->getOption ( 'end_date' ) );
		$summary ['end_date'] = $end_datetime->format ( 'd-m-Y' );
		$summary ['total_pair'] = $count;
		$summary ['total_amount'] = $amount;
		$summary ['date'] = $date;
		$data = array (
				'summary' => $summary,
				'details' => $details 
		);
		return $data;
	}
}

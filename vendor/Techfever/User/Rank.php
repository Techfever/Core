<?php

namespace Techfever\User;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Rank extends GeneralBase {
	/**
	 *
	 * @var Rank Data
	 *     
	 */
	private $_rank_data = array ();
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'group' => 0,
			'id' => 0 
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
	 * Prepare
	 */
	public function getRankData() {
		if (! is_array ( $this->_rank_data ) || count ( $this->_rank_data ) < 1) {
			$DBRank = $this->getDatabase ();
			$DBRank->select ();
			$DBRank->columns ( array (
					'id' => 'user_rank_id',
					'iso' => 'user_rank_key',
					'price_status' => 'user_rank_price_status',
					'price_dl' => 'user_rank_price_dl',
					'price_pv' => 'user_rank_price_pv',
					'group' => 'user_rank_group_id',
					'is_admin' => 'user_rank_is_admin' 
			) );
			$DBRank->from ( array (
					'ur' => 'user_rank' 
			) );
			$DBRankWhere = array (
					'ur.user_rank_status = 1' 
			);
			if ($this->getOption ( 'group' ) > 0) {
				$DBRankWhere [] = 'ur.user_rank_group_id = ' . $this->getOption ( 'group' );
			}
			if ($this->getOption ( 'id' ) > 0) {
				$DBRankWhere [] = 'ur.user_rank_id = ' . $this->getOption ( 'id' );
			}
			$DBRank->where ( $DBRankWhere );
			$DBRank->order ( array (
					'user_rank_key ASC' 
			) );
			$DBRank->execute ();
			if ($DBRank->hasResult ()) {
				$data = array ();
				while ( $DBRank->valid () ) {
					$data = $DBRank->current ();
					$data ['is_admin'] = ($data ['is_admin'] == "1" ? True : False);
					$this->_rank_data [$data ['id']] = $data;
					$DBRank->next ();
				}
			}
		}
		return $this->_rank_data;
	}
	
	/**
	 * Get Rank Message
	 */
	public function getMessage($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$data = $this->getRank ( $id );
		$iso = $data ['iso'];
		$name = "";
		if (strlen ( $iso ) > 0) {
			$name = $this->getTranslate ( 'text_rank_' . $data ['group'] . '_' . strtolower ( $this->convertToUnderscore ( $data ['iso'], ' ' ) ) );
		}
		return $name;
	}
	
	/**
	 * Get Rank Message
	 */
	public function getMessageKey($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$data = $this->getRank ( $id );
		$iso = $data ['iso'];
		$name = "";
		if (strlen ( $iso ) > 0) {
			$name = $data ['group'] . '_' . strtolower ( $this->convertToUnderscore ( $data ['iso'], ' ' ) );
		}
		return $name;
	}
	
	/**
	 * Get Rank
	 */
	public function verifyRank($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$data = $this->getRankData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			return (array_key_exists ( $id, $data ) ? true : false);
		}
		return false;
	}
	
	/**
	 * Get Rank Group
	 */
	public function getRankGroup($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$group = false;
		if ($this->verifyRank ( $id )) {
			$data = $this->getRank ( $id );
			$group = $data ['group'];
		}
		return $group;
	}
	
	/**
	 * Get Rank
	 */
	public function getRank($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$status = false;
		if ($this->verifyRank ( $id )) {
			$data = $this->getRankData ();
			return (array_key_exists ( $id, $data ) ? $data [$id] : null);
		}
		return false;
	}
	
	/**
	 * Get Rank Price Status
	 */
	public function getRankPriceStatus($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$status = false;
		if ($this->verifyRank ( $id )) {
			$data = $this->getRank ( $id );
			$status = $data ['price_status'];
		}
		return $status;
	}
	
	/**
	 * Get Rank Price DL
	 */
	public function getRankPriceDL($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$price = false;
		if ($this->verifyRank ( $id )) {
			$data = $this->getRank ( $id );
			$price = $data ['price_dl'];
		}
		return $price;
	}
	
	/**
	 * Get Rank Price PV
	 */
	public function getRankPricePV($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$price = false;
		if ($this->verifyRank ( $id )) {
			$data = $this->getRank ( $id );
			$price = $data ['price_pv'];
		}
		return $price;
	}
	
	/**
	 * Get Rank ISO
	 */
	public function getRankISO($id = null) {
		$id = (! empty ( $id ) ? $id : $this->getOption ( 'id' ));
		$data = $this->getRank ( $id );
		$iso = "";
		if (strlen ( $data ['iso'] ) > 0) {
			$iso = $data ['iso'];
		}
		return $iso;
	}
	
	/**
	 * Get Rank All
	 */
	public function getRankAll() {
		return $this->getRankData ();
	}
	
	/**
	 * Get Rank ID All
	 */
	public function getRankIDAll() {
		$data = $this->getRankData ();
		$rankData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $rank ) {
				$rankData [] = $rank ['id'];
			}
		}
		return $rankData;
	}
	
	/**
	 * RankTo Form
	 */
	public function rankToForm() {
		$data = $this->getRankData ();
		$rankData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $rank ) {
				$rankData [$rank ['id']] = $this->getMessage ( $rank ['id'] );
			}
		}
		return $rankData;
	}
}

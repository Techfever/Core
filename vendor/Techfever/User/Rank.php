<?php
namespace Techfever\User;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Rank extends GeneralBase {
	/**
	 * @var Rank Data
	 **/
	private $_rank_data = array();

	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array(
			'group' => 0,
			'id' => 0
	);

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
	 * Prepare
	 */
	public function getRankData() {
		if (!is_array($this->_rank_data) || count($this->_rank_data) < 1) {
			if ($this->getOption('group') > 0 || $this->getOption('id') > 0) {
				$DBRankCache = 'user_rank';
				$DBRank = $this->getDatabase();
				$DBRank->select();
				$DBRank->columns(array(
								'id' => 'user_rank_id',
								'iso' => 'user_rank_key',
								'group' => 'user_rank_group_id',
						));
				$DBRank->from(array(
								'ur' => 'user_rank'
						));
				$DBRankWhere = array(
						'ur.user_rank_status = 1'
				);
				if ($this->getOption('group') > 0) {
					$DBRankWhere[] = 'ur.user_rank_group_id = ' . $this->getOption('group');
					$DBRankCache .= '_' . $this->getOption('group');
				}
				if ($this->getOption('id') > 0) {
					$DBRankWhere[] = 'ur.user_rank_id = ' . $this->getOption('id');
					$DBRankCache .= '_' . $this->getOption('id');
				}
				$DBRank->where($DBRankWhere);
				$DBRank->order(array(
								'user_rank_key ASC'
						));
				$DBRank->setCacheName($DBRankCache);
				$DBRank->execute();
				if ($DBRank->hasResult()) {
					$data = array();
					while ($DBRank->valid()) {
						$data = $DBRank->current();
						$this->_rank_data[$data['id']] = $data;
						$DBRank->next();
					}
				}
			}
		}
		return $this->_rank_data;
	}

	/**
	 * Get Rank Message
	 */
	public function getMessage($id = null) {
		$data = $this->getRank($id);
		$iso = $data['iso'];
		$name = "";
		if (strlen($iso) > 0) {
			$name = $this->getTranslate('text_rank_' . $data['group'] . '_' . strtolower(str_replace(' ', '_', $data['iso'])));
		}
		return $name;
	}

	/**
	 * Get Rank
	 */
	public function getRank($id = null) {
		$data = $this->getRankData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Rank ISO
	 */
	public function getRankISO($id = null) {
		$data = $this->getRank($id);
		$iso = "";
		if (strlen($data['iso']) > 0) {
			$iso = $data['iso'];
		}
		return $iso;
	}

	/**
	 * Get Rank All
	 */
	public function getRankAll() {
		return $this->getRankData();
	}

	/**
	 * Get Rank ID All
	 */
	public function getRankIDAll() {
		$data = $this->getRankData();
		$rankData = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $rank) {
				$rankData[] = $rank['id'];
			}
		}
		return $rankData;
	}

	/**
	 * RankTo Form
	 */
	public function rankToForm() {
		$data = $this->getRankData();
		$rankData = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $rank) {
				$rankData[$rank['id']] = $this->getMessage($rank['id']);
			}
		}
		return $rankData;
	}
}

<?php
namespace Kernel\User;

use Kernel\Database\Database;
use Zend\Db\Sql\Expression;
use Zend\Crypt\Password\Bcrypt;

class Rank {

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
			'group' => 0
	);

	public function __construct($options = array()) {
		if (!is_array($options)) {
			$options = func_get_args();
			$temp['group_id'] = array_shift($options);
			$options = $temp;
		} else {
			$options = array_merge($this->options, $options);
		}
		$this->options = $options;
		self::prepare();
	}

	/**
	 * Returns an option
	 *
	 * @param string $option Option to be returned
	 * @return mixed Returned option
	 * @throws Exception\InvalidArgumentException
	 */
	public function getOption($option) {
		if (isset($this->options) && array_key_exists($option, $this->options)) {
			return $this->options[$option];
		}

		throw new Exception\InvalidArgumentException("Invalid option '$option'");
	}

	/**
	 * Returns all available options
	 *
	 * @return array Array with all available options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets one or multiple options
	 *
	 * @param  array|Traversable $options Options to set
	 * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setOptions($options = array()) {
		if (!is_array($options) && !$options instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->options !== $options) {
			$this->options = $options;
		}
		return $this;
	}

	/**
	 * Set a single option
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options[(string) $name] = $value;
		return $this;
	}

	/**
	 * Prepare
	 */
	public function prepare() {
		if (isset($this->options['group']) && $this->options['group'] > 0) {
			$DBRank = new Database('select');
			$DBRank->columns(array(
							'id' => 'user_rank_id',
							'iso' => 'user_rank_key',
							'group' => 'user_rank_group_id',
					));
			$DBRank->from(array(
							'ur' => 'user_rank'
					));
			$DBRank->where(array(
							'ur.user_rank_group_id = ' . $this->options['group'],
							'ur.user_rank_status = 1'
					));
			$DBRank->order(array(
							'user_rank_key ASC'
					));
			$DBRank->setCacheName('user_rank_' . $this->options['group']);
			$DBRank->execute();
			if ($DBRank->hasResult()) {
				$data = array();
				while ($DBRank->valid()) {
					$data = $DBRank->current();
					$this->_rank_data[$data['id']] = $data;
					$DBRank->next();
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get Rank
	 */
	public function getRank($id = null) {
		if (is_array($this->_rank_data) && count($this->_rank_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_rank_data) ? $this->_rank_data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Rank ISO
	 */
	public function getRankISO($id = null) {
		if (is_array($this->_rank_data) && count($this->_rank_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_rank_data) ? (array_key_exists('iso', $this->_rank_data[$id]) ? $this->_rank_data[$id]['iso'] : null) : null);
			}
		}
		return null;
	}

	/**
	 * Get Rank All
	 */
	public function getRankAll() {
		if (is_array($this->_rank_data) && count($this->_rank_data) > 0) {
			return $this->_rank_data;
		}
		return false;
	}

	/**
	 * RankTo Form
	 */
	public function rankToForm() {
		$data = array();
		$data_raw = $this->getRankAll();
		if (is_array($data_raw) && count($data_raw) > 0) {
			foreach ($data_raw as $rank) {
				$data[$rank['id']] = 'text_rank_' . $this->options['group'] . '_' . strtolower(str_replace(' ', '_', $rank['iso']));
			}
		}
		return $data;
	}
}

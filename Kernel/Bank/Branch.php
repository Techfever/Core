<?php
namespace Kernel\Bank;

use Kernel\Database\Database;
use Kernel\Exception;

class Branch {

	/**
	 * @var State Data
	 **/
	private $_branch_data = array();

	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array(
			'country' => 0,
			'state' => 0,
			'bank' => 0
	);

	/**
	 * Constructor
	 */
	public function __construct($options = array()) {
		if (!is_array($options)) {
			$options = func_get_args();
			$temp['country'] = array_shift($options);
			if (!empty($options)) {
				$temp['state'] = array_shift($options);
			}
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
		if ((isset($this->options['bank']) && $this->options['bank'] > 0) && (isset($this->options['country']) && $this->options['country'] > 0) && isset($this->options['state']) && $this->options['state'] > 0) {
			$DBBranch = new Database('select');
			$DBBranch->columns(array(
							'id' => 'bank_branch_id',
							'key' => 'bank_branch_key',
							'hq' => 'bank_branch_hq',
							'city' => 'bank_branch_city'
					));
			$DBBranch->from(array(
							'bb' => 'bank_branch'
					));
			$DBBranch->where(array(
							'bb.bank_branch_country_id = ' . $this->options['country'],
							'bb.bank_branch_state_id = ' . $this->options['state'],
							'bb.bank_id = ' . $this->options['bank'],
							'bb.bank_branch_status = 1',
					));
			$DBBranch->order(array(
							'bank_branch_key ASC'
					));
			$DBBranch->setCacheName('bank_branch_' . $this->options['bank'] . '_' . $this->options['country'] . '_' . $this->options['state']);
			$DBBranch->execute();
			if ($DBBranch->hasResult()) {
				$data = array();
				while ($DBBranch->valid()) {
					$data = $DBBranch->current();
					$this->_branch_data[$data['id']] = $data;
					$DBBranch->next();
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get Branch
	 */
	public function getBranch($id = null) {
		if (is_array($this->_branch_data) && count($this->_branch_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_branch_data) ? $this->_branch_data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Branch ISO
	 */
	public function getBranchISO($id = null) {
		if (is_array($this->_branch_data) && count($this->_branch_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_branch_data) ? (array_key_exists('iso', $this->_branch_data[$id]) ? $this->_branch_data[$id]['key'] : null) : null);
			}
		}
		return null;
	}

	/**
	 * Get Branch All
	 */
	public function getBranchAll() {
		if (is_array($this->_branch_data) && count($this->_branch_data) > 0) {
			return $this->_branch_data;
		}
		return false;
	}

	/**
	 * BranchTo Form
	 */
	public function branchToForm() {
		$data = array();
		$data_raw = $this->getBranchAll();
		if (is_array($data_raw) && count($data_raw) > 0) {
			foreach ($data_raw as $branch) {
				$data[$branch['id']] = 'text_bank_branch_' . $this->options['bank'] . '_' . strtolower(str_replace(' ', '_', $branch['key']));
			}
		}
		return $data;
	}
}

<?php
namespace Kernel\Bank;

use Kernel\Database\Database;
use Kernel\Exception;

class State extends Branch {

	/**
	 * @var State Data
	 **/
	private $_state_data = array();

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
			if (!empty($options)) {
				$temp['bank'] = array_shift($options);
			}
			$options = $temp;
		} else {
			$options = array_merge($this->options, $options);
		}
		$this->options = $options;
		parent::__construct($options);
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
		if (isset($this->options['country']) && $this->options['country'] > 0) {
			$DBState = new Database('select');
			$DBState->columns(array(
							'id' => 'country_state_id',
							'iso' => 'country_state_iso_code',
							'name' => 'country_state_name',
							'address' => 'country_state_address',
							'state' => 'country_state_bank'
					));
			$DBState->from(array(
							'cs' => 'country_state'
					));
			$DBState->where(array(
							'cs.country_id = ' . $this->options['country'],
							'cs.country_state_bank = 1'
					));
			$DBState->order(array(
							'country_state_name ASC'
					));
			$DBState->setCacheName('country_bank_' . $this->options['country']);
			$DBState->execute();
			if ($DBState->hasResult()) {
				$data = array();
				while ($DBState->valid()) {
					$data = $DBState->current();
					$this->_state_data[$data['id']] = $data;
					$DBState->next();
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get State
	 */
	public function getState($id = null) {
		if (is_array($this->_state_data) && count($this->_state_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_state_data) ? $this->_state_data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get State ISO
	 */
	public function getStateISO($id = null) {
		if (is_array($this->_state_data) && count($this->_state_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_state_data) ? (array_key_exists('iso', $this->_state_data[$id]) ? $this->_state_data[$id]['iso'] : null) : null);
			}
		}
		return null;
	}

	/**
	 * Get State All
	 */
	public function getStateAll() {
		if (is_array($this->_state_data) && count($this->_state_data) > 0) {
			return $this->_state_data;
		}
		return false;
	}

	/**
	 * StateTo Form
	 */
	public function stateToForm() {
		$data = array();
		$data_raw = $this->getStateAll();
		if (is_array($data_raw) && count($data_raw) > 0) {
			foreach ($data_raw as $state) {
				$data[$state['id']] = 'text_country_state_' . $this->options['country'] . '_' . strtolower(str_replace(' ', '_', $state['iso']));
			}
		}
		return $data;
	}
}

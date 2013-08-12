<?php
namespace Techfever\Bank;

use Techfever\Exception;

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
			'bank' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0,
	);

	/**
	 * Constructor
	 */
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
	public function getStateData() {
		if (!is_array($this->_state_data) || count($this->_state_data) < 1) {
			if ($this->getOption('country') > 0) {
				$DBState = $this->getDatabase();
				$DBState->select();
				$DBState->columns(array(
								'id' => 'country_state_id',
								'iso' => 'country_state_iso_code',
								'name' => 'country_state_name',
								'address' => 'country_state_address',
								'state' => 'country_state_bank',
								'country' => 'country_id'
						));
				$DBState->from(array(
								'cs' => 'country_state'
						));
				$DBState->where(array(
								'cs.country_id = ' . $this->getOption('country'),
								'country_state_address = 1'
						));
				$DBState->order(array(
								'country_state_name ASC'
						));
				$DBState->setCacheName('country_address_state_' . $this->getOption('country'));
				$DBState->execute();
				if ($DBState->hasResult()) {
					$data = array();
					while ($DBState->valid()) {
						$data = $DBState->current();
						$this->_state_data[$data['id']] = $data;
						$DBState->next();
					}
				}
			}
		}
		return $this->_state_data;
	}

	/**
	 * Get State
	 */

	public function getState($id = null) {
		$data = $this->getStateData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get State ISO
	 */
	public function getStateISO($id = null) {
		$data = $this->getState($id);
		$iso = "";
		if (strlen($data['iso']) > 0) {
			$iso = $data['iso'];
		}
		return $iso;
	}

	/**
	 * Get State Name
	 */
	public function getStateName($id = null) {
		$data = $this->getState($id);
		$country = $this->getOption('country');
		$iso = $data['iso'];
		$name = "";
		if (strlen($iso) > 0) {
			$name = $this->getTranslate('text_country_state_' . $country . '_' . strtolower(str_replace(' ', '_', $iso)));
		}
		return $name;
	}

	/**
	 * Get State All
	 */
	public function getStateAll() {
		return $this->getStateData();
	}

	/**
	 * StateTo Form
	 */
	public function stateToForm() {
		$data = $this->getStateData();
		$stateData = array();
		$country = $this->getOption('country');
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $state) {
				$stateData[$state['id']] = $this->getStateName($state['id']);
			}
		}
		return $stateData;
	}
}

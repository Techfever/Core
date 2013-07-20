<?php
namespace Kernel;

use Kernel\Database;

class Country {

	/**
	 * @var Country Data
	 **/
	private $_country_data = array();

	/**
	 * @var Address Data
	 **/
	private $_address_data = array();

	/**
	 * @var Bank Data
	 **/
	private $_bank_data = array();

	/**
	 * @var Nationality Data
	 **/
	private $_nationality_data = array();

	/**
	 * @var State Data
	 **/
	private $_state_data = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->prepare();
	}

	/**
	 * Prepare
	 */
	public function prepare() {
		if (count($this->_country_data) < 1) {
			$DBCountry = new Database('select');
			$DBCountry
					->columns(
							array(
									'id' => 'country_id',
									'name' => 'country_name',
									'iso_2' => 'country_iso_code_2',
									'iso_3' => 'country_iso_code_3',
									'iso_custom' => 'country_iso_code_custom',
									'address_format' => 'country_address_format',
									'address' => 'country_address',
									'nationality' => 'country_nationality',
									'bank' => 'country_bank'
							));
			$DBCountry->from(array(
							'c' => 'country'
					));
			$DBCountry->order(array(
							'country_name ASC'
					));
			$DBCountry->setCacheName('country');
			$DBCountry->execute();
			if ($DBCountry->hasResult()) {
				$data = array();
				while ($DBCountry->valid()) {
					$data = $DBCountry->current();
					$this->_country_data[$data['id']] = $data;
					if ($data['address'] == 1) {
						$this->_address_data[$data['id']] = $data;
					}
					if ($data['bank'] == 1) {
						$this->_bank_data[$data['id']] = $data;
					}
					if ($data['nationality'] == 1) {
						$this->_nationality_data[$data['id']] = $data;
					}
					$DBCountry->next();
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get
	 */
	public function get($id = null) {
		if (is_array($this->_country_data) && count($this->_country_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_country_data) ? $this->_country_data[$id] : null);
			} else {
				return $this->_country_data;
			}
		}
		return false;
	}

	/**
	 * Form
	 */
	public function toForm() {
		$data = array();
		$data_raw = $this->get();
		if (count($data_raw) > 0) {
			foreach ($data_raw as $country) {
				$data[$country['id']] = 'text_country_' . strtolower(str_replace(' ', '_', $country['iso_3']));
			}
		}
		return $data;
	}

	/**
	 * Address
	 */
	public function getAddress($id = null) {
		if (is_array($this->_address_data) && count($this->_address_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_address_data) ? $this->_address_data[$id] : null);
			} else {
				return $this->_address_data;
			}
		}
		return false;
	}

	/**
	 * Address Form
	 */
	public function addressToForm() {
		$data = array();
		$data_raw = $this->getAddress();
		if (count($data_raw) > 0) {
			foreach ($data_raw as $address) {
				$data[$address['id']] = 'text_country_' . strtolower(str_replace(' ', '_', $address['iso_3']));
			}
		}
		return $data;
	}

	/**
	 * Nationality
	 */
	public function getNationality($id = null) {
		if (is_array($this->_nationality_data) && count($this->_nationality_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_nationality_data) ? $this->_nationality_data[$id] : null);
			} else {
				return $this->_nationality_data;
			}
		}
		return false;
	}

	/**
	 * Nationality Form
	 */
	public function nationalityToForm() {
		$data = array();
		$data_raw = $this->getNationality();
		if (count($data_raw) > 0) {
			foreach ($data_raw as $nationality) {
				$data[$nationality['id']] = 'text_country_' . strtolower(str_replace(' ', '_', $nationality['iso_3']));
			}
		}
		return $data;
	}

	/**
	 * Bank
	 */
	public function getBank($id = null) {
		if (is_array($this->_bank_data) && count($this->_bank_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_bank_data) ? $this->_bank_data[$id] : null);
			} else {
				return $this->_bank_data;
			}
		}
		return false;
	}

	/**
	 * Bank Form
	 */
	public function bankToForm() {
		$data = array();
		$data_raw = $this->getBank();
		if (count($data_raw) > 0) {
			foreach ($data_raw as $bank) {
				$data[$bank['id']] = 'text_country_' . strtolower(str_replace(' ', '_', $bank['iso_3']));
			}
		}
		return $data;
	}

	/**
	 * Get State
	 */
	public function getState($id = null, $country_id, $type = 'address') {
		if (!empty($country_id) && ($type == 'address' || $type == 'bank')) {
			if (count($this->_state_data) < 1) {
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
				$where = array(
						'cs.country_id = ' . $country_id
				);
				if ($type == 'address') {
					$where['cs.country_state_address'] = 1;
				} elseif ($type == 'bank') {
					$where['cs.country_state_bank'] = 1;
				}
				$DBState->where($where);
				$DBState->order(array(
								'country_state_name ASC'
						));
				$DBState->setCacheName('country_state_' . $type . '_' . $country_id);
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
			if (is_array($this->_state_data) && count($this->_state_data) > 0) {
				if (!empty($id)) {
					return (array_key_exists($id, $this->_state_data) ? $this->_state_data[$id] : null);
				} else {
					return $this->_state_data;
				}
			}
		}
		return false;
	}

	/**
	 * State Form
	 */
	public function stateToForm($country_id, $type = 'address') {
		$data = array();
		$data_raw = $this->getState(null, $country_id, $type);
		if (count($data_raw) > 0) {
			foreach ($data_raw as $state) {
				$data[$state['id']] = 'text_country_state_' . $country_id . '_' . strtolower(str_replace(' ', '_', $state['iso']));
			}
		}
		return $data;
	}
}

<?php
namespace Kernel\Bank;

use Kernel\Database\Database;

class Bank extends Country {

	/**
	 * @var Country Data
	 **/
	private $_bank_data = array();

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
	 * Prepare
	 */
	public function prepare() {
		$DBBank = new Database('select');
		$DBBank->columns(array(
						'id' => 'bank_id',
						'name' => 'bank_name',
						'iso' => 'bank_iso'
				));
		$DBBank->from(array(
						'b' => 'bank'
				));
		$DBBank->where(array(
						'b.bank_status = 1'
				));
		$DBBank->order(array(
						'bank_name ASC'
				));
		$DBBank->setCacheName('bank');
		$DBBank->execute();
		if ($DBBank->hasResult()) {
			$data = array();
			while ($DBBank->valid()) {
				$data = $DBBank->current();
				$this->_bank_data[$data['id']] = $data;
				$DBBank->next();
			}
			return true;
		}
		return false;
	}

	/**
	 * Get Bank
	 */
	public function getBank($id = null) {
		if (is_array($this->_bank_data) && count($this->_bank_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_bank_data) ? $this->_bank_data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Bank ISO
	 */
	public function getBankISO($id = null) {
		if (is_array($this->_bank_data) && count($this->_bank_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_bank_data) ? (array_key_exists('iso', $this->_bank_data[$id]) ? $this->_bank_data[$id]['iso'] : null) : null);
			}
		}
		return null;
	}

	/**
	 * Get Bank All
	 */
	public function getBankAll() {
		if (is_array($this->_bank_data) && count($this->_bank_data) > 0) {
			return $this->_bank_data;
		}
		return false;
	}

	/**
	 * Bank To Form
	 */
	public function bankToForm() {
		$data = array();
		$data_raw = $this->getBankAll();
		if (is_array($data_raw) && count($data_raw) > 0) {
			foreach ($data_raw as $bank) {
				$data[$bank['id']] = 'text_bank_' . strtolower(str_replace(' ', '_', $bank['iso']));
			}
		}
		return $data;
	}
}

<?php
namespace Techfever\Bank;

use Techfever\Exception;

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
			'bank' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0,
	);

	/**
	 * @var User Bank Data
	 **/
	private $_user_bank_data = array();

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
	public function getUserBankData() {
		if (!is_array($this->_user_bank_data) || count($this->_user_bank_data) < 1) {
			$QBank = $this->getDatabase();
			$QBank->select();
			$QBank->columns(array(
							'*'
					));
			$QBank->from(array(
							'ud' => 'user_bank'
					));
			$QBank->where(array(
							'ud.user_profile_id' => $this->getOption('profile_id'),
							'ud.user_bank_delete_status' => '0'
					));
			$QBank->setCacheName('user_bank_' . $this->getOption('profile_id'));
			$QBank->execute();
			if ($QBank->hasResult()) {
				$data = array();
				while ($QBank->valid()) {
					$data = $QBank->current();

					if ($data['user_bank_name'] > 0) {
						$this->setOption('bank', $data['user_bank_name']);
						$data['user_bank_name_text'] = $this->getBankName($data['user_bank_name']);
					}
					if ($data['user_bank_country'] > 0) {
						$data['user_bank_country_text'] = $this->getCountryName($data['user_bank_country']);

						if ($data['user_bank_state'] > 0) {
							$this->setOption('country', $data['user_bank_country']);
							$data['user_bank_state_text'] = $this->getStateName($data['user_bank_state']);

							if ($data['user_bank_branch'] > 0) {
								$this->setOption('state', $data['user_bank_state']);
								$this->prepareBranch();
								$data['user_bank_branch_text'] = $this->getBranchName($data['user_bank_name']);
							}
						}
					}

					$this->_user_bank_data[$data['user_bank_id']] = $data;
					$QBank->next();
				}
			}
		}
		return $this->_user_bank_data;
	}

	/**
	 * Get Country
	 */
	public function getUserBank($id = null) {
		$data = $this->getUserBankData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Prepare
	 */
	public function getBankData() {
		if (!is_array($this->_bank_data) || count($this->_bank_data) < 1) {
			$DBBank = $this->getDatabase();
			$DBBank->select();
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
			}
		}
		return $this->_bank_data;
	}

	/**
	 * Get Bank
	 */
	public function getBank($id = null) {
		$data = $this->getBankData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Bank ISO
	 */
	public function getBankISO($id = null) {
		$data = $this->getBank($id);
		$iso = "";
		if (strlen($data['iso']) > 0) {
			$iso = $data['iso'];
		}
		return $iso;
	}

	/**
	 * Get Bank Name
	 */
	public function getBankName($id = null) {
		$data = $this->getBank($id);
		$iso = $data['iso'];
		$name = "";
		if (strlen($data['iso']) > 0) {
			$name = $this->getTranslate('text_bank_' . strtolower(str_replace(' ', '_', $iso)));
		}
		return $name;
	}

	/**
	 * Get Bank All
	 */
	public function getBankAll() {
		return $this->getBankData();
	}

	/**
	 * Bank To Form
	 */
	public function bankToForm() {
		$data = $this->getBankData();
		$bankData = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $bank) {
				$bankData[$bank['id']] = $this->getBankName($bank['id']);
			}
		}
		return $bankData;
	}
}

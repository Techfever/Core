<?php
namespace Techfever\Bank;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Branch extends GeneralBase {

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
	public function getBranchData() {
		if (!is_array($this->_branch_data) || count($this->_branch_data) < 1) {
			if ($this->getOption('bank') > 0 && $this->getOption('country') > 0 && $this->getOption('state') > 0) {
				$DBBranch = $this->getDatabase();
				$DBBranch->select();
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
								'bb.bank_branch_country_id = ' . $this->getOption('country'),
								'bb.bank_branch_state_id = ' . $this->getOption('state'),
								'bb.bank_id = ' . $this->getOption('bank'),
								'bb.bank_branch_status = 1',
						));
				$DBBranch->order(array(
								'bank_branch_key ASC'
						));
				$DBBranch->setCacheName('country_bank_branch_' . $this->getOption('bank') . '_' . $this->getOption('country') . '_' . $this->getOption('state'));
				$DBBranch->execute();
				if ($DBBranch->hasResult()) {
					$data = array();
					while ($DBBranch->valid()) {
						$data = $DBBranch->current();
						$this->_branch_data[$data['id']] = $data;
						$DBBranch->next();
					}
				}
			}
		}
		return $this->_branch_data;
	}

	/**
	 * Get Branch
	 */
	public function getBranch($id = null) {
		$data = $this->getBranchData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Branch ISO
	 */
	public function getBranchISO($id = null) {
		$data = $this->getBranch($id);
		$iso = "";
		if (strlen($data['iso']) > 0) {
			$iso = $data['iso'];
		}
		return $iso;
	}

	/**
	 * Get State Name
	 */
	public function getBranchName($id = null) {
		$data = $this->getBranch($id);
		$bank = $this->getOption('bank');
		$key = $data['key'];
		$name = "";
		if (strlen($key) > 0) {
			$name = $this->getTranslate('text_bank_branch_' . $bank . '_' . strtolower(str_replace(' ', '_', $key)));
		}
		return $name;
	}

	/**
	 * Get Branch All
	 */
	public function getBranchAll() {
		return $this->getBranchData();
	}

	/**
	 * BranchTo Form
	 */
	public function branchToForm() {
		$data = $this->getBranchData();
		$branchData = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $branch) {
				$branchData[$branch['id']] = $this->getBranchName($branch['id']);
			}
		}
		return $branchData;
	}
}

<?php
namespace Techfever\Address;

use Techfever\Exception;

class Address extends Country {

	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array(
			'country' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0,
	);

	/**
	 * @var User Address Data
	 **/
	private $_user_address_data = array();

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
	public function getUserAddressData() {
		if (!is_array($this->_user_address_data) || count($this->_user_address_data) < 1) {
			if ($this->getOption('profile_id') > 0) {
				$QAddress = $this->getDatabase();
				$QAddress->select();
				$QAddress->columns(array(
								'*'
						));
				$QAddress->from(array(
								'ud' => 'user_address'
						));
				$QAddress->where(array(
								'ud.user_profile_id' => $this->getOption('profile_id'),
								'ud.user_address_delete_status' => '0'
						));
				$QAddress->setCacheName('user_address_' . $this->getOption('profile_id'));
				$QAddress->execute();
				if ($QAddress->hasResult()) {
					$data = array();
					while ($QAddress->valid()) {
						$data = $QAddress->current();

						if ($data['user_address_country'] > 0) {
							$data['user_address_country_text'] = $this->getCountryName($data['user_address_country']);

							if ($data['user_address_state'] > 0) {
								$this->setOption('country', $data['user_address_country']);
								$data['user_address_state_text'] = $this->getStateName($data['user_address_state']);
							}
						}

						$this->_user_address_data[$data['user_address_id']] = $data;
						$QAddress->next();
					}
				}
			}
		}
		return $this->_user_address_data;
	}

	/**
	 * Get Country
	 */
	public function getUserAddress($id = null) {
		$data = $this->getUserAddressData();
		if (is_array($data) && count($data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $data) ? $data[$id] : null);
			}
		}
		return false;
	}
}

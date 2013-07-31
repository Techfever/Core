<?php
namespace Kernel\Nationality;

use Kernel\Database\Database;
use Kernel\Exception;

class Country {

	/**
	 * @var Country Data
	 **/
	private $_country_data = array();

	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array(
			'country' => 0,
	);

	/**
	 * Constructor
	 */
	public function __construct($options = array()) {
		if (!is_array($options)) {
			$options = func_get_args();
			$temp['country'] = array_shift($options);

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
		$DBCountry->where(array(
						'c.country_nationality = 1'
				));
		$DBCountry->order(array(
						'country_name ASC'
				));
		$DBCountry->setCacheName('country_nationality');
		$DBCountry->execute();
		if ($DBCountry->hasResult()) {
			$data = array();
			while ($DBCountry->valid()) {
				$data = $DBCountry->current();
				$this->_country_data[$data['id']] = $data;
				$DBCountry->next();
			}
			return true;
		}
		return false;
	}

	/**
	 * Get Country
	 */
	public function getCountry($id = null) {
		if (is_array($this->_country_data) && count($this->_country_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_country_data) ? $this->_country_data[$id] : null);
			}
		}
		return false;
	}

	/**
	 * Get Country ISO
	 */
	public function getCountryISO($id = null) {
		if (is_array($this->_country_data) && count($this->_country_data) > 0) {
			if (!empty($id)) {
				return (array_key_exists($id, $this->_country_data) ? (array_key_exists('iso_3', $this->_country_data[$id]) ? $this->_country_data[$id]['iso_3'] : null) : null);
			}
		}
		return null;
	}

	/**
	 * Get Country All
	 */
	public function getCountryAll() {
		if (is_array($this->_country_data) && count($this->_country_data) > 0) {
			return $this->_country_data;
		}
		return false;
	}

	/**
	 * Country To Form
	 */
	public function countryToForm() {
		$data = array();
		$data_raw = $this->getCountryAll();
		if (is_array($data_raw) && count($data_raw) > 0) {
			foreach ($data_raw as $country) {
				$data[$country['id']] = 'text_country_' . strtolower(str_replace(' ', '_', $country['iso_3']));
			}
		}
		return $data;
	}
}

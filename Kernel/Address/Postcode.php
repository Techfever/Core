<?php
namespace Kernel\Address;

use Kernel\Database\Database;
use Kernel\Exception;

class Postcode {

	/**
	 * @var State Data
	 **/
	private $_postcode_data = array();

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
		if (isset($this->options['country']) && $this->options['country'] > 0) {
			$DBPostcode = new Database('select');
			$DBPostcode->columns(array(
							'id' => 'country_postcode_id',
							'regex' => 'country_postcode_regex'
					));
			$DBPostcode->from(array(
							'cp' => 'country_postcode'
					));
			$DBPostcode->where(array(
							'cp.country_id = ' . $this->options['country']
					));
			$DBPostcode->setCacheName('country_' . $this->options['country']);
			$DBPostcode->execute();
			if ($DBPostcode->hasResult()) {
				$data = $DBPostcode->current();
				$this->_postcode_data = $data;
				return true;
			}
		}
		return false;
	}

	/**
	 * Get Postcode
	 */
	public function getPostcode() {
		if (is_array($this->_postcode_data) && count($this->_postcode_data) > 0) {
			return $this->_postcode_data;
		}
		return false;
	}

	/**
	 * Get Postcode Regex
	 */
	public function postcodeRegex() {
		$data = null;
		$data_raw = $this->getPostcode();
		if (is_array($data_raw) && count($data_raw) > 0) {
			$data = $data_raw['regex'];
		}
		return $data;
	}
}

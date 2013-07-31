<?php
namespace Kernel\Address;

use Kernel\Database\Database;

class Address extends Country {

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
		parent::__construct($options);
	}
}

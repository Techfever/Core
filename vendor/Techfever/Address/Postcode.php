<?php

namespace Techfever\Address;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Postcode extends GeneralBase {
	
	/**
	 *
	 * @var State Data
	 *     
	 */
	private $_postcode_data = array ();
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array (
			'country' => 0,
			'profile_id' => 0,
			'address_id' => 0,
			'country_id' => 0,
			'state_id' => 0 
	);
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Prepare
	 */
	public function getPostcodeData() {
		if (! is_array ( $this->_postcode_data ) || count ( $this->_postcode_data ) < 1) {
			if ($this->getOption ( 'country' ) > 0) {
				$DBPostcode = $this->getDatabase ();
				$DBPostcode->select ();
				$DBPostcode->columns ( array (
						'id' => 'country_postcode_id',
						'regex' => 'country_postcode_regex' 
				) );
				$DBPostcode->from ( array (
						'cp' => 'country_postcode' 
				) );
				$DBPostcode->where ( array (
						'cp.country_id = ' . $this->getOption ( 'country' ) 
				) );
				$DBPostcode->setCacheName ( 'country_postcode_' . $this->getOption ( 'country' ) );
				$DBPostcode->execute ();
				if ($DBPostcode->hasResult ()) {
					$data = $DBPostcode->current ();
					$this->_postcode_data = $data;
				}
			}
		}
		return $this->_postcode_data;
	}
	
	/**
	 * Get Postcode
	 */
	public function getPostcode() {
		return $this->getPostcodeData ();
	}
	
	/**
	 * Get Postcode Regex
	 */
	public function postcodeRegex() {
		$data = $this->getPostcode ();
		$postcodeData = $this->getPostcode ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			$postcodeData = $data ['regex'];
		}
		return $postcodeData;
	}
}

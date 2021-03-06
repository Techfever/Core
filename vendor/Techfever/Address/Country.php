<?php

namespace Techfever\Address;

use Techfever\Exception;

class Country extends State {
	
	/**
	 *
	 * @var Country Data
	 *     
	 */
	private $_country_data = array ();
	
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
	public function getCountryData() {
		if (! is_array ( $this->_country_data ) || count ( $this->_country_data ) < 1) {
			$DBCountry = $this->getDatabase ();
			$DBCountry->select ();
			$DBCountry->columns ( array (
					'id' => 'country_id',
					'name' => 'country_name',
					'iso_2' => 'country_iso_code_2',
					'iso_3' => 'country_iso_code_3',
					'iso_custom' => 'country_iso_code_custom',
					'address_format' => 'country_address_format',
					'address' => 'country_address',
					'nationality' => 'country_nationality',
					'bank' => 'country_bank' 
			) );
			$DBCountry->from ( array (
					'c' => 'country' 
			) );
			$DBCountry->where ( array (
					'c.country_address = 1' 
			) );
			$DBCountry->order ( array (
					'country_name ASC' 
			) );
			$DBCountry->execute ();
			if ($DBCountry->hasResult ()) {
				$data = array ();
				while ( $DBCountry->valid () ) {
					$data = $DBCountry->current ();
					$this->_country_data [$data ['id']] = $data;
					$DBCountry->next ();
				}
			}
		}
		return $this->_country_data;
	}
	
	/**
	 * Get Country
	 */
	public function getCountry($id = null) {
		$data = $this->getCountryData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
	
	/**
	 * Get Country ISO
	 */
	public function getCountryISO($id = null) {
		$data = $this->getCountry ( $id );
		$iso = "";
		if (strlen ( $data ['iso_3'] ) > 0) {
			$iso = $data ['iso_3'];
		}
		return $iso;
	}
	
	/**
	 * Get Country Name
	 */
	public function getCountryName($id = null) {
		$data = $this->getCountry ( $id );
		$iso = $data ['iso_3'];
		$name = "";
		if (strlen ( $iso ) > 0) {
			$name = $this->getTranslate ( 'text_country_' . strtolower ( $this->convertToUnderscore ( $iso, ' ' ) ) );
		}
		return $name;
	}
	
	/**
	 * Get Country All
	 */
	public function getCountryAll() {
		return $this->getCountryData ();
	}
	
	/**
	 * Get Country Id
	 */
	public function getCountryID($val = null) {
		$data = $this->getCountryData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $country ) {
				$countryName = $this->getCountryName ( $country ['id'] );
				if (strtolower ( $val ) === strtolower ( $countryName )) {
					return $country ['id'];
				}
			}
		}
		return 0;
	}
	
	/**
	 * Get Country By Expr
	 */
	public function getCountryByExpr($expr = null) {
		$data = $this->getCountryData ();
		$countryData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $country ) {
				$countryName = $this->getCountryName ( $country ['id'] );
				if (empty ( $expr )) {
					$countryData [$countryName] = $countryName;
				} elseif (strpos ( strtolower ( $countryName ), strtolower ( $expr ) ) !== false) {
					$countryData [$countryName] = $countryName;
				}
			}
		}
		return $countryData;
	}
	
	/**
	 * Country To Form
	 */
	public function countryToForm() {
		$data = $this->getCountryData ();
		$countryData = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $country ) {
				$countryData [$country ['id']] = $this->getCountryName ( $country ['id'] );
			}
		}
		return $countryData;
	}
}

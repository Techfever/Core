<?php

namespace Techfever\Nationality;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Country extends GeneralBase {
	
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
	protected $options = array ();
	
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
					'c.country_nationality = 1' 
			) );
			$DBCountry->order ( array (
					'country_name ASC' 
			) );
			$DBCountry->setCacheName ( 'country_nationality' );
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

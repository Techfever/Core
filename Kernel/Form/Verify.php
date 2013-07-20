<?php
namespace Kernel\Form;

// Add these import statements
use Zend\Stdlib\DateTime;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Filter\FilterChain;
use Zend\Validator\ValidatorChain;
use Kernel\Database;
use Kernel\Exception;
use Kernel\ServiceLocator;

class Verify implements InputFilterAwareInterface {

	/**
	 * @var Data
	 **/
	private $_data = null;

	/**
	 * @var Input Filter
	 **/
	private $_inputFilter;

	/**
	 * @var Field
	 **/
	private $_field = null;

	/**
	 * @var Created By
	 **/
	public $_created_by;

	/**
	 * @var Created Date
	 **/
	public $_created_date;

	/**
	 * @var Modified By
	 **/
	public $_modified_by;

	/**
	 * @var Modified Date
	 **/
	public $_modified_date;

	/**
	 * Constructor
	 *
	 * @return	void
	 **/
	public function __construct($options) {
		$this->_field = $options['field'];
	}

	private function getField() {
		return $this->_field;
	}

	public function exchangeArray($data) {
		if (isset($data['subaction'])) {
			$datetime = new DateTime();
			if ($data['subaction'] == 'new') {
				$this->created_by = null;
				$this->created_date = $datetime->format('H:i:s d-m-Y');
			}
			$this->modified_by = null;
			$this->modified_date = $datetime->format('H:i:s d-m-Y');
			if (!empty($this->_field) && count($this->_field) > 0) {
				foreach ($this->_field as $field_key => $field_value) {
					if (isset($field_value['table']) && isset($field_value['table']['name']) && isset($field_value['table']['column'])) {
						if (!is_array($this->_data) || !array_key_exists($field_value['table']['name'], $this->_data)) {
							$this->_data[$field_value['table']['name']] = array();
						}
						$this->_data[$field_value['table']['name']][$field_value['table']['column']] = $data[$field_key];
					}
				}
			}
		}
	}
	public function getArrayCopy() {
		return get_object_vars($this);
	}

	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new Exception('Not used');
	}

	public function getInputFilter() {
		if (!$this->_inputFilter) {

			$validatorManager = ServiceLocator::getServiceManager('ValidatorManager');
			$filterManager = ServiceLocator::getServiceManager('FilterManager');
			
			$validatorChain = new ValidatorChain();
			$validatorChain->setPluginManager($validatorManager);

			$filterChain = new FilterChain();
			$filterChain->setPluginManager($filterManager);

			$factory = new InputFactory();
			$factory->setDefaultValidatorChain($validatorChain);
			$factory->setDefaultFilterChain($filterChain);

			$inputFilter = new InputFilter();

			$Language = ServiceLocator::getServiceManager('Language');
			if (!empty($this->_field) && count($this->_field) > 0) {
				foreach ($this->_field as $field_key => $field_value) {
					$required = false;
					if (isset($field_value['attributes']) && isset($field_value['attributes']['require']) && $field_value['attributes']['require']) {
						$required = true;
					}
					$filter = array(
							array(
									'name' => 'StringTrim'
							)
					);
					if (isset($field_value['filters'])) {
						$filter = array();
						foreach ($field_value['filters'] as $filter_value) {
							$filter[] = array(
									'name' => $filter_value
							);
						}
						;
					}
					$validator_data = null;
					if ($required) {
						$validator_data[] = array(
								'name' => 'NotEmpty',
								'break_chain_on_failure' => true,
						);
					}
					if (isset($field_value['validators'])) {
						foreach ($field_value['validators'] as $validator_key => $validator_value) {
							$validator_data[] = $validator_value;
						}
					}
					$validator = array();
					if (count($validator_data) > 0) {
						foreach ($validator_data as $validator_key => $validator_value) {
							if (!isset($validator_value['break_chain_on_failure'])) {
								$validator_value['break_chain_on_failure'] = true;
							}
							if (!isset($validator_value['options']) || !isset($validator_value['options']['messages'])) {
								//$validator_value['options']['messages'] = $Language->validatorMessages($validator_value['name'], $field_key);
							}
							$validator[] = $validator_value;
						}
					}
					$options = array(
							'name' => $field_key,
							'required' => $required,
							'filters' => $filter,
							'validators' => $validator
					);
					$inputFilter->add($factory->createInput($options));
				}
			}
			$this->_inputFilter = $inputFilter;
		}

		return $this->_inputFilter;
	}
}

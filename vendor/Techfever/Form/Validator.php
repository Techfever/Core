<?php
namespace Techfever\Form;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Validator extends GeneralBase {
	/**
	 * @var Options
	 */
	protected $options = array();

	/**
	 * @var Variables
	 */
	protected $variables = array();

	/**
	 * @var Validator Data
	 **/
	private $validator_data = null;

	/**
	 * @var Validator Option Data
	 **/
	private $validator_option_data = null;

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		$this->setOptions($options);
		parent::__construct($options);
		unset($this->options['servicelocator']);
	}

	/**
	 * Get Element ID
	 * 
	 * @return array id
	 **/
	public function getElementID() {
		return $this->getOption('element_id');
	}

	/**
	 * Get Validator Data
	 * 
	 * @return array data
	 **/
	public function getValidatorsData() {
		if (!is_array($this->validator_data) || count($this->validator_data) < 1) {
			$element = $this->getElementID();
			if (is_array($element) && count($element) > 0) {
				$config = array();
				$QValidator = $this->getDatabase();
				$QValidator->select();
				$QValidator->columns(array(
								'id' => 'form_element_validators_id',
								'element' => 'form_element_id',
								'key' => 'form_element_validators_key',
						));
				$QValidator->from(array(
								'fe' => 'form_element_validators'
						));
				$QValidator->where(array(
								'fe.form_element_id in (' . implode(', ', $element) . ')'
						));
				$QValidator->order(array(
								'fe.form_element_validators_key ASC'
						));
				$QValidator->setCacheName('form_element_validators');
				$QValidator->execute();
				if ($QValidator->hasResult()) {
					while ($QValidator->valid()) {
						$rawdata = $QValidator->current();
						$config[$rawdata['id']] = $rawdata;
						$QValidator->next();
					}
				}
				$this->validator_data = $config;
			}
		}
		return $this->validator_data;
	}

	/**
	 * Get Validator Option Data
	 * 
	 * @return array data
	 **/
	public function getValidatorsOptionsData() {
		if (!is_array($this->validator_option_data) || count($this->validator_option_data) < 1) {
			$validator = $this->getValidatorsData();
			if (is_array($validator) && count($validator) > 0) {
				$validator_id = array();
				foreach ($validator as $validator_value) {
					$validator_id[] = $validator_value['id'];
				}
				$config = array();
				$QValidator = $this->getDatabase();
				$QValidator->select();
				$QValidator->columns(array(
								'id' => 'form_element_validators_options_id',
								'validator' => 'form_element_validators_id',
								'key' => 'form_element_validators_options_key',
								'value' => 'form_element_validators_options_value',
						));
				$QValidator->from(array(
								'fe' => 'form_element_validators_options'
						));
				$QValidator->where(array(
								'fe.form_element_validators_id in (' . implode(', ', $validator_id) . ')'
						));
				$QValidator->order(array(
								'fe.form_element_validators_options_key ASC'
						));
				$QValidator->setCacheName('form_element_validators_options');
				$QValidator->execute();
				if ($QValidator->hasResult()) {
					while ($QValidator->valid()) {
						$rawdata = $QValidator->current();
						if (preg_match('/val\{(.*)\}$/', $rawdata['value'])) {
							$variable = $rawdata['value'];
							$variable = str_replace('val{', '', $variable);
							$variable = str_replace('}', '', $variable);
							$rawdata['value'] = $this->getVariable($variable);
						}
						$config[$rawdata['validator']][$rawdata['key']] = $rawdata['value'];
						$QValidator->next();
					}
				}
				$this->validator_option_data = $config;
			}
		}
		return $this->validator_option_data;
	}

	/**
	 * Get Validator Option
	 * 
	 * @return array data
	 **/
	public function getValidatorsOptions($id = null) {
		$option_data = $this->getValidatorsOptionsData();
		if (!empty($id) && array_key_exists($id, $option_data)) {
			return $option_data[$id];
		}
		return array();
	}

	/**
	 * Get Validators by ID
	 * 
	 * @return array
	 **/
	public function getValidatorsByID($id) {
		$data = $this->getValidatorsData();
		$validators = array();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $value) {
				if ($id == $value['element']) {
					$options = $this->getValidatorsOptions($value['id']);
					$options['servicelocator'] = $this->getServiceLocator();
					$validators[] = array(
							'name' => $value['key'],
							'break_chain_on_failure' => True,
							'options' => $options,
					);
				}
			}
		}
		return $validators;
	}
}

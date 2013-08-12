<?php
namespace Techfever\Form;

use Techfever\Exception;
use Techfever\Parameter\Parameter;

class Element extends Attribute {
	/**
	 * @var Options
	 */
	protected $options = array();

	/**
	 * @var Element Data
	 **/
	private $element_data = null;

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		$this->setOptions($options);
		$options['element_id'] = $this->getElementID();
		parent::__construct($options);
		unset($this->options['servicelocator']);
	}

	/**
	 * Get Element Data
	 * 
	 * @return array data
	 **/
	public function getElementData() {
		if (!is_array($this->element_data) || count($this->element_data) < 1) {
			$this->element_data = array();
			$cachename = str_replace('\\', '_', 'module_controllers_form_element_controller_form_element_to_controller_form_element_' . $this->getController() . '_' . $this->getRouteAction());
			$QElement = $this->getDatabase();
			$QElement->select();
			$QElement->columns(array(
							'mid' => 'module_controllers_id',
					));
			$QElement->from(array(
							'mc' => 'module_controllers'
					));
			$QElement->join(array(
							'fc' => 'form_element_controller'
					), 'fc.module_controllers_id = mc.module_controllers_id', array(
							'fid' => 'form_element_controller_id',
					));
			$QElement
					->join(array(
									'fec' => 'form_element_to_controller'
							), 'fec.form_element_controller_id = fc.form_element_controller_id',
							array(
									'id' => 'form_element_id',
									'required' => 'form_element_to_controller_required',
									'display' => 'form_element_to_controller_display',
									'step' => 'form_element_to_controller_step',
									'steptitle' => 'form_element_to_controller_title',
							));
			$QElement
					->join(array(
									'fe' => 'form_element'
							), 'fe.form_element_id = fec.form_element_id',
							array(
									'type' => 'form_element_type',
									'key' => 'form_element_key',
									'value' => 'form_element_value',
									'field_input' => 'form_element_field_input',
									'field_display' => 'form_element_field_display',
							));
			$QElement->where(array(
							'fc.module_controllers_action = "' . $this->getRouteAction() . '"',
							'mc.module_controllers_alias = "' . str_replace('\\', '\\\\', $this->getController()) . '"',
							'fec.form_element_to_controller_status = 1',
					));
			$QElement->order(array(
							'fec.form_element_to_controller_step ASC',
							'fec.form_element_to_controller_sort_order ASC',
					));
			$QElement->setCacheName($cachename);
			$QElement->execute();
			if ($QElement->hasResult()) {
				while ($QElement->valid()) {
					$rawdata = $QElement->current();
					$rawdata['key'] = strtolower($rawdata['key']);
					$type = explode('\\', $rawdata['type']);
					$type = array_slice($type, -1);
					$type = strtolower($type[0]);
					if (in_array($type, array(
							'button',
							'hidden',
							'submit',
							'seperator'
					))) {
						$rawdata['display'] = "false";
					} elseif (!in_array($type, array(
							'button',
							'hidden',
							'submit',
							'seperator'
					)) && $rawdata['display'] == "True") {
						$rawdata['required'] = "false";
						$rawdata['type'] = "Techfever\\Template\\Plugin\\Forms\\Paragraph";
					}
					$class = explode('\\', $rawdata['type']);
					$class = array_slice($class, -1);
					$rawdata['class'] = $class[0];
					$rawdata['required'] = (strtolower($rawdata['required']) == "true" ? True : False);
					$rawdata['display'] = (strtolower($rawdata['display']) == "true" ? True : False);
					$rawdata['value'] = (strtolower($rawdata['value']) == "true" ? True : $rawdata['value']);
					$this->element_data[$rawdata['key'] . ($rawdata['display'] ? '_display' : null)] = $rawdata;
					$QElement->next();
				}
			}
		}
		return $this->element_data;
	}

	/**
	 * Get Element ID
	 * 
	 * @return array id
	 **/
	public function getElementID() {
		$data = $this->getElementData();
		$id = null;
		if (is_array($data) && count($data) > 0) {
			$id = array();
			foreach ($data as $value) {
				$id[] = $value['id'];
			}
		}
		return $id;
	}

	/**
	 * Valid Element by Key
	 * 
	 * @return boolean
	 **/
	public function validElementByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key && $value['value']) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Gey Element ID by Key
	 * 
	 * @return int
	 **/
	public function getElementIDByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					return (int) $value['id'];
				}
			}
		}
		return 0;
	}

	/**
	 * Get Attributes By Key
	 * 
	 * @return array
	 **/
	public function getAttributesByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					$attributes = $this->getAttributesByID((int) $value['id']);
					return $attributes;
				}
			}
		}
		return array();
	}

	/**
	 * Get Options By Key
	 * 
	 * @return array
	 **/
	public function getOptionsByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					$options = $this->getOptionsByID((int) $value['id']);
					return $options;
				}
			}
		}
		return array();
	}

	/**
	 * Get Filters By Key
	 * 
	 * @return array
	 **/
	public function getFiltersByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					$filters = $this->getFiltersByID((int) $value['id']);
					return $filters;
				}
			}
		}
		return array();
	}

	/**
	 * Get Validators By Key
	 * 
	 * @return array
	 **/
	public function getValidatorsByKey($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					$validators = $this->getValidatorsByID((int) $value['id']);
					return $validators;
				}
			}
		}
		return array();
	}

	/**
	 * Get Element Required
	 * 
	 * @return array config
	 **/
	public function elementIsRequired($element) {
		$data = $this->getElementData();
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					return $value['required'];
				}
			}
		}
		return false;
	}

	/**
	 * Get Stucture By Key
	 * 
	 * @return array
	 **/
	public function getStuctureByKey($element, $default = null) {
		$data = $this->getElementData();

		$validatorNotEmpty = array(
				'name' => 'NotEmpty',
				'break_chain_on_failure' => true,
				'options' => array(
						'messages' => array(
								'isEmpty' => "text_error_required",
								'notEmptyInvalid' => "text_error_invalid"
						)
				)
		);

		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				if (strtolower($element) == $key) {
					$elementOrFieldset = array(
							'name' => strtolower($key),
							'type' => $value['type'],
							'required' => $this->elementIsRequired($element),
							'options' => array(
									'label' => 'text_' . strtolower(($value['display'] ? str_replace('_display', '', $key) : $key)),
							),
							'attributes' => array(
									'stitle' => $this->getTranslate($value['steptitle']),
									'is_display' => $value['display'],
									'has_value' => False,
									'step' => $value['step'],
									'is_require' => $this->elementIsRequired($element),
									'class' => strtolower($value['class']),
									'id' => strtolower($key),
							),
							'filters' => array(),
							'validators' => array(),
					);
					if ($elementOrFieldset['required']) {
						$elementOrFieldset['validators'][] = $validatorNotEmpty;
					}
					$ElementParameter = null;
					if (in_array(strtolower($value['class']), array(
							'select',
							'radio',
					))) {
						$ElementParameter = new Parameter(array(
								'key' => strtolower($key),
								'servicelocator' => $this->getServiceLocator(),
						));
						$valueParameter = $ElementParameter->toForm();
						$elementOrFieldset['options']['value_options'] = $valueParameter;
					}
					$options = array_merge($elementOrFieldset['options'], $this->getOptionsByKey($key));
					$attributes = array_merge($elementOrFieldset['attributes'], $this->getAttributesByKey($key));
					$elementOrFieldset['options'] = $options;
					$elementOrFieldset['attributes'] = $attributes;
					$valueData = $this->getOption('value');
					if ($value['display']) {
						if (is_array($valueData) && count($valueData) > 0 && array_key_exists(strtolower($value['field_display']), $valueData)) {
							$elementOrFieldset['attributes']['value'] = $valueData[strtolower($value['field_display'])];
						}
						$elementOrFieldset['attributes']['show_label'] = False;
						$elementOrFieldset['attributes']['is_hide'] = False;
						unset($elementOrFieldset['attributes']['id']);
					} else {
						if (is_array($valueData) && count($valueData) > 0 && array_key_exists(strtolower($value['field_input']), $valueData)) {
							$elementOrFieldset['attributes']['value'] = $valueData[strtolower($value['field_input'])];
							if (strlen($valueData[strtolower($value['field_input'])]) > 0) {
								$elementOrFieldset['attributes']['has_value'] = True;
							}
							if (in_array(strtolower($value['class']), array(
									'select',
									'radio',
							))) {
								if (is_numeric($valueData[strtolower($value['field_input'])])) {
									$defaultValue = $ElementParameter->getKeyByValue($valueData[strtolower($value['field_input'])]);
									if (!empty($defaultValue) && strlen($defaultValue) > 0) {
										$elementOrFieldset['attributes']['value'] = $defaultValue;
									}
								}
							}
						}
						$filters = array_merge($elementOrFieldset['filters'], $this->getFiltersByKey($key));
						$validators = array_merge($elementOrFieldset['validators'], $this->getValidatorsByKey($key));
						$elementOrFieldset['filters'] = $filters;
						$elementOrFieldset['validators'] = $validators;
					}
					if (is_array($default) && count($default) > 0) {
						foreach ($default as $default_key => $default_value) {
							if (is_array($default_value) && count($default_value) > 0) {
								if (!array_key_exists($default_key, $elementOrFieldset)) {
									$elementOrFieldset[$default_key] = array();
								}
								$elementOrFieldset[$default_key] = array_merge($elementOrFieldset[$default_key], $default_value);
							} else {
								$elementOrFieldset[$default_key] = $default_value;
							}
						}
					}
					return $elementOrFieldset;
				}
			}
		}
		return array();
	}

	/**
	 * Get Form Stucture By Key
	 * 
	 * @return array
	 **/
	public function getFormStuctureByKey($element, $default = null) {
		$structure = $this->getStuctureByKey($element, $default);
		unset($structure['filters']);
		unset($structure['validators']);
		return $structure;
	}

	/**
	 * Get Form Stucture By Key
	 * 
	 * @return array
	 **/
	public function getFilterStuctureByKey($element, $default = null) {
		$structure = $this->getStuctureByKey($element, $default);
		unset($structure['type']);
		unset($structure['options']);
		unset($structure['attributes']);
		return $structure;
	}
}

<?php
namespace Kernel\Form;

use Zend\Form\Form as BaseForm;
use Zend\Captcha;
use Zend\Form\Factory;
use Kernel\Database\Database;
use Kernel\Exception;
use Kernel\ServiceLocator;
use Kernel\Form\Parameter;

class Input extends BaseForm {

	/**
	 * @var Name
	 **/
	private $_route = null;

	/**
	 * @var Action
	 **/
	private $_action = null;

	/**
	 * @var ID
	 **/
	private $_id = null;

	/**
	 * @var Url
	 **/
	private $_url = null;

	/**
	 * @var Field
	 **/
	private $_field = null;

	/**
	 * @var Field
	 **/
	private $_method = null;

	/**
	 * @var ViewHelper
	 **/
	private $_viewhelper = null;

	/**
	 * @var FormHelper
	 **/
	private $_formhelper = null;

	/**
	 * Constructor
	 *
	 * @return	void
	 **/
	public function __construct($options) {
		parent::__construct();

		$formElementManager = ServiceLocator::getServiceManager('FormElementManager');
		$filterManager = ServiceLocator::getServiceManager('FilterManager');
		$formFactory = new Factory();
		$formFactory->setFormElementManager($formElementManager);
		$this->setFormFactory($formFactory);

		$Translator = ServiceLocator::getServiceManager('Translator');

		$this->_viewhelper = ServiceLocator::getServiceManager('ViewHelperManager');

		$this->_route = $options['route'];

		$this->_action = $options['action'];

		$strreplace = array(
				'/',
				'\\'
		);
		$this->_id = str_replace($strreplace, '_', $this->_route);

		$this->_formhelper = $this->getHelper('form');
		$serverUrl = $this->getHelper('serverUrl');
		$baseHref = $this->getHelper('baseHref');
		$url = $this->getHelper('url');
		$this->_url = $url($this->_route, $this->getAction());
		if (substr($this->_url, -2) == '//') {
			$this->_url = substr($this->_url, 0, (strlen($this->_url) - 1));
		}

		$this->_field = $options['field'];

		$this->_method = $options['method'];

		$this->setAttribute('method', $this->getMethod());
		$this->setAttribute('action', $this->getUrl());
		$this->setAttribute('id', $this->getID());

		foreach ($this->_field as $field_key => $field_value) {
			$not_show_label = true;
			$is_seperator = false;
			$is_captcha = false;
			$is_comment = false;
			$is_require = false;
			$is_button = false;
			if (is_array($field_value)) {
				$field = $field_value;
				$field['name'] = $field_key;
				$type = strtolower($field['type']);
				if (!isset($field['options'])) {
					$field['options'] = array();
				}
				if (isset($field['type'])) {
					if ($type != 'seperator' && $type != 'hidden' && $type != 'submit') {
						$not_show_label = false;
						if (!isset($field['options']['label'])) {
							$field['options']['label'] = $Translator->translate(strtolower('text_' . $field['name']));
						}
						if (!isset($field['options']['disable_inarray_validator'])) {
							$field['options']['disable_inarray_validator'] = true;
						}
						if ($type == 'captcha') {
							$comment_msg = $Translator->translate('text_error_not_match');
							$field['options']['captcha'] = new Captcha\Image(
									array(
											'wordLen' => 6,
											'font' => KERNEL_PATH . '/Font/arial.ttf',
											'width' => 140,
											'height' => 50,
											'dotNoiseLevel' => 30,
											'lineNoiseLevel' => 3,
											'ImgDir' => CORE_PATH . '/Data/Captcha',
											'ImgUrl' => $serverUrl($baseHref()) . '/Image/Captcha',
											'messages' => array(
													'badCaptcha' => str_replace("%field%", (string) $field['options']['label'], $comment_msg)
											)
									));
						}
						if (($type == 'select' || $type == 'radio' || $type == 'checkbox') && (!isset($field['options']['value_options']) || count($field['options']['value_options']) < 1)) {
							$value = null;
							$Parameter = new Parameter($field['name']);
							$Parameter->prepare($field['name']);
							$value = $Parameter->toForm();
							$field['options']['value_options'] = $value;
						}
						if (isset($field['attributes']['not_show_label']) && $field['attributes']['not_show_label'] == true) {
							$not_show_label = true;
						}
						if ($type == 'captcha') {
							$is_captcha = true;
						}

						if (isset($field['attributes']['require']) && $field['attributes']['require'] == true) {
							$is_require = true;
						}
						if (isset($field['attributes']['require_comment']) && is_array($field['attributes']['require_comment'])) {
							$is_comment = true;
						} else {
							$field['attributes']['require_comment'] = array();
						}
						if ($type == 'button') {
							$is_button = true;
							$not_show_label = true;
						}
					} elseif ($type == 'seperator') {
						$is_seperator = true;
					} elseif ($type == 'hidden' || $type == 'submit') {
						$is_button = true;
					}
					if ($type != 'seperator') {
						$field['attributes']['id'] = $field['name'];
						$field['attributes']['class'] = $type;
					}
				}
				if (!isset($field['attributes']['is_hidden'])) {
					$field['attributes']['is_hidden'] = false;
				}
				$field['attributes']['is_hidden'] = $field['attributes']['is_hidden'];
				$field['attributes']['is_seperator'] = $is_seperator;
				$field['attributes']['is_captcha'] = $is_captcha;
				$field['attributes']['is_comment'] = $is_comment;
				$field['attributes']['is_require'] = $is_require;
				$field['attributes']['is_button'] = $is_button;
				$field['attributes']['not_show_label'] = $not_show_label;
				$field['attributes']['element'] = $type;
				$this->add($field);
			}
		}
	}

	private function getID() {
		return $this->_id;
	}

	private function getUrl() {
		return $this->_url;
	}

	private function getField() {
		return $this->_field;
	}

	private function getMethod() {
		return $this->_method;
	}

	private function getAction() {
		return $this->_action;
	}

	private function getHelper($service) {
		return $this->_viewhelper->get($service);
	}

	public function openTag() {
		return $this->_formhelper->openTag($this);
	}

	public function closeTag() {
		return $this->_formhelper->closeTag($this);
	}
}

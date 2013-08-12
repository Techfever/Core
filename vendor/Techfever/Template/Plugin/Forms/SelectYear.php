<?php

namespace Techfever\Template\Plugin\Forms;

use DateTime as PhpDateTime;
use Zend\Form\Element;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\Date as DateValidator;
use Zend\Form\Element\Select;

class SelectYear extends Element implements InputProviderInterface, ElementPrepareAwareInterface {

	/**
	 * Select form element that contains values for year
	 *
	 * @var Select
	 */
	protected $yearElement;

	/**
	 * Min year to use for the select (default: current year - 100)
	 *
	 * @var int
	 */
	protected $minYear;

	/**
	 * Max year to use for the select (default: current year)
	 *
	 * @var int
	 */
	protected $maxYear;

	/**
	 * If set to true, it will generate an empty option for every select (this is mainly needed by most JavaScript
	 * libraries to allow to have a placeholder)
	 *
	 * @var bool
	 */
	protected $createEmptyOption = false;

	/**
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Constructor. Add two selects elements
	 *
	 * @param  null|int|string  $name    Optional name for the element
	 * @param  array            $options Optional options for the element
	 */
	public function __construct($name = null, $options = array()) {
		$this->minYear = date('Y') - 100;
		$this->maxYear = date('Y');

		$this->yearElement = new Select($name . '[year]', $options);
		parent::__construct($name, $options);

		if (isset($options['year_attributes'])) {
			$this->setYearAttributes($options['year_attributes']);
		}
	}

	/**
	 * Accepted options for SelectDate:
	 * - year_attributes: HTML attributes to be rendered with the year element
	 * - min_year: min year to use in the year select
	 * - max_year: max year to use in the year select
	 *
	 * @param array|\Traversable $options
	 * @return SelectYear
	 */
	public function setOptions($options) {
		parent::setOptions($options);

		if (isset($options['year_attributes'])) {
			$this->setYearAttributes($options['year_attributes']);
		}

		if (isset($options['min_year'])) {
			$this->setMinYear($options['min_year']);
		}

		if (isset($options['max_year'])) {
			$this->setMaxYear($options['max_year']);
		}

		if (isset($options['create_empty_option'])) {
			$this->setShouldCreateEmptyOption($options['create_empty_option']);
		}

		return $this;
	}

	/**
	 * @return Select
	 */
	public function getYearElement() {
		return $this->yearElement;
	}

	/**
	 * Set the year attributes
	 *
	 * @param  array $yearAttributes
	 * @return SelectYear
	 */
	public function setYearAttributes(array $yearAttributes) {
		$this->yearElement->setAttributes($yearAttributes);
		return $this;
	}

	/**
	 * Get the year attributes
	 *
	 * @return array
	 */
	public function getYearAttributes() {
		return $this->yearElement->getAttributes();
	}

	/**
	 * @param  int $minYear
	 * @return SelectYear
	 */
	public function setMinYear($minYear) {
		$this->minYear = $minYear;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMinYear() {
		return $this->minYear;
	}

	/**
	 * @param  int $maxYear
	 * @return SelectYear
	 */
	public function setMaxYear($maxYear) {
		$this->maxYear = $maxYear;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxYear() {
		return $this->maxYear;
	}

	/**
	 * @param  bool $createEmptyOption
	 * @return SelectYear
	 */
	public function setShouldCreateEmptyOption($createEmptyOption) {
		$this->createEmptyOption = (bool) $createEmptyOption;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function shouldCreateEmptyOption() {
		return $this->createEmptyOption;
	}

	/**
	 * @param mixed $value
	 * @return void|\Zend\Form\Element
	 */
	public function setValue($value) {
		if ($value instanceof PhpDateTime) {
			$value = array(
					'year' => $value->format('Y'),
			);
		}

		$this->yearElement->setValue($value['year']);
	}

	/**
	 * Prepare the form element (mostly used for rendering purposes)
	 *
	 * @param  FormInterface $form
	 * @return mixed
	 */
	public function prepareElement(FormInterface $form) {
		$name = $this->getName();
		$this->yearElement->setName($name . '[year]');
		$this->yearElement->setAttribute('id', $name . '[year]');
		$this->yearElement->setAttribute('class', 'selectyear');
		$this->yearElement->setOptions(array(
						'empty_option' => ''
				));
	}

	/**
	 * Get validator
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator() {
		if (null === $this->validator) {
			$this->validator = new DateValidator(
					array(
							'field' => $this->getName() . '[day]',
							'format' => 'Y-m-d H:i:s',
							'messages' => array(
									DateValidator::INVALID => 'text_error_invalid_value_type',
									DateValidator::INVALID_DATE => 'text_error_invalid_value_date',
									DateValidator::FALSEFORMAT => 'text_error_invalid_format'
							),
					));
		}

		return $this->validator;
	}

	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInput()}.
	 *
	 * @return array
	 */
	public function getInputSpecification() {
		return array(
				'name' => $this->getName(),
				'required' => false,
				'filters' => array(
						array(
								'name' => 'Callback',
								'options' => array(
										'callback' => function ($date) {
											// Convert the date to a specific format
											if (is_array($date)) {
												$date = $date['year'] . '-01-01 00:00:00';
											}

											return $date;
										}
								)
						)
				),
				'validators' => array(
						$this->getValidator(),
				)
		);
	}

	/**
	 * Clone the element (this is needed by Collection element, as it needs different copies of the elements)
	 */
	public function __clone() {
		$this->yearElement = clone $this->yearElement;
	}
}

<?php

namespace Techfever\Template\Plugin\Forms;

use DateTime as PhpDateTime;
use Zend\Form\FormInterface;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\Date as DateValidator;
use Zend\Form\Element\Select;

class SelectMonth extends SelectYear {
	/**
	 * Select form element that contains values for month
	 *
	 * @var Select
	 */
	protected $monthElement;

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
		$this->monthElement = new Select($name . '[month]', $options);
		parent::__construct($name, $options);

		if (isset($options['month_attributes'])) {
			$this->setMonthAttributes($options['month_attributes']);
		}
	}

	/**
	 * Accepted options for SelectDate:
	 * - month_attributes: HTML attributes to be rendered with the month element
	 * - year_attributes: HTML attributes to be rendered with the month element
	 * - min_year: min year to use in the year select
	 * - max_year: max year to use in the year select
	 *
	 * @param array|\Traversable $options
	 * @return SelectMonth
	 */
	public function setOptions($options) {
		parent::setOptions($options);

		if (isset($options['month_attributes'])) {
			$this->setMonthAttributes($options['month_attributes']);
		}

		return $this;
	}

	/**
	 * @return Select
	 */
	public function getMonthElement() {
		return $this->monthElement;
	}

	/**
	 * Set the month attributes
	 *
	 * @param  array $monthAttributes
	 * @return SelectMonth
	 */
	public function setMonthAttributes(array $monthAttributes) {
		$this->monthElement->setAttributes($monthAttributes);
		return $this;
	}

	/**
	 * Get the month attributes
	 *
	 * @return array
	 */
	public function getMonthAttributes() {
		return $this->monthElement->getAttributes();
	}

	/**
	 * @param mixed $value
	 * @return void|\Zend\Form\Element
	 */
	public function setValue($value) {
		if ($value instanceof PhpDateTime) {
			$value = array(
					'year' => $value->format('Y'),
					'month' => $value->format('m')
			);
		}

		$this->yearElement->setValue($value['year']);
		$this->monthElement->setValue($value['month']);
	}

	/**
	 * Prepare the form element (mostly used for rendering purposes)
	 *
	 * @param  FormInterface $form
	 * @return mixed
	 */
	public function prepareElement(FormInterface $form) {
		parent::prepareElement($form);

		$name = $this->getName();
		$this->monthElement->setName($name . '[month]');
		$this->monthElement->setAttribute('id', $name . '[month]');
		$this->monthElement->setAttribute('class', 'selectmonth');
		$this->monthElement->setOptions(array(
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
												$date = $date['year'] . '-' . $date['month'] . '-01 00:00:00';
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
		$this->monthElement = clone $this->monthElement;
		$this->yearElement = clone $this->yearElement;
	}
}

<?php

namespace Techfever\Template\Plugin\Forms;

use DateTime as PhpDateTime;
use Zend\Form\FormInterface;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\Date as DateValidator;
use Techfever\Exception\InvalidArgumentException;
use Zend\Form\Element\Select;
use Exception;

class SelectDate extends SelectMonth {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'selectdate' 
	);
	
	/**
	 * Select form element that contains values for day
	 *
	 * @var Select
	 */
	protected $dayElement;
	
	/**
	 * Constructor.
	 * Add the day select element
	 *
	 * @param null|int|string $name
	 *        	Optional name for the element
	 * @param array $options
	 *        	Optional options for the element
	 */
	public function __construct($name = null, $options = array()) {
		$this->dayElement = new Select ( $name . '[day]', $options );
		parent::__construct ( $name, $options );
		
		if (isset ( $options ['day_attributes'] )) {
			$this->setDayAttributes ( $options ['day_attributes'] );
		}
	}
	
	/**
	 * Accepted options for SelectDate (plus the ones from SelectMonth) :
	 * - day_attributes: HTML attributes to be rendered with the day element
	 *
	 * @param array|\Traversable $options        	
	 * @return SelectDate
	 */
	public function setOptions($options) {
		parent::setOptions ( $options );
		
		if (isset ( $options ['day_attributes'] )) {
			$this->setDayAttributes ( $options ['day_attributes'] );
		}
		
		return $this;
	}
	
	/**
	 *
	 * @return Select
	 */
	public function getDayElement() {
		return $this->dayElement;
	}
	
	/**
	 * Set the day attributes
	 *
	 * @param array $dayAttributes        	
	 * @return SelectDate
	 */
	public function setDayAttributes(array $dayAttributes) {
		$this->dayElement->setAttributes ( $dayAttributes );
		return $this;
	}
	
	/**
	 * Get the day attributes
	 *
	 * @return array
	 */
	public function getDayAttributes() {
		return $this->dayElement->getAttributes ();
	}
	
	/**
	 *
	 * @param string|array|\ArrayAccess|PhpDateTime $value        	
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 * @return void \Zend\Form\Element
	 */
	public function setValue($value) {
		if (is_string ( $value )) {
			try {
				$value = new PhpDateTime ( $value );
			} catch ( Exception $e ) {
				throw new InvalidArgumentException ( 'Value should be a parsable string or an instance of DateTime' );
			}
		}
		
		if ($value instanceof PhpDateTime) {
			$value = array (
					'year' => $value->format ( 'Y' ),
					'month' => $value->format ( 'm' ),
					'day' => $value->format ( 'd' ) 
			);
		}
		
		$this->yearElement->setValue ( $value ['year'] );
		$this->monthElement->setValue ( $value ['month'] );
		$this->dayElement->setValue ( $value ['day'] );
	}
	
	/**
	 * Prepare the form element (mostly used for rendering purposes)
	 *
	 * @param FormInterface $form        	
	 * @return mixed
	 */
	public function prepareElement(FormInterface $form) {
		parent::prepareElement ( $form );
		
		$name = $this->getName ();
		$this->dayElement->setName ( $name . '[day]' );
		$this->dayElement->setAttribute ( 'id', $name . '[day]' );
		$this->dayElement->setAttribute ( 'class', 'selectdate' );
		$this->dayElement->setOptions ( array (
				'empty_option' => '' 
		) );
	}
	
	/**
	 * Get validator
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator() {
		if (null === $this->validator) {
			$this->validator = new DateValidator ( array (
					'field' => $this->getName () . '[day]',
					'format' => 'Y-m-d H:i:s',
					'messages' => array (
							DateValidator::INVALID => 'text_error_invalid_value_type',
							DateValidator::INVALID_DATE => 'text_error_invalid_value_date',
							DateValidator::FALSEFORMAT => 'text_error_invalid_format' 
					) 
			) );
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
		return array (
				'name' => $this->getName (),
				'required' => false,
				'filters' => array (
						array (
								'name' => 'Callback',
								'options' => array (
										'callback' => function ($date) {
											// Convert the date to a specific format
											if (is_array ( $date )) {
												$date = $date ['year'] . '-' . $date ['month'] . '-' . $date ['day'] . ' 00:00:00';
											}
											
											return $date;
										} 
								) 
						) 
				),
				'validators' => array (
						$this->getValidator () 
				) 
		);
	}
	
	/**
	 * Clone the element (this is needed by Collection element, as it needs different copies of the elements)
	 */
	public function __clone() {
		$this->dayElement = clone $this->dayElement;
		$this->monthElement = clone $this->monthElement;
		$this->yearElement = clone $this->yearElement;
	}
}

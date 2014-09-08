<?php

namespace Techfever\Template\Plugin\Forms;

use Zend\Validator\InArray as InArrayValidator;
use Techfever\Template\Plugin\Forms\MultiCheckbox;

class Radio extends MultiCheckbox {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'radio' 
	);
	
	/**
	 * Get validator
	 *
	 * @return \Zend\Validator\ValidatorInterface
	 */
	protected function getValidator() {
		if (null === $this->validator && ! $this->disableInArrayValidator ()) {
			$this->validator = new InArrayValidator ( array (
					'haystack' => $this->getValueOptionsValues (),
					'strict' => false 
			) );
		}
		return $this->validator;
	}
}

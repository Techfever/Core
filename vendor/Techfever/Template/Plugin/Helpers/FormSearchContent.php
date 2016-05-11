<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormSearchContent extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag type="text"
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'name' => true,
			'autocomplete' => true,
			'autofocus' => true,
			'dirname' => true,
			'disabled' => true,
			'form' => true,
			'list' => true,
			'maxlength' => true,
			'pattern' => true,
			'placeholder' => true,
			'readonly' => true,
			'required' => true,
			'size' => true,
			'type' => true,
			'value' => true 
	);
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string FormInput
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
	
	/**
	 * Render a form <input> element from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		$name = $element->getName ();
		if ($name === null || $name === '') {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		$renderElement = '	<div class="row" type="input" id="' . $name . '">' . "\n";
		$renderElement .= '		<div class="label">' . call_user_func_array ( array (
				$this->getView (),
				'formLabel' 
		), array (
				$element 
		) ) . '&nbsp;:<span class="required">*</span></div>' . "\n";
		$renderElement .= '		<div class="value">' . call_user_func_array ( array (
				$this->getView (),
				'formInput' 
		), array (
				$element 
		) ) . "</div>\n";
		$element->setLabel ( 'text_search' );
		$element->setAttribute ( 'value', 'search' );
		$renderElement .= '		<div class="button">' . call_user_func_array ( array (
				$this->getView (),
				'formButton' 
		), array (
				$element 
		) ) . "</div>\n";
		$renderElement .= '	</div>' . "\n";
		$renderElement .= '	<div class="row" type="help" id="' . $name . '_help">' . "\n";
		$renderElement .= '		<div class="help"></div>' . "\n";
		$renderElement .= '	</div>';
		$renderElement .= '<script type="text/javascript">
		$(document).ready(function() {	
			$(this).ContentSearch();
    	});
		</script>';
		
		return $renderElement;
	}
}
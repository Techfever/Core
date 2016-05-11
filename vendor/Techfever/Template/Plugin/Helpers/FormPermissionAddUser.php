<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class FormPermissionAddUser extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'id' => true,
			'name' => true,
			'class' => true 
	);
	
	/**
	 * function call handler
	 *
	 * @param string $function
	 *        	Function name to call
	 * @param array $args
	 *        	Function arguments
	 * @return mixed
	 * @throws Exception\RuntimeException
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		if (is_object ( $this->generalobject )) {
			$obj = $this->generalobject;
			if (method_exists ( $obj, $name )) {
				if (is_array ( $arguments ) && count ( $arguments ) > 0) {
					return call_user_func_array ( array (
							$obj,
							$name 
					), $arguments );
				} else {
					return call_user_func ( array (
							$obj,
							$name 
					) );
				}
			}
		}
		return null;
	}
	
	/**
	 * Render a form <textarea> element from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		$name = $element->getName ();
		if (empty ( $name ) && $name !== 0) {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		$translator = $this->getTranslator ();
		
		$attributes = $element->getAttributes ();
		$attributes ['name'] = $name;
		$attributes ['id'] = $name;
		
		$attributes_paragraph ['name'] = $name . "_paragraph";
		$attributes_paragraph ['id'] = $name . "_paragraph";
		$attributes_paragraph ['class'] = 'paragraph';
		
		$attributes_content ['name'] = $name;
		$attributes_content ['id'] = $name;
		$attributes_content ['class'] = 'div';
		
		$default_value = null;
		$value = $element->getValue ();
		if (is_array ( $value ) && count ( $value ) > 0) {
			foreach ( $value as $data_key => $data_value ) {
				$default_value .= '<span id="' . $data_key . '">';
				$default_value .= '<input value="' . $data_key . '" id="' . $name . '" class="permissionuserusername" name="' . $name . '[]" type="hidden">';
				$default_value .= '<p>' . $data_value . '</p>&nbsp;';
				$default_value .= '<a href="#" onclick="$(this).cancelUser(\'' . $data_key . '\');">[&nbsp;Cancel&nbsp;]</a>';
				$default_value .= '<br>';
				$default_value .= '</span>';
			}
		}
		
		$escapeHtml = $this->getEscapeHtmlHelper ();
		$label = $escapeHtml ( $translator->translate ( 'text_add_user', $this->getTranslatorTextDomain () ) );
		
		return sprintf ( '<div %s>' . "\n" . '	<p %s>' . "\n" . '		<a>%s</a>' . "\n" . '	</p>' . "\n" . '	<div %s>%s</div>' . "\n" . '</div>', $this->createAttributesString ( $attributes ), $this->createAttributesString ( $attributes_paragraph ), $label, $this->createAttributesString ( $attributes_content ), $default_value );
	}
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string FormTextarea
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		$options = $element->getOptions ();
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		return $this->render ( $element );
	}
}

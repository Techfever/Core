<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Template\Plugin\Forms\Permission as PermissionElement;
use Techfever\Exception;

class FormPermission extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'class' => true 
	);
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @param null|string $labelPosition        	
	 * @return string FormMultiCheckbox
	 */
	public function __invoke(ElementInterface $element = null, $labelPosition = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
	
	/**
	 * Render a form <textarea> element from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		if (! $element instanceof PermissionElement) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s requires that the element is of type Zend\Form\Element\MultiCheckbox', __METHOD__ ) );
		}
		$value_options = $element->getValueOptions ();
		if (empty ( $value_options )) {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has "value_options"; none found', __METHOD__ ) );
		}
		
		$options = $element->getOptions ();
		$name = $element->getName ();
		if ($name === null || $name === '') {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		
		$translator = $this->getTranslator ();
		$allow = "";
		$deny = "";
		if (null !== $translator) {
			$allow = $translator->translate ( 'text_allow', $this->getTranslatorTextDomain () );
			$deny = $translator->translate ( 'text_deny', $this->getTranslatorTextDomain () );
		}
		
		$render = '<div id="' . $name . '" class="permission">';
		if (count ( $value_options ) > 0) {
			$render .= '<table border="0" cellspacing="0" cellpadding="0">';
			foreach ( $value_options as $key => $value ) {
				$title = "";
				if (isset ( $value ['title'] )) {
					$title = $value ['title'];
					if (null !== $translator) {
						$title = $translator->translate ( $title, $this->getTranslatorTextDomain () );
					}
				}
				$description = "";
				if (isset ( $value ['description'] )) {
					$description = $value ['description'];
					if (null !== $translator) {
						$description = $translator->translate ( $description, $this->getTranslatorTextDomain () );
					}
				}
				$select = false;
				if (isset ( $value ['selected'] ) && $value ['selected'] === "True") {
					$select = true;
				}
				$render .= '<tr id="' . $key . '_title">' . "\n";
				$render .= '<td colspan="2" class="title">' . $title . '</td>' . "\n";
				$render .= '</tr>' . "\n";
				$render .= '<tr id="' . $key . '_module">' . "\n";
				$render .= '<td class="description">' . "\n";
				$render .= $description;
				$render .= '</td>' . "\n";
				$render .= '<td class="action">' . "\n";
				$render .= '<div id="radio">' . "\n";
				$render .= '<input type="radio" id="' . $key . '_allow" value="allow" name="' . $name . '[' . $key . ']"' . ($select ? ' checked="checked"' : '') . '><label for="' . $key . '_allow">' . $allow . '</label>' . "\n";
				$render .= '<input type="radio" id="' . $key . '_deny" value="deny" name="' . $name . '[' . $key . ']"' . (! $select ? ' checked="checked"' : '') . '><label for="' . $key . '_deny">' . $deny . '</label>' . "\n";
				$render .= '</div>' . "\n";
				$render .= '</td>' . "\n";
				$render .= '</tr>' . "\n";
			}
			$render .= '</table>' . "\n";
		}
		$render .= '</div>' . "\n";
		return $render;
	}
}

<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Techfever\Exception;

class ViewDiv extends AbstractViewHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'name' => true,
			'id' => true,
			'class' => true 
	);
	
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
		
		$attributes = $element->getAttributes ();
		$attributes ['name'] = $name;
		$content = ( string ) $element->getContent ();
		$escapeHtml = $this->getEscapeHtmlHelper ();
		
		$label = $element->getLabel ();
		if (empty ( $label )) {
			throw new Exception\DomainException ( sprintf ( '%s expects either label content as the second argument, ' . 'or that the element provided has a label attribute; neither found', __METHOD__ ) );
		}
		
		if (null !== ($translator = $this->getTranslator ())) {
			$label = $translator->translate ( $label, $this->getTranslatorTextDomain () );
		}
		
		return sprintf ( '<div %s><div class="label">%s :</div><div class="value">%s</div></div>', $this->createAttributesString ( $attributes ), $escapeHtml ( $label ), $escapeHtml ( $content ) );
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
		
		return $this->render ( $element );
	}
}

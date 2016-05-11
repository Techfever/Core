<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\LabelAwareInterface;
use Zend\Form\View\Helper\FormButton as BFormButton;

class FormButton extends BFormButton {
	/**
	 * Render a form <button> element from the provided $element,
	 * using content from $buttonContent or the element's "label" attribute
	 *
	 * @param ElementInterface $element        	
	 * @param null|string $buttonContent        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element, $buttonContent = null) {
		$openTag = $this->openTag ( $element );
		
		if (null === $buttonContent) {
			$buttonContent = $element->getLabel ();
			if (null === $buttonContent) {
				throw new Exception\DomainException ( sprintf ( '%s expects either button content as the second argument, ' . 'or that the element provided has a label value; neither found', __METHOD__ ) );
			}
		}
		
		if (null !== ($translator = $this->getTranslator ())) {
			$buttonContent = $translator->translate ( $buttonContent, $this->getTranslatorTextDomain () );
		}
		
		if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption ( 'disable_html_escape' )) {
			$escapeHtmlHelper = $this->getEscapeHtmlHelper ();
			$buttonContent = $escapeHtmlHelper ( $buttonContent );
		}
		
		return $openTag . "<span>" . $buttonContent . "</span>" . $this->closeTag ();
	}
}

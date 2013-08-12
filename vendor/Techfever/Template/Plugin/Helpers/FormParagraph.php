<?php
namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
//use Techfever\Exception;

class FormParagraph extends AbstractHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array(
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
		/*
		$name = $element->getName();
		if (empty($name) && $name !== 0) {
		    throw new Exception\DomainException(sprintf('%s requires that the element has an assigned name; none discovered', __METHOD__));
		}
		 */
		$attributes = $element->getAttributes();
		//$attributes['name'] = $name;
		$content = (string) $element->getValue();
		$escapeHtml = $this->getEscapeHtmlHelper();
		return sprintf('<p %s>%s</p>', $this->createAttributesString($attributes), $escapeHtml($content));
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
		if (!$element) {
			return $this;
		}

		return $this->render($element);
	}
}

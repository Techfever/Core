<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Techfever\Exception;

class ViewInput extends AbstractViewHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array(
			'name' => true,
			'accept' => true,
			'alt' => true,
			'autocomplete' => true,
			'autofocus' => true,
			'checked' => true,
			'dirname' => true,
			'disabled' => true,
			'form' => true,
			'formaction' => true,
			'formenctype' => true,
			'formmethod' => true,
			'formnovalidate' => true,
			'formtarget' => true,
			'height' => true,
			'list' => true,
			'max' => true,
			'maxlength' => true,
			'min' => true,
			'multiple' => true,
			'pattern' => true,
			'placeholder' => true,
			'readonly' => true,
			'required' => true,
			'size' => true,
			'src' => true,
			'step' => true,
			'type' => true,
			'value' => true,
			'width' => true,
	);

	/**
	 * Valid values for the input type
	 *
	 * @var array
	 */
	protected $validTypes = array(
			'text' => true,
			'button' => true,
			'checkbox' => true,
			'file' => true,
			'hidden' => true,
			'image' => true,
			'password' => true,
			'radio' => true,
			'reset' => true,
			'select' => true,
			'submit' => true,
			'color' => true,
			'date' => true,
			'datetime' => true,
			'datetime-local' => true,
			'email' => true,
			'month' => true,
			'number' => true,
			'range' => true,
			'search' => true,
			'tel' => true,
			'time' => true,
			'url' => true,
			'week' => true,
	);

	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param  ElementInterface|null $element
	 * @return string|ViewInput
	 */
	public function __invoke(ElementInterface $element = null) {
		if (!$element) {
			return $this;
		}

		return $this->render($element);
	}

	/**
	 * Render a view <input> element from the provided $element
	 *
	 * @param  ElementInterface $element
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		$name = $element->getName();
		if ($name === null || $name === '') {
			throw new Exception\DomainException(sprintf('%s requires that the element has an assigned name; none discovered', __METHOD__));
		}

		$attributes = $element->getAttributes();
		$attributes['name'] = $name;
		$attributes['type'] = $this->getType($element);
		$attributes['value'] = $element->getContent();

		return sprintf('<input %s%s', $this->createAttributesString($attributes), $this->getInlineClosingBracket());
	}

	/**
	 * Determine input type to use
	 *
	 * @param  ElementInterface $element
	 * @return string
	 */
	protected function getType(ElementInterface $element) {
		$type = $element->getAttribute('type');
		if (empty($type)) {
			return 'text';
		}

		$type = strtolower($type);
		if (!isset($this->validTypes[$type])) {
			return 'text';
		}

		return $type;
	}
}

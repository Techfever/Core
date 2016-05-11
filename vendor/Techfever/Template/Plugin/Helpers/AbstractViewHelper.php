<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;
use Zend\View\Helper\Doctype;
use Zend\View\Helper\EscapeHtml;
use Zend\View\Helper\EscapeHtmlAttr;
use Techfever\Template\Plugin\Helpers\ViewFactory;

/**
 * Base functionality for all view helpers
 */
abstract class AbstractViewHelper extends BaseAbstractHelper {
	/**
	 * Standard boolean attributes, with expected values for enabling/disabling
	 *
	 * @var array
	 */
	protected $booleanAttributes = array ();
	
	/**
	 * Translatable attributes
	 *
	 * @var array
	 */
	protected $translatableAttributes = array (
			'placeholder' => true 
	);
	
	/**
	 *
	 * @var Doctype
	 */
	protected $doctypeHelper;
	
	/**
	 *
	 * @var EscapeHtml
	 */
	protected $escapeHtmlHelper;
	
	/**
	 *
	 * @var ViewFactory
	 */
	protected $viewFactoryHelper;
	
	/**
	 *
	 * @var EscapeHtmlAttr
	 */
	protected $escapeHtmlAttrHelper;
	
	/**
	 * Attributes globally valid for all tags
	 *
	 * @var array
	 */
	protected $validGlobalAttributes = array (
			'accesskey' => true,
			'class' => true,
			'contenteditable' => true,
			'contextmenu' => true,
			'dir' => true,
			'draggable' => true,
			'dropzone' => true,
			'hidden' => true,
			'id' => true,
			'lang' => true,
			'spellcheck' => true,
			'style' => true,
			'tabindex' => true,
			'title' => true 
	);
	
	/**
	 * Attributes valid for the tag represented by this helper
	 *
	 * This should be overridden in extending classes
	 *
	 * @var array
	 */
	protected $validTagAttributes = array ();
	
	/**
	 * Set value for doctype
	 *
	 * @param string $doctype        	
	 * @return AbstractHelper
	 */
	public function setDoctype($doctype) {
		$this->getDoctypeHelper ()->setDoctype ( $doctype );
		return $this;
	}
	
	/**
	 * Get value for doctype
	 *
	 * @return string
	 */
	public function getDoctype() {
		return $this->getDoctypeHelper ()->getDoctype ();
	}
	
	/**
	 * Set value for character encoding
	 *
	 * @param string $encoding        	
	 * @return AbstractHelper
	 */
	public function setEncoding($encoding) {
		$this->getEscapeHtmlHelper ()->setEncoding ( $encoding );
		return $this;
	}
	
	/**
	 * Get character encoding
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->getEscapeHtmlHelper ()->getEncoding ();
	}
	
	/**
	 * Create a string of all attribute/value pairs
	 *
	 * Escapes all attribute values
	 *
	 * @param array $attributes        	
	 * @return string
	 */
	public function createAttributesString(array $attributes) {
		$attributes = $this->prepareAttributes ( $attributes );
		$escape = $this->getEscapeHtmlHelper ();
		$strings = array ();
		foreach ( $attributes as $key => $value ) {
			$key = strtolower ( $key );
			if (! $value && isset ( $this->booleanAttributes [$key] )) {
				// Skip boolean attributes that expect empty string as false value
				if ('' === $this->booleanAttributes [$key] ['off']) {
					continue;
				}
			}
			
			// check if attribute is translatable
			if (isset ( $this->translatableAttributes [$key] ) && ! empty ( $value )) {
				if (($translator = $this->getTranslator ()) !== null) {
					$value = $translator->translate ( $value, $this->getTranslatorTextDomain () );
				}
			}
			
			// @TODO Escape event attributes like AbstractHtmlElement view helper does in htmlAttribs ??
			$strings [] = sprintf ( '%s="%s"', $escape ( $key ), $escape ( $value ) );
		}
		return implode ( ' ', $strings );
	}
	
	/**
	 * Get the ID of an element
	 *
	 * If no ID attribute present, attempts to use the name attribute.
	 * If no name attribute is present, either, returns null.
	 *
	 * @param ElementInterface $element        	
	 * @return null string
	 */
	public function getId(ElementInterface $element) {
		$id = $element->getAttribute ( 'id' );
		if (null !== $id) {
			return $id;
		}
		
		return $element->getName ();
	}
	
	/**
	 * Get the closing bracket for an inline tag
	 *
	 * Closes as either "/>" for XHTML doctypes or ">" otherwise.
	 *
	 * @return string
	 */
	public function getInlineClosingBracket() {
		$doctypeHelper = $this->getDoctypeHelper ();
		if ($doctypeHelper->isXhtml ()) {
			return '/>';
		}
		return '>';
	}
	
	/**
	 * Retrieve the doctype helper
	 *
	 * @return Doctype
	 */
	protected function getDoctypeHelper() {
		if ($this->doctypeHelper) {
			return $this->doctypeHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->doctypeHelper = $this->view->plugin ( 'doctype' );
		}
		
		if (! $this->doctypeHelper instanceof Doctype) {
			$this->doctypeHelper = new Doctype ();
		}
		
		return $this->doctypeHelper;
	}
	
	/**
	 * Retrieve the escapeHtml helper
	 *
	 * @return EscapeHtml
	 */
	protected function getEscapeHtmlHelper() {
		if ($this->escapeHtmlHelper) {
			return $this->escapeHtmlHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->escapeHtmlHelper = $this->view->plugin ( 'escapehtml' );
		}
		
		if (! $this->escapeHtmlHelper instanceof EscapeHtml) {
			$this->escapeHtmlHelper = new EscapeHtml ();
		}
		
		return $this->escapeHtmlHelper;
	}
	
	/**
	 * Retrieve the viewFactory helper
	 *
	 * @return ViewFactory
	 */
	protected function getViewFactoryHelper() {
		if ($this->viewFactoryHelper) {
			return $this->viewFactoryHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->viewFactoryHelper = $this->view->plugin ( 'viewfactory' );
		}
		
		if (! $this->viewFactoryHelper instanceof ViewFactory) {
			$this->viewFactoryHelper = new ViewFactory ();
		}
		
		return $this->viewFactoryHelper;
	}
	
	/**
	 * Retrieve the escapeHtmlAttr helper
	 *
	 * @return EscapeHtmlAttr
	 */
	protected function getEscapeHtmlAttrHelper() {
		if ($this->escapeHtmlAttrHelper) {
			return $this->escapeHtmlAttrHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->escapeHtmlAttrHelper = $this->view->plugin ( 'escapehtmlattr' );
		}
		
		if (! $this->escapeHtmlAttrHelper instanceof EscapeHtmlAttr) {
			$this->escapeHtmlAttrHelper = new EscapeHtmlAttr ();
		}
		
		return $this->escapeHtmlAttrHelper;
	}
	
	/**
	 * Prepare attributes for rendering
	 *
	 * Ensures appropriate attributes are present (e.g., if "name" is present,
	 * but no "id", sets the latter to the viewer).
	 *
	 * Removes any invalid attributes
	 *
	 * @param array $attributes        	
	 * @return array
	 */
	protected function prepareAttributes(array $attributes) {
		foreach ( $attributes as $key => $value ) {
			$attribute = strtolower ( $key );
			
			if (! isset ( $this->validGlobalAttributes [$attribute] ) && ! isset ( $this->validTagAttributes [$attribute] ) && 'data-' != substr ( $attribute, 0, 5 )) {
				// Invalid attribute for the current tag
				unset ( $attributes [$key] );
				continue;
			}
			
			// Normalize attribute key, if needed
			if ($attribute != $key) {
				unset ( $attributes [$key] );
				$attributes [$attribute] = $value;
			}
			
			// Normalize boolean attribute values
			if (isset ( $this->booleanAttributes [$attribute] )) {
				$attributes [$attribute] = $this->prepareBooleanAttributeValue ( $attribute, $value );
			}
		}
		
		return $attributes;
	}
	
	/**
	 * Prepare a boolean attribute value
	 *
	 * Prepares the expected representation for the boolean attribute specified.
	 *
	 * @param string $attribute        	
	 * @param mixed $value        	
	 * @return string
	 */
	protected function prepareBooleanAttributeValue($attribute, $value) {
		if (! is_bool ( $value ) && in_array ( $value, $this->booleanAttributes [$attribute] )) {
			return $value;
		}
		
		$value = ( bool ) $value;
		return ($value ? $this->booleanAttributes [$attribute] ['on'] : $this->booleanAttributes [$attribute] ['off']);
	}
}

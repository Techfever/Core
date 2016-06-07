<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * Renders HTML Element tag (both opening and closing) of a web page, to which some custom
 * attributes can be added dynamically.
 */
class HtmlElement extends AbstractHtmlElement {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'tag' => "div",
			'attributes' => array (),
			'element' => array (),
			'content' => '',
			'append' => 'after',
			'deep' => 0 
	);
	
	/**
	 * Attributes for the HTML Element tag.
	 *
	 * @var array
	 */
	protected $attributes = array ();
	
	/**
	 * HTML Element tag.
	 *
	 * @var String
	 */
	protected $tag = "div";
	
	/**
	 * HTML Element Element.
	 *
	 * @var String/Object
	 */
	protected $element;
	
	/**
	 * HTML Element Content.
	 *
	 * @var String
	 */
	protected $content = "";
	
	/**
	 * HTML Element Append.
	 *
	 * @var String
	 */
	protected $append = "after";
	
	/**
	 * HTML Element Deep.
	 *
	 * @var String
	 */
	protected $deep = 1;
	
	/**
	 * Retrieve object instance; optionally add attributes.
	 *
	 * @param array $attribs        	
	 * @return self
	 */
	public function __invoke($options = array()) {
		$options = array_merge ( $this->options, $options );
		$this->setTag ( $options ['tag'] );
		$this->setAttributes ( $options ['attributes'] );
		$this->setDeep ( $options ['deep'] );
		$this->setElement ( $options ['element'] );
		$this->setContent ( $options ['content'] );
		$this->setAppend ( $options ['append'] );
		
		return clone $this;
	}
	
	/**
	 * Set new deep.
	 *
	 * @param string $deep        	
	 * @return self
	 */
	public function setDeep($deep) {
		$this->deep = ( int ) $deep;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDeep() {
		return $this->deep;
	}
	
	/**
	 * Set new tag.
	 *
	 * @param string $tag        	
	 * @return self
	 */
	public function setTag($tag) {
		$this->tag = strtolower ( $tag );
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}
	
	/**
	 * is new element.
	 *
	 * @return boolean
	 */
	public function isContent() {
		return (is_object ( $this->element ) || is_array ( $this->element ) || ! empty ( $this->element ) || ! empty ( $this->content ) ? True : False);
	}
	
	/**
	 * Set new content.
	 *
	 * @param mix $content        	
	 * @return self
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}
	
	/**
	 *
	 * @return mix
	 */
	public function getContent() {
		if (! empty ( $this->content )) {
			return $this->content;
		}
		return '';
	}
	
	/**
	 * Set new append.
	 *
	 * @param mix $append        	
	 * @return self
	 */
	public function setAppend($append) {
		$this->append = $append;
		return $this;
	}
	
	/**
	 *
	 * @return mix
	 */
	public function getAppend() {
		if (! empty ( $this->append )) {
			return strtolower ( $this->append );
		}
		return 'after';
	}
	
	/**
	 * Set new element.
	 *
	 * @param mix $element        	
	 * @return self
	 */
	public function setElement($element) {
		$this->element = $element;
		return $this;
	}
	
	/**
	 *
	 * @return mix
	 */
	public function getElement() {
		if (! empty ( $this->element )) {
			return $this->element;
		}
		return '';
	}
	
	/**
	 *
	 * @return mix
	 */
	public function getIdent($deep = 0) {
		if ($deep > 0) {
			$deep = ($deep - 1);
		} else {
			$deep = $this->getDeep ();
		}
		return str_repeat ( '	', $deep );
	}
	
	/**
	 * Set new attribute.
	 *
	 * @param string $attrName        	
	 * @param string $attrValue        	
	 * @return self
	 */
	public function setAttribute($attrName, $attrValue) {
		$this->attributes [$attrName] = $attrValue;
		return $this;
	}
	
	/**
	 * Add new or overwrite the existing attributes.
	 *
	 * @param array $attribs        	
	 * @return self
	 */
	public function setAttributes(array $attribs) {
		$this->attributes = $attribs;
		return $this;
	}
	
	/**
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}
	
	/**
	 * Render opening tag.
	 *
	 * @return string
	 */
	public function render() {
		$element = null;
		if (is_array ( $this->getElement () )) {
			$elementArray = $this->getElement ();
			foreach ( $elementArray as $elementRaw ) {
				$elementRaw->setDeep ( ($this->getDeep () + 1) );
				$element .= $elementRaw->render ();
			}
		} elseif (is_object ( $this->getElement () )) {
			$elementRaw = $this->getElement ();
			$elementRaw->setDeep ( ($this->getDeep () + 1) );
			$element = $elementRaw->render ();
		}
		$content = $this->getContent ();
		if (! empty ( $content )) {
			$content = $this->getIdent () . $content . "\n";
			if ($this->getAppend () === "after") {
				$element = $element . $content;
			} elseif ($this->getAppend () === "before") {
				$element = $content . $element;
			}
		}
		return sprintf ( '%s%s%s', $this->getIdent () . $this->openTag (), $element, (empty ( $this->getContent () ) && empty ( $this->element ) ? null : $this->getIdent ()) . $this->closeTag () ) . ($this->isContent () ? "\n" : null);
	}
	
	/**
	 * Render opening tag.
	 *
	 * @return string
	 */
	public function openTag() {
		return sprintf ( '<%s%s>' . ($this->isContent () ? (empty ( $this->getContent () ) && empty ( $this->element ) ? null : "\n") : null), $this->getTag (), $this->htmlAttribs ( $this->getAttributes () ) );
	}
	
	/**
	 * Render closing tag.
	 *
	 * @return string
	 */
	public function closeTag() {
		return sprintf ( '</%s>', $this->getTag () ) . ($this->isContent () ? null : "\n");
	}
}

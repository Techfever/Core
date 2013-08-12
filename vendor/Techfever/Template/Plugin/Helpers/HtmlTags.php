<?php
namespace Techfever\Template\Plugin\Helpers;

use Zend\View\Helper\AbstractHtmlElement;

class HtmlLink extends AbstractHtmlElement {
	/**
	 * Generates a HTML element.
	 *
	 * @param string $tag        	
	 * @param string $object        	
	 * @param array $attribs        	
	 * @param bool $closetag
	 *        	Close tag.
	 * @param bool $escape
	 *        	Escape the items.
	 * @return string Link XHTML.
	 */
	public function __invoke($tag = null, $object = null, $attribs = false, $closetag = false, $escape = true) {
		if ($attribs) {
			$attribs = $this->htmlAttribs($attribs);
		} else {
			$attribs = '';
		}
		return '<' . $tag . $attribs . '>' . $object . ($closetag ? '</' . $tag . '>' : null);
	}
}

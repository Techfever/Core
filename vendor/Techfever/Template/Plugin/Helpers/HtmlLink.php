<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\View\Helper\AbstractHtmlElement;

class HtmlLink extends AbstractHtmlElement {
	/**
	 * Generates a 'Link' element.
	 *
	 * @param string $url        	
	 * @param string $object        	
	 * @param array $attribs        	
	 * @param bool $escape
	 *        	Escape the items.
	 * @return string Link XHTML.
	 */
	public function __invoke($url = null, $object = null, $attribs = false, $escape = true) {
		$href = null;
		if (! empty ( $url )) {
			$href = ' href="';
			if ($escape) {
				$escaper = $this->view->plugin ( 'escapeHtml' );
				$href .= $escaper ( $url );
			} else {
				$href .= $url;
			}
			$href .= '"';
		}
		
		if ($attribs) {
			if (array_key_exists ( 'href', $attribs )) {
				unset ( $attribs ['href'] );
			}
			$attribs = $this->htmlAttribs ( $attribs );
		} else {
			$attribs = '';
		}
		
		$tag = 'a';
		
		return '<' . $tag . $href . $attribs . '>' . $object . '</' . $tag . '>';
	}
}

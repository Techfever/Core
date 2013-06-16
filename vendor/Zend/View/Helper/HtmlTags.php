<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\View\Helper;

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
			$attribs = $this->htmlAttribs ( $attribs );
		} else {
			$attribs = '';
		}
		return '<' . $tag . $attribs . '>' . $object . ($closetag ? '</' . $tag . '>' : null);
	}
}

<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\View\Helper;

class HtmlImage extends AbstractHtmlElement {
	/**
	 * Generates a 'Image' element.
	 *
	 * @param string $url        	
	 * @param string $object        	
	 * @param array $attribs        	
	 * @param bool $escape
	 *        	Escape the items.
	 * @return string Image XHTML.
	 */
	public function __invoke($src = null, $attribs = false, $escape = true) {
		$href = null;
		if (! empty ( $src )) {
			$href = ' src="';
			if ($escape) {
				$escaper = $this->view->plugin ( 'escapeHtml' );
				$href .= $escaper ( $src );
			} else {
				$href .= $src;
			}
			$href .= '"';
		}
		
		if ($attribs) {
			if (array_key_exists ( 'src', $attribs )) {
				unset ( $attribs ['src'] );
			}
			$attribs = $this->htmlAttribs ( $attribs );
		} else {
			$attribs = '';
		}
		
		$tag = 'img';
		
		return '<' . $tag . $href . $attribs . '>';
	}
}

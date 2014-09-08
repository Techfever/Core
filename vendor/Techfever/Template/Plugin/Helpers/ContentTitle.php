<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\View\Helper\AbstractHelper;
use Techfever\Exception;

/**
 * Helper for retrieving the content title.
 */
class ContentTitle extends AbstractHelper {
	/**
	 * content title.
	 *
	 * @var string
	 */
	protected $contentTitle;
	
	/**
	 * Returns content title
	 *
	 * $file is appended to the base path for simplicity.
	 *
	 * @param string|null $file        	
	 * @throws Exception\RuntimeException
	 * @return string
	 */
	public function __invoke($title = null) {
		if (null === $this->contentTitle) {
			throw new Exception\RuntimeException ( 'No content title provided' );
		}
		
		return $this->contentTitle . $title;
	}
	
	/**
	 * Set the base path.
	 *
	 * @param string $contentTitle        	
	 * @return self
	 */
	public function set($contentTitle) {
		$this->contentTitle = $contentTitle;
		return $this;
	}
}

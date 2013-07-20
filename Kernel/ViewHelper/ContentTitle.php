<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\ViewHelper;

use Zend\View\Exception;
use Zend\View\Helper\AbstractHelper;

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
	 * @param  string|null $file
	 * @throws Exception\RuntimeException
	 * @return string
	 */
	public function __invoke($title = null) {
		if (null === $this->contentTitle) {
			throw new Exception\RuntimeException('No content title provided');
		}

		return $this->contentTitle . $title;
	}

	/**
	 * Set the base path.
	 *
	 * @param  string $contentTitle
	 * @return self
	 */
	public function set($contentTitle) {
		$this->contentTitle = $contentTitle;
		return $this;
	}
}

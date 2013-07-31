<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kernel\ViewHelper;

use Zend\View\Helper\AbstractHelper;
use Kernel\ServiceLocator;
use Kernel\Exception;

/**
 * Helper for retrieving the base href.
 */
class BaseHref extends AbstractHelper {
	/**
	 * Base href.
	 *
	 * @var string
	 */
	protected $baseHref;

	/**
	 * Returns site's base href, or file with base href prepended.
	 *
	 * $file is appended to the base href for simplicity.
	 *
	 * @throws Exception\RuntimeException
	 * @return string
	 */
	public function __invoke() {
		if (null === $this->baseHref) {
			$config = ServiceLocator::getServiceConfig('view_manager');
			if (isset($config['view_manager']) && isset($config['view_manager']['base_href'])) {
				$this->baseHref = $config['view_manager']['base_href'];
			} else {
				$this->baseHref = ServiceLocator::getServiceManager('Request')->getBaseHref();
			}
		}

		return $this->baseHref;
	}

	/**
	 * Set the base href.
	 *
	 * @param  string $baseHref
	 * @return self
	 */
	public function setBaseHref($baseHref) {
		$this->baseHref = $baseHref;
		return $this;
	}
}

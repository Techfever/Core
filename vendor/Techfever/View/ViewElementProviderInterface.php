<?php

namespace Techfever\View;

interface ViewElementProviderInterface {
	/**
	 * Expected to return \Zend\ServiceManager\Config object or array to
	 * seed such an object.
	 *
	 * @return array \Zend\ServiceManager\Config
	 */
	public function getViewElementConfig();
}

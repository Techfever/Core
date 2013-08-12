<?php

namespace Techfever\View;

interface ViewFactoryAwareInterface {
	/**
	 * Compose a View factory into the object
	 *
	 * @param Factory $factory
	 */
	public function setViewFactory(Factory $factory);
}

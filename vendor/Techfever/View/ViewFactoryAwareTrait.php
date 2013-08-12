<?php

namespace Techfever\View;

use \Techfever\View\Factory;

trait ViewFactoryAwareTrait {
	/**
	 * @var Factory
	 */
	protected $factory = null;

	/**
	 * Compose a View factory into the object
	 *
	 * @param Factory $factory
	 * @return mixed
	 */
	public function setViewFactory(Factory $factory) {
		$this->factory = $factory;

		return $this;
	}
}

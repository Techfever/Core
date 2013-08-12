<?php

namespace Techfever\View;

interface ElementPrepareAwareInterface {
	/**
	 * Prepare the View element (mostly used for rendering purposes)
	 *
	 * @param ViewInterface $view
	 * @return mixed
	 */
	public function prepareElement(ViewInterface $view);
}

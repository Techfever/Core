<?php
namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getTemplate extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$Template = $this->getController()->getServiceLocator()->get('template');

		return $Template;
	}
}

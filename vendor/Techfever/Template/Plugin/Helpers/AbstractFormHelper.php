<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\Template\Plugin\Helpers\FormFactory;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Base functionality for all form view helpers
 */
abstract class AbstractFormHelper extends AbstractHelper {
	
	/**
	 *
	 * @var FormFactory
	 */
	protected $formFactoryHelper;
	
	/**
	 * Retrieve the formFactory helper
	 *
	 * @return FormFactory
	 */
	protected function getFormFactoryHelper() {
		if ($this->formFactoryHelper) {
			return $this->formFactoryHelper;
		}
		
		if (method_exists ( $this->view, 'plugin' )) {
			$this->formFactoryHelper = $this->view->plugin ( 'formfactory' );
		}
		
		if (! $this->formFactoryHelper instanceof FormFactory) {
			$this->formFactoryHelper = new FormFactory ();
		}
		
		return $this->formFactoryHelper;
	}
}

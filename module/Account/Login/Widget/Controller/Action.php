<?php

namespace Account\Login\Widget\Controller;

use Techfever\Widget\Controller\General;
use Techfever\User\Form\Defined as UserLoginForm;

class ActionController extends General {
	
	/**
	 *
	 * @var Input Form
	 */
	protected $inputform = "";
	
	/**
	 * Initial Action
	 *
	 * @return WidgetModel
	 */
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		$status = true;
		
		$content = $this->InputForm ();
		
		$this->setContent ( array (
				'content' => $content,
				'success' => $status 
		) );
		$this->setSuccess ( $status );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		if (! $this->inputform instanceof \Zend\Form\FormInterface) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'id' => 'Account/Login/Index',
					'controller' => 'Account\Login\Controller\Action',
					'action' => 'Index' 
			);
			$this->inputform = new UserLoginForm ( $options );
		}
		return $this->inputform;
	}
}

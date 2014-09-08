<?php

namespace Index\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ActionController extends AbstractActionController {
	public function __construct() {
	}
	public function IndexAction() {
		return $this->redirect ()->toRoute ( 'Account/Login', array (
				'action' => 'Index' 
		) );
	}
}

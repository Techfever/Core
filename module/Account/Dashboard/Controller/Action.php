<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class DashboardActionController extends AbstractActionController {
	protected $type = 'account';
	protected $module = 'dashboard';
	protected $inputform = null;
	public function IndexAction() {
		return array (
				'dashboard' => "" 
		);
	}
}

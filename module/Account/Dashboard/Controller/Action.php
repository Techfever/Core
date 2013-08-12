<?php
namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserUpdateForm;

class DashboardActionController extends AbstractActionController {

	protected $type = 'account';

	protected $module = 'dashboard';

	protected $inputform = null;

	public function IndexAction() {
			return array(
					'dashboard' => "",
			);
	}
}

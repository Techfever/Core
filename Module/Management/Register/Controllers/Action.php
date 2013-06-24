<?php
namespace Management\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RegisterActionController extends AbstractActionController {
	public function __construct() {
	}
	public function IndexAction() {
	}
	public function PreviewAction() {
	}
	public function DoneAction() {
	}
	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}
}

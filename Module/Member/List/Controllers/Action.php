<?php
namespace Member\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ListActionController extends AbstractActionController {
	public function __construct() {
	}
	public function IndexAction() {
	}
	public function SearchAction() {
	}
	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}
}

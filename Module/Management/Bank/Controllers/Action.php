<?php
namespace Management\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BankActionController extends AbstractActionController {
	public function __construct() {
	}
	public function SearchAction() {
	}
	public function EditAction() {
	}
	public function PreviewAction() {
	}
	public function DoneAction() {
	}
	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}
}

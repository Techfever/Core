<?php
namespace Content\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Kernel\ServiceLocator;

class ManageActionController extends AbstractActionController {
	public function __construct() {
	}
	public function ListAction() {
	}
	public function EditAction() {
		$this->id = (int) $this->params()->fromRoute('id', null);
		if (!$this->id) {
			return $this->redirect()->toRoute($this->GetRoute(), array(
							'action' => 'New'
					));
		}
	}
	public function DeleteAction() {
		$this->id = (int) $this->params()->fromRoute('id', null);
		if (!$this->id) {
			return $this->redirect()->toRoute($this->GetRoute(), array(
							'action' => 'New'
					));
		}
	}
	public function NewAction() {
	}
	public function PreviewAction() {
		$this->id = (int) $this->params()->fromRoute('id', null);
		if (!$this->id) {
			return $this->redirect()->toRoute($this->GetRoute(), array(
							'action' => 'New'
					));
		}
	}
	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}
}

<?php

namespace Content\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ManageActionController extends AbstractActionController {
	public function __construct() {
	}
	public function IndexAction() {
	}
	public function ListAction() {
	}
	public function EditAction() {
		$this->id = ( int ) $this->params ()->fromRoute ( 'id', null );
		if (! $this->id) {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'New' 
			) );
		}
	}
	public function DeleteAction() {
		$this->id = ( int ) $this->params ()->fromRoute ( 'id', null );
		if (! $this->id) {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'New' 
			) );
		}
	}
	public function NewAction() {
	}
	public function PreviewAction() {
		$this->id = ( int ) $this->params ()->fromRoute ( 'id', null );
		if (! $this->id) {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'New' 
			) );
		}
	}
}

<?php
namespace Member\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Module\Member\Register\Form\Input as InputForm;
use Module\Member\Register\Form\Verify as InputVerify;
use Kernel\Template;

class RegisterActionController extends AbstractActionController {
	public function __construct() {
		Template::addCSS("ui-lightness/jquery-ui.css", "jquery");
	}
	public function IndexAction() {
		$form = new InputForm();
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$content = new InputVerify();
			$form->setInputFilter($content->getInputFilter());
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$content->exchangeArray($form->getData());
				$this->contentManagement->saveData($content);

				return $this->redirect()->toRoute();
			}
		}
		return array(
			'title' => 'text_member_register_title', 'form' => $form
		);
	}
	public function PreviewAction() {
	}
	public function DoneAction() {
	}
	public function GetRoute() {
		return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
	}
}

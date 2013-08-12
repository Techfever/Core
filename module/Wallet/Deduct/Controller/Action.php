<?php
namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class DeductActionController extends AbstractActionController {

	protected $type = 'wallet';

	protected $module = 'deduct';

	protected $inputform = null;

	public function IndexAction() {
		$this->getTemplate()->addCSS("ui-lightness/jquery-ui.css", "jquery");
		$this->getTemplate()->addCSS("vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css");
		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/account.update.js", array(
						'updateformid' => $this->getMatchedRouteName(),
						'updateformuri' => $this->getMatchedRouteName(),
						'updateformaction' => 'Index',
				));

		$InputForm = $this->InputForm();
		if ($InputForm->isXmlHttpRequest()) {
			$valid = false;
			$redirect = null;
			$messages = array();
			$messagescount = 0;
			$relation = null;
			$input = $InputForm->getPost('Input', null);
			if (!is_null($input)) {
				$relation = $InputForm->getValidatorRelation($input);
				$messages[$input] = "";
			}
			if ($InputForm->isPost()) {
				if ($InputForm->isValid()) {
					$submit = strtolower($InputForm->getPost('submit', 'preview'));
					if ($submit == 'submit') {
						$valid = true;
						$id = 1;
						$data = $InputForm->getData();
						if ($this->getUserManagement()->verifyID($id) && $this->getUserManagement()->updateSecurity($id, $data)) {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_success_msg_user_update_' . $this->module));
						} else {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_error_msg_user_update_' . $this->module));
						}
						$redirect = $this->url()->fromRoute($this->getMatchedRouteName(), array(
										'action' => 'Index'
								));
					}
				} else {
					$messages_data = $InputForm->getMessages();
					$messagescount = count($messages_data);
					if (count($messages_data) > 0) {
						foreach ($messages_data as $messages_key => $messages_data) {
							foreach ($messages_data as $messages_data) {
								$messages[$messages_key] = $messages_data;
							}
						}
					}
				}
			}
			$InputForm->getResponse()->setContent(Json::encode(array(
							'input' => $input,
							'valid' => $valid,
							'redirect' => $redirect,
							'relation' => $relation,
							'messages' => $messages,
							'messagescount' => $messagescount,
					)));
			return $InputForm->getResponse();
		} else {
			return array(
					'inputmodel' => $this->ViewModel()
			);
		}
	}

	private function ViewModel() {
		$ViewModel = new ViewModel();
		$ViewModel->setTerminal(true);
		$ViewModel->setTemplate('share/account/update');
		$ViewModel->setVariables(array(
						'form' => $this->InputForm()
				));
		return $this->getServiceLocator()->get('viewrenderer')->render($ViewModel);
	}

	private function InputForm() {
		if (!is_object($this->inputform)) {
			$id = $this->getUserAccess()->getID();
			$rank_group = $this->getUserAccess()->getRankGroupID();
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
					'rank' => $rank_group,
					'action' => 'Index',
			);
			if ($this->getUserManagement()->verifyID($id, $rank_group)) {
				$data = $this->getUserManagement()->getData($id, $rank_group);
				if (count($data) > 0) {
					$options['value'] = $data;
				}
			}
			$this->inputform = new UserUpdateForm($options);
		}
		return $this->inputform;
	}
}

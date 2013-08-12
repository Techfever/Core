<?php
namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserLoginForm;

class LoginActionController extends AbstractActionController {

	protected $type = 'wallet';

	protected $module = 'login';

	protected $inputform = null;

	public function IndexAction() {
		if ($this->getUserAccess()->isLogin()) {
			return $this->redirect()->toRoute('Wallet/List', array(
							'action' => 'Index'
					));
		}

		$this->getTemplate()->addCSS("ui-lightness/jquery-ui.css", "jquery");
		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.login.js", array(
						'loginformid' => $this->getMatchedRouteName(),
						'loginformuri' => $this->getMatchedRouteName(),
						'loginformaction' => 'Index',
				));

		$InputForm = $this->InputForm();
		if ($InputForm->isXmlHttpRequest()) {
			$valid = false;
			$redirect = null;
			$messages = array();
			$messagescount = 0;
			$flashmessages = null;
			$input = $InputForm->getPost('Input', null);
			$relation = null;
			if (!is_null($input)) {
				$relation = $InputForm->getValidatorRelation($input);
				$messages[$input] = "";
			}
			if ($InputForm->isPost()) {
				if ($InputForm->isValid()) {
					$submit = strtolower($InputForm->getPost('submit', null));
					$username = $InputForm->getPost('wallet_username', null);
					$password = $InputForm->getPost('wallet_password', null);
					$loginID = $this->getUserAccess()->verifyPassword($username, $password);
					if ($submit == 'submit' && $loginID > 0) {
						$valid = true;
						$this->getUserAccess()->setLogin($loginID);
						$redirect = $this->url()->fromRoute($this->getMatchedRouteName(), array(
										'action' => 'Index'
								));
					} else {
						$flashmessages = '<div class="ui-state-error ui-corner-all"><span><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>' . $this->getTranslate('text_error_msg_wallet_' . $this->module) . '</span></div>';
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
			$InputForm->getResponse()
					->setContent(Json::encode(array(
							'input' => $input,
							'valid' => $valid,
							'redirect' => $redirect,
							'relation' => null,
							'flashmessages' => $flashmessages,
							'messages' => $messages,
							'messagescount' => $messagescount,
					)));
			return $InputForm->getResponse();
		} else {
			return array(
					'form' => $InputForm,
			);
		}
	}

	private function InputForm() {
		if (!is_object($this->inputform) || !empty($id)) {
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
					'action' => 'Index',
			);
			$this->inputform = new UserLoginForm($options);
		}
		return $this->inputform;
	}
}

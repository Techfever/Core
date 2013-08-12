<?php
namespace Trader\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserUpdateForm;
use Techfever\Functions\Crypt\Encode as Encrypt;
use Techfever\Functions\Crypt\Decode as Decrypt;

class RegisterActionController extends AbstractActionController {

	protected $rankgroup = 99999;

	protected $type = 'trader';

	protected $module = 'register';

	protected $inputform = null;

	protected $viewdata = null;

	public function IndexAction() {
		$this->getTemplate()->addCSS("ui-lightness/jquery-ui.css", "jquery");
		$this->getTemplate()->addCSS("vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css");
		$this->getTemplate()->addCSS("vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/steps.css");

		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/steps.js");
		$this->getTemplate()
				->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.register.js",
						array(
								'stepsformid' => $this->getMatchedRouteName(),
								'stepsformuri' => $this->getMatchedRouteName(),
								'stepsformaction' => 'Index',
								'stepsformdialogtitle' => $this->getTranslate("text_dialog_user_register_title"),
								'stepsformdialogcontent' => $this->getTranslate("text_dialog_user_register_content"),
						));
		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.address.js", array(
						'addressformid' => $this->getMatchedRouteName() . '/Index',
						'addressformuri' => $this->getMatchedRouteName()
				));
		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.bank.js", array(
						'bankformid' => $this->getMatchedRouteName() . '/Index',
						'bankformuri' => $this->getMatchedRouteName()
				));

		$InputForm = $this->InputForm();
		if ($InputForm->isXmlHttpRequest()) {
			$messages = array();
			$subaction = null;
			$input = null;
			$relation = null;
			$messagescount = 0;
			$valid = false;
			$redirect = null;
			$id = 0;
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
						$data = $InputForm->getData();
						$data['user_username_open_tag'] = null;
						$data['user_username_min'] = null;
						$data['user_username_max'] = null;
						$data['user_username_end_tag'] = null;
						if (defined("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN")) {
							$data['user_username_open_tag'] = constant("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN");
						}
						if (defined("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN")) {
							$data['user_username_min'] = constant("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN");
						}
						if (defined("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX")) {
							$data['user_username_max'] = constant("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX");
						}
						if (defined("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END")) {
							$data['user_username_end_tag'] = constant("USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END");
						}
						$id = $this->getUserManagement()->createUser($data);
						if ($id !== false && $id > 0) {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_success_msg_user_' . $this->module));

							$cryptID = new Encrypt($id);
							$cryptID = $cryptID->__toString();
							$redirect = $this->url()->fromRoute($this->getMatchedRouteName(), array(
											'action' => 'Preview',
											'crypt' => $cryptID,
									));
						} else {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_error_msg_user_' . $this->module));

							$redirect = $this->url()->fromRoute($this->getMatchedRouteName(), array(
											'action' => 'Index'
									));
						}
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
					->setContent(
							Json::encode(
									array(
											'id' => $id,
											'input' => $input,
											'subaction' => $subaction,
											'relation' => $relation,
											'messages' => $messages,
											'messagescount' => $messagescount,
											'valid' => $valid,
											'redirect' => $redirect,
									)));
			return $InputForm->getResponse();
		} else {
			return array(
					'inputmodel' => $this->ViewModel('index')
			);
		}
	}

	public function PreviewAction() {
		$id = new Decrypt((string) $this->params()->fromRoute('crypt', null));
		$id = $id->__toString();
		if (!$id) {
			throw new \Exception('Could not find the User ID ( $id )');
		}
		$this->PreviewData($id);

		return array(
				'previewmodel' => $this->ViewModel('preview')
		);
	}

	private function ViewModel($action) {
		$ViewModel = new ViewModel();
		$ViewModel->setTerminal(true);
		if ($action === 'preview') {
			$ViewModel->setTemplate('share/form/preview');
			$ViewModel->setVariables(array(
							'view' => $this->PreviewData()
					));
		} elseif ($action === 'index') {
			$ViewModel->setTemplate('share/form/input');
			$ViewModel->setVariables(array(
							'form' => $this->InputForm(),
					));
		}
		return $this->getServiceLocator()->get('viewrenderer')->render($ViewModel);
	}

	private function PreviewData($id = null) {
		if (!is_object($this->viewdata) && !empty($id)) {
			if ($this->getUserManagement()->verifyID($id, $this->rankgroup, null)) {
				$data = $this->getUserManagement()->getData($id);
				if (count($data) > 0) {
					/*
					$timestampNow = new \DateTime();
					$timestampCreated = new \DateTime($data['user_access_created_date']);
					$timestampDiff = $timestampNow->format('YmdHis') - $timestampCreated->format('YmdHis');
					if ($timestampDiff > 3600) {
					    $this->redirect()->toRoute($this->getMatchedRouteName(), array(
					                    'action' => 'Index'
					            ));
					}
					 */
				}
				$options = array(
						'servicelocator' => $this->getServiceLocator(),
						'variable' => $data,
				);
				$this->viewdata = new View($options);
			} else {
				throw new \Exception('Could not find the User ID ( $id )');
			}
		}
		return $this->viewdata;
	}

	private function InputForm() {
		if (!is_object($this->inputform)) {
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
					'rank' => $this->rankgroup,
			);
			$this->inputform = new UserRegisterForm($options);
		}
		return $this->inputform;
	}
}

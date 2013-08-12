<?php
namespace Management\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserUpdateForm;
use Techfever\Functions\Crypt\Decode as Decrypt;

class SecurityActionController extends AbstractActionController {

	protected $rankgroup = 99999;

	protected $type = 'management';

	protected $module = 'security';

	protected $inputform = null;

	protected $searchform = null;

	protected $search_username = null;

	public function IndexAction() {
		return $this->redirect()->toRoute($this->getMatchedRouteName(), array(
						'action' => 'Update'
				));
	}

	public function UpdateAction() {
		$this->getTemplate()->addCSS("ui-lightness/jquery-ui.css", "jquery");
		$this->getTemplate()->addCSS("vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css");
		$this->getTemplate()->addCSS("vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/steps.css");

		$cryptId = (string) $this->params()->fromRoute('crypt', null);
		if (!empty($cryptId) && strlen($cryptId) > 0) {
			$userID = new Decrypt($cryptId);
			$userID = $userID->__toString();
			$this->search_username = $this->getUserManagement()->getUsername($userID);
		}

		$this->getTemplate()
				->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.search.js",
						array(
								'updateformid' => $this->getMatchedRouteName() . '/Update',
								'searchformid' => $this->getMatchedRouteName() . '/Search',
								'searchformuri' => $this->getMatchedRouteName(),
								'searchformusername' => $this->search_username
						));
		$this->getTemplate()->addJavascript("vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/steps.js");

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
						$id = new Decrypt($InputForm->getPost('user_modify'));
						$id = $id->__toString();
						$data = $InputForm->getData();
						if ($this->getUserManagement()->verifyID($id, $this->rankgroup, null) && $this->getUserManagement()->updateSecurity($id, $data)) {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_success_msg_user_update_' . $this->module));
						} else {
							$this->FlashMessenger()->addMessage($this->getTranslate('text_error_msg_user_update_' . $this->module));
						}
						$redirect = $this->url()->fromRoute($this->getMatchedRouteName(), array(
										'action' => 'Update'
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
					'searchmodel' => $this->ViewModel('search')
			);
		}
	}

	public function SearchAction() {
		$valid = false;
		$id = 0;
		$username = null;
		$messages = array();
		$InputModel = null;

		$SearchForm = $this->SearchForm();
		if ($SearchForm->isXmlHttpRequest()) {
			$username = strtoupper($SearchForm->getPost('search_username', null));
			$id = $this->getUserManagement()->getID($username, $this->rankgroup, null);
			if ($id > 0) {
				$valid = true;

				$this->InputForm($id);
				$InputModel = $this->ViewModel('update');
			} else {
				$messages = $this->getTranslate('text_error_' . $this->type . '_username_not_exist');
				$messages = sprintf($messages, $username);
			}
		} else {
			return $this->redirect()->toRoute($this->getMatchedRouteName(), array(
							'action' => 'Update'
					));
		}
		$SearchForm->getResponse()
				->setContent(
						Json::encode(
								array(
										'inputmodel' => $InputModel,
										'messages' => $messages,
										'id' => $id,
										'username' => $username,
										'valid' => $valid,
										'js' => '$(this).Steps({
							formname : "' . str_replace('/', '_', ($this->getMatchedRouteName() . '/' . 'Update')) . '",
							formuri : "' . $this->url()->fromRoute($this->getMatchedRouteName(), array(
																'action' => 'Update'
														)) . '",
							dialogtitle : "' . $this->getTranslate("text_dialog_user_update_" . $this->module . "_title") . '",
							dialogcontent : "' . $this->getTranslate("text_dialog_user_update_" . $this->module . "_content") . '",
						})',
								)));

		return $SearchForm->getResponse();
	}

	private function ViewModel($action) {
		$ViewModel = new ViewModel();
		$ViewModel->setTerminal(true);
		if ($action === 'search') {
			$ViewModel->setTemplate('share/user/searchupdate');
			$ViewModel->setVariables(array(
							'searchform' => $this->SearchForm()
					));
		} elseif ($action === 'update') {
			$ViewModel->setTemplate('share/form/update');
			$ViewModel->setVariables(array(
							'form' => $this->InputForm()
					));
		}
		return $this->getServiceLocator()->get('viewrenderer')->render($ViewModel);
	}

	private function SearchForm() {
		if (!is_object($this->searchform)) {
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
					'rank' => $this->rankgroup,
					'action' => 'Search',
			);
			$this->searchform = new UserUpdateForm($options);
		}
		return $this->searchform;
	}

	private function InputForm($id = null) {
		if (!is_object($this->inputform) || !empty($id)) {
			$options = array(
					'servicelocator' => $this->getServiceLocator(),
					'rank' => $this->rankgroup,
					'action' => 'Update',
			);
			if ($id > 0) {
				if ($this->getUserManagement()->verifyID($id, $this->rankgroup, null)) {
					$data = $this->getUserManagement()->getData($id);
					if (count($data) > 0) {
						$options['value'] = $data;
					}
				}
			}
			$this->inputform = new UserUpdateForm($options);
		}
		return $this->inputform;
	}
}

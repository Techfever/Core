<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserRegisterForm;
use Techfever\View\View as UserRegisterView;

class RegisterActionController extends AbstractActionController {
	protected $rankgroup = 99999;
	protected $type = 'admin';
	protected $module = 'register';
	protected $inputform = null;
	protected $viewdata = null;
	public function IndexAction() {
		$this->addCSS ( "ui-lightness/jquery-ui.css", "jquery" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/steps.css" );
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/steps.js" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.address.js", array (
				'addressformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.bank.js", array (
				'bankformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.register.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_user_register_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_user_register_content" ) 
		) );
		
		$InputForm = $this->InputForm ();
		if ($this->isXmlHttpRequest ()) {
			$id = 0;
			$action = strtolower ( $this->getPost ( 'submit', 'preview' ) );
			$subaction = null;
			$js = null;
			$valid = false;
			$redirect = null;
			$flashmessages = null;
			if ($InputForm->isPost () && $InputForm->isValid () && $action == 'submit') {
				$valid = true;
				$data = $InputForm->getData ();
				$data ['user_rank_group_id'] = $this->rankgroup;
				$data ['user_username_open_tag'] = null;
				$data ['user_username_min'] = null;
				$data ['user_username_max'] = null;
				$data ['user_username_end_tag'] = null;
				if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN" )) {
					$data ['user_username_open_tag'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN" );
				}
				if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN" )) {
					$data ['user_username_min'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN" );
				}
				if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX" )) {
					$data ['user_username_max'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX" );
				}
				if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END" )) {
					$data ['user_username_end_tag'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END" );
				}
				$id = $this->getUserManagement ()->createUser ( $data );
				if ($id !== false && $id > 0) {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_success_msg_user_' . $this->module ) );
					$cryptID = $this->Encrypt ( $id );
					$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Preview',
							'crypt' => $cryptID 
					) );
				} else {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_error_msg_user_' . $this->module ) );
					
					$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Index' 
					) );
				}
			}
			$Input = $InputForm->getPost ( 'Input', null );
			$InputForm->getResponse ()->setContent ( Json::encode ( array (
					'id' => $id,
					'subaction' => $subaction,
					'valid' => $valid,
					'redirect' => $redirect,
					'flashmessages' => $flashmessages,
					'js' => $js,
					'input' => $Input,
					'relation' => $InputForm->getValidatorRelation ( $Input ),
					'messages' => $InputForm->getMessages (),
					'messagescount' => $InputForm->getMessagesTotal () 
			) ) );
			return $InputForm->getResponse ();
		} else {
			return array (
					'inputmodel' => $this->ViewModel ( 'index' ) 
			);
		}
	}
	public function PreviewAction() {
		$id = $this->Decrypt ( ( string ) $this->params ()->fromRoute ( 'crypt', null ) );
		if (! $id) {
			throw new \Exception ( 'Could not find the User ID ( $id )' );
		}
		$this->PreviewData ( $id );
		
		return array (
				'previewmodel' => $this->ViewModel ( 'preview' ) 
		);
	}
	private function ViewModel($action) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		if ($action === 'preview') {
			$ViewModel->setTemplate ( 'share/form/preview' );
			$ViewModel->setVariables ( array (
					'view' => $this->PreviewData () 
			) );
		} elseif ($action === 'index') {
			$ViewModel->setTemplate ( 'share/form/input' );
			$ViewModel->setVariables ( array (
					'form' => $this->InputForm () 
			) );
		}
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	private function PreviewData($id = null) {
		if (! is_object ( $this->viewdata ) && ! empty ( $id )) {
			if ($this->getUserManagement ()->verifyID ( $id, $this->rankgroup, null )) {
				$data = $this->getUserManagement ()->getData ( $id );
				if (count ( $data ) > 0) {
					$timestampNow = new \DateTime ();
					$timestampCreated = new \DateTime ( $data ['user_access_created_date'] );
					$timestampDiff = $timestampNow->format ( 'YmdHis' ) - $timestampCreated->format ( 'YmdHis' );
					if ($timestampDiff > 3600) {
						$this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
								'action' => 'Index' 
						) );
					}
				}
				$options = array (
						'servicelocator' => $this->getServiceLocator (),
						'variable' => $data 
				);
				$this->viewdata = new UserRegisterView ( $options );
			} else {
				throw new \Exception ( 'Could not find the User ID ( $id )' );
			}
		}
		return $this->viewdata;
	}
	private function InputForm() {
		if (! is_object ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $this->rankgroup 
			);
			$this->inputform = new UserRegisterForm ( $options );
		}
		return $this->inputform;
	}
}

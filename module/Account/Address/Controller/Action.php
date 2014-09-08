<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserUpdateForm;

class AddressActionController extends AbstractActionController {
	protected $type = 'account';
	protected $module = 'address';
	protected $inputform = null;
	public function IndexAction() {
		$this->addCSS ( "ui-lightness/jquery-ui.css", "jquery" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.address.js", array (
				'addressformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/account.update.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'updateformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'updatecallback' => '$(this).Address()' 
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
				$id = $this->getUserID ();
				$profile = $this->getUserManagement ()->getProfileID ( $id );
				if ($this->getUserManagement ()->verifyID ( $id ) && $this->getUserAddress ()->updateUserAddress ( $profile, $data )) {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_success_msg_user_update_' . $this->module ) );
				} else {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_error_msg_user_update_' . $this->module ) );
				}
				$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) );
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
					'inputmodel' => $this->ViewModel () 
			);
		}
	}
	private function ViewModel() {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		$ViewModel->setTemplate ( 'share/account/update' );
		$ViewModel->setVariables ( array (
				'form' => $this->InputForm () 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	private function InputForm() {
		if (! is_object ( $this->inputform )) {
			$id = $this->getUserAccess ()->getID ();
			$rank_group = $this->getUserAccess ()->getRankGroupID ();
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $rank_group,
					'action' => 'Index' 
			);
			if ($this->getUserManagement ()->verifyID ( $id, $rank_group )) {
				$data = $this->getUserManagement ()->getData ( $id, $rank_group );
				if (count ( $data ) > 0) {
					$options ['datavalues'] = $data;
				}
			}
			$this->inputform = new UserUpdateForm ( $options );
		}
		return $this->inputform;
	}
}

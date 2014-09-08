<?php

namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Json\Json;
use Techfever\Wallet\Form\Defined as WalletForm;

class LoginActionController extends AbstractActionController {
	protected $type = 'wallet';
	protected $module = 'login';
	protected $inputform = null;
	public function IndexAction() {
		if ($this->getUserAccess ()->isLoginWallet ()) {
			$this->getSnapshot ()->redirect ();
		}
		
		$this->addCSS ( "ui-lightness/jquery-ui.css", "jquery" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.login.js", array (
				'loginformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'loginformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ) 
		) );
		
		$InputForm = $this->InputForm ();
		if ($this->isXmlHttpRequest ()) {
			$id = 0;
			$subaction = null;
			$js = null;
			$valid = false;
			$redirect = null;
			$flashmessages = null;
			if ($InputForm->isPost () && $InputForm->isValid ()) {
				$js = '$("form[id=' . $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) . '] table[class=form] button[id=login]").show()';
				$submit = strtolower ( $InputForm->getPost ( 'submit', null ) );
				$username = $InputForm->getPost ( 'wallet_username', null );
				$password = $InputForm->getPost ( 'wallet_password', null );
				if ($submit == 'submit' && $this->getUserAccess ()->verifySecurity ( $username, $password )) {
					$valid = true;
					$id = $this->getUserManagement ()->getID ( $username );
					$this->getUserAccess ()->setLoginWallet ( $id );
					$this->getSnapshot ()->redirect ();
				} else {
					$flashmessages = '<div class="ui-state-error ui-corner-all"><span><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>' . $this->getTranslate ( 'text_error_msg_wallet_' . $this->module ) . '</span></div>';
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
					'form' => $InputForm 
			);
		}
	}
	private function InputForm() {
		if (! is_object ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index' 
			);
			$this->inputform = new WalletForm ( $options );
		}
		return $this->inputform;
	}
}

<?php

namespace Wallet\Login\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Techfever\Wallet\Form\Defined as WalletForm;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'wallet';
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'login';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		if ($this->getUserAccess ()->isLoginWallet ()) {
			$this->getSnapshot ()->redirect ();
		}
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.login.js", array (
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
			$captcha = null;
			if ($InputForm->isPost () && $InputForm->isValid ()) {
				$js = '$("form[id=' . $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) . '] div[class=button] button[id=login]").show()';
				$submit = strtolower ( $InputForm->getPost ( 'submit', null ) );
				$username = $this->getUsername ();
				$password = $InputForm->getPost ( 'account_password', null );
				if ($submit == 'submit') {
					if ($this->getUserAccess ()->verifySecurity ( $username, $password )) {
						$valid = true;
						$id = $this->getUserManagement ()->getID ( $username );
						$this->getUserAccess ()->setLoginWallet ( $id );
						$redirect = $this->getSnapshot ()->get ();
					} else {
						$captcha = $InputForm->getCaptchaRefresh ( 'account_captcha' );
						$flashmessages = '<div class="ui-state-error ui-corner-all"><span><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>' . $this->getTranslate ( 'text_error_msg_wallet_' . $this->module ) . '</span></div>';
					}
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
					'messagescount' => $InputForm->getMessagesTotal (),
					'captcha' => $captcha 
			) ) );
			return $InputForm->getResponse ();
		} else {
			return array (
					'form' => $InputForm 
			);
		}
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		if (! is_object ( $this->inputform ) && empty ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index' 
			);
			$this->inputform = new WalletForm ( $options );
		}
		return $this->inputform;
	}
}

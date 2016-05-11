<?php

namespace Account\Login\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\User\Form\Defined as UserLoginForm;

class ActionController extends AbstractActionController {
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		if ($this->isXmlHttpRequest ()) {
			$InputForm = $this->InputForm ();
			if ($InputForm->isPost ()) {
				$this->setInput ( $InputForm->getPost ( 'input', null ) );
				$this->setPost ( true );
				if ($InputForm->isValid ()) {
					$this->setValid ( true );
					if ($InputForm->isSubmit ()) {
						$this->setSubmit ( true );
						
						$username = $InputForm->getPost ( 'account_username', null );
						$password = $InputForm->getPost ( 'account_password', null );
						if ($this->getUserAccess ()->verifyPassword ( $username, $password )) {
							$this->setVerified ( true );
							$this->setID ( $this->getUserManagement ()->getID ( $username ) );
							
							$this->getUserAccess ()->setLogin ( $this->getID () );
							$this->getUserAccess ()->setLogoutWallet ();
						} else {
							$captcha = $InputForm->getCaptchaRefresh ( 'account_captcha' );
						}
					}
				} else {
					$this->setValidatorRelation ( $InputForm->getValidatorRelation ( $this->getInput () ) );
					$this->setMessages ( $InputForm->getMessages () );
					$this->setMessagesTotal ( $InputForm->getMessagesTotal () );
					
					$account_captcha = $InputForm->getPost ( 'account_captcha', null );
					if (array_key_exists ( 'input', $account_captcha ) && strlen ( $account_captcha ['input'] ) >= CAPTCHA_LENGTH && $this->getInput () == "account_captcha") {
						$this->setCaptcha ( $InputForm->getCaptchaRefresh ( 'account_captcha' ) );
					}
				}
			}
			$this->setContent ( $this->ViewModal ( array (
					'form' => $InputForm 
			), 'share/form/input' ) );
			return $this->renderModal ();
		} else {
			$this->redirectHome ();
		}
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		if (! $this->inputform instanceof \Zend\Form\FormInterface) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index' 
			);
			$this->inputform = new UserLoginForm ( $options );
		}
		return $this->inputform;
	}
	
	/**
	 * CSS
	 *
	 * @return Array
	 */
	protected function CSS() {
		return array (
				"Theme/" . SYSTEM_THEME_LOAD . "/CSS/loginmodal.css" 
		);
	}
	
	/**
	 * Done Callback
	 *
	 * @return JS
	 */
	protected function doneCallback() {
		return "
		$(this).syncSystem();
		";
	}
}

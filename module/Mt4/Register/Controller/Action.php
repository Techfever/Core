<?php

namespace Mt4\Register\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Mt4\Form\Defined as MT4RegisterForm;
use Techfever\View\View as MT4RegisterView;
use Techfever\Mt4\Management as MT4Management;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'mt4';
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'register';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = null;
	/**
	 *
	 * @var View Data
	 *     
	 */
	protected $viewdata = null;
	/**
	 *
	 * @var MT4 Management
	 *     
	 */
	protected $mt4Management = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css" );
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/steps.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_user_mt4_register_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_user_mt4_register_content" ) 
		) );
		
		$user_id = $this->getUserID ();
		if ($this->getMT4Management ()->verifyUser ( $user_id )) {
			$id = $this->getMT4Management ()->getID ( $user_id );
			if ($id !== false && $id > 0) {
				$cryptID = $this->Encrypt ( $id );
				$this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Preview',
						'crypt' => $cryptID 
				) );
			}
		}
		
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
				$data ['user_access_id'] = $user_id;
				$id = $this->getMT4Management ()->createMT4User ( $data );
				if ($id !== false && $id > 0) {
					/*
					 * $mail = new Mail\Message(); $mail->setBody('Thank you for your registration with IFX Resources. To complete the registration process, please proceed with the payment and email to us the bank in proof. Your Name: '.$data ['user_mt4_fullname'].' Your Email: '.$data ['user_mt4_email_address'].' Identification: '.$data ['user_mt4_nric_passport'].' Bank Name: '.$data ['user_mt4_bank_name'].' Bank Holder Name: '.$data ['user_mt4_bank_holder_name'].' Bank Holder Account:'.$data ['user_mt4_bank_holder_no'].' Agent: '.$data ['user_mt4_agent']. ''); $mail->setFrom('admin@ifxmoney.com', 'IFX Registration'); $mail->addTo('accounts@rtcm-asia.com', 'RTCM Asia'); $mail->addBcc('admin@ifxmoney.com', 'IFX Registration'); $mail->addBcc('ifxmoneygold@gmail.com', 'IFX Registration'); $mail->setSubject('IFX Registration'); $transport = new Mail\Transport\Sendmail(); $transport->send($mail);
					 */
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
					'form' => $InputForm 
			);
		}
	}
	
	/**
	 * Preview Action
	 *
	 * @return ViewModel
	 */
	public function PreviewAction() {
		$id = $this->Decrypt ( ( string ) $this->params ()->fromRoute ( 'crypt', null ) );
		$PreviewData = $this->PreviewData ( $id );
		if (empty ( $PreviewData )) {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Index' 
			) );
		}
		return array (
				'view' => $PreviewData 
		);
	}
	
	/**
	 * Preview Data
	 *
	 * @return Data
	 */
	protected function PreviewData($id = null) {
		if (! is_object ( $this->viewdata ) && empty ( $this->viewdata ) && ! empty ( $id )) {
			if ($this->getMT4Management ()->verifyID ( $id )) {
				$data = $this->getMT4Management ()->getData ( $id );
				if (count ( $data ) > 0) {
					$options = array (
							'servicelocator' => $this->getServiceLocator (),
							'variable' => $data 
					);
					$this->viewdata = new MT4RegisterView ( $options );
				}
			}
		}
		return $this->viewdata;
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		if (! is_object ( $this->inputform ) && empty ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator () 
			);
			$this->inputform = new MT4RegisterForm ( $options );
		}
		return $this->inputform;
	}
	
	/**
	 * MT4 Management Obj
	 *
	 * @return Object
	 */
	private function getMT4Management() {
		if (! isset ( $this->mt4Management )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator () 
			);
			$this->mt4Management = new MT4Management ( $options );
		}
		return $this->mt4Management;
	}
}

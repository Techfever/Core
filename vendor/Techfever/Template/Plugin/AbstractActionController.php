<?php

namespace Techfever\Template\Plugin;

use Zend\Mvc\Controller\AbstractActionController as BAbstractActionController;
use Zend\Json\Json;
use Techfever\View\View;
use Techfever\View\ViewInterface;
use Techfever\Form\Form;
use Zend\Form\FormInterface;

/**
 * Basic action controller
 */
abstract class AbstractActionController extends BAbstractActionController {
	/*
	 * Language text_' . $module . '_title text_submit_' . $module . '_title text_submit_' . $module . '_content text_cancel_' . $module . '_title text_cancel_' . $module . '_content text_success_msg_' . $module . '_title text_success_msg_' . $module . '_content text_error_msg_' . $module . '_title text_error_msg_' .$module . '_content
	 */
	
	/**
	 *
	 * @var Title
	 */
	protected $title = "";
	
	/**
	 *
	 * @var Content
	 */
	protected $content = "";
	
	/**
	 *
	 * @var Content
	 */
	protected $id = 0;
	
	/**
	 *
	 * @var Content
	 */
	protected $post = false;
	
	/**
	 *
	 * @var Content
	 */
	protected $valid = false;
	
	/**
	 *
	 * @var Content
	 */
	protected $submit = false;
	
	/**
	 *
	 * @var Content
	 */
	protected $verified = false;
	
	/**
	 *
	 * @var Content
	 */
	protected $search = false;
	
	/**
	 *
	 * @var Content
	 */
	protected $redirect = "";
	
	/**
	 *
	 * @var Captcha
	 */
	protected $captcha = "";
	
	/**
	 *
	 * @var Input
	 */
	protected $input = "";
	
	/**
	 *
	 * @var Input Form
	 */
	protected $inputform = "";
	
	/**
	 *
	 * @var Search Form
	 */
	protected $searchform = "";
	
	/**
	 *
	 * @return Form Validator Relation
	 */
	protected $validatorrelation = "";
	
	/**
	 *
	 * @return Form Message
	 */
	protected $messages = array ();
	
	/**
	 *
	 * @return Form Message Total
	 */
	protected $messagestotal = 0;
	
	/**
	 *
	 * @var Preview Data
	 */
	protected $previewdata = "";
	
	/**
	 *
	 * @var Javascript
	 */
	protected $javascript = "";
	
	/**
	 *
	 * @var CSS
	 */
	protected $css = "";
	
	/**
	 *
	 * @var Init Callback
	 */
	protected $initcallback = "";
	
	/**
	 *
	 * @var Done Callback
	 */
	protected $donecallback = "";
	
	/**
	 *
	 * @var Fail Callback
	 */
	protected $failcallback = "";
	
	/**
	 *
	 * @var Search Callback
	 */
	protected $searchcallback = "";
	
	/**
	 *
	 * @var Valid Callback
	 */
	protected $validcallback = "";
	
	/**
	 * Render System Desktop Modal
	 *
	 * @return JSON
	 */
	protected function renderModal() {
		$Response = $this->getResponse ();
		
		$options = array (
				'id' => $this->getID (),
				'post' => $this->getPost (),
				'valid' => $this->getValid (),
				'submit' => $this->getSubmit (),
				'verified' => $this->getVerified (),
				'search' => $this->getSearch (),
				'redirect' => $this->getRedirect (),
				'input' => $this->getInput (),
				
				'relation' => $this->getValidatorRelation (),
				'messages' => $this->getMessages (),
				'messagescount' => $this->getMessagesTotal (),
				
				'captcha' => $this->getCaptcha (),
				'title' => $this->getTitle (),
				'content' => $this->getContent (),
				'javascript' => $this->Javascript (),
				'css' => $this->CSS (),
				'callback' => array (
						'init' => $this->initCallback (),
						'done' => $this->doneCallback (),
						'fail' => $this->failCallback (),
						'search' => $this->searchCallback (),
						'valid' => $this->validCallback () 
				),
				'dialog' => $this->getDialogID (),
				'form' => $this->getFormID (),
				'is' => array (
						'login' => array (
								'account' => $this->isLogin (),
								'wallet' => $this->isLoginWallet () 
						),
						'admin' => $this->isAdminUser () 
				) 
		);
		
		$Response->setContent ( Json::encode ( $options ) );
		return $Response;
	}
	
	/**
	 * Redirect
	 *
	 * @return JSON
	 */
	protected function redirectHome() {
		return $this->redirect ()->toRoute ( 'Index', array (
				'action' => 'Index' 
		) );
	}
	
	/**
	 * Get Dialog ID
	 *
	 * @return String
	 */
	protected function getDialogID() {
		return strtolower ( $this->convertToDash ( $this->getMatchedRouteName () . '/' . $this->getControllerAction (), '/' ) );
	}
	
	/**
	 * Get Form ID
	 *
	 * @return String
	 */
	protected function getFormID() {
		return $this->convertToUnderscore ( $this->getMatchedRouteName () . '/' . $this->getControllerAction (), '/' );
	}
	
	/**
	 * Get Title
	 *
	 * @return String
	 */
	protected function getTitle() {
		if (empty ( $this->title ) || strlen ( $this->title ) <= 0) {
			$this->title = $this->getTranslate ( 'text_' . $this->getModuleID () . '_title' );
		}
		return $this->title;
	}
	
	/**
	 * Get Content
	 *
	 * @return String
	 */
	protected function getContent() {
		return $this->content;
	}
	
	/**
	 * Get ID
	 *
	 * @return Int/String
	 */
	protected function getID() {
		return $this->id;
	}
	
	/**
	 * Get Post Status
	 *
	 * @return Boolean
	 */
	protected function getPost() {
		return $this->post;
	}
	
	/**
	 * Get Valid Status
	 *
	 * @return Boolean
	 */
	protected function getValid() {
		return $this->valid;
	}
	
	/**
	 * Get Submit Status
	 *
	 * @return Boolean
	 */
	protected function getSubmit() {
		return $this->submit;
	}
	
	/**
	 * Get Verified Status
	 *
	 * @return Boolean
	 */
	protected function getVerified() {
		return $this->verified;
	}
	
	/**
	 * Get Search Status
	 *
	 * @return Boolean
	 */
	protected function getSearch() {
		return $this->search;
	}
	
	/**
	 * Get Redirect
	 *
	 * @return String
	 */
	protected function getRedirect() {
		return $this->redirect;
	}
	
	/**
	 * Get Captcha
	 *
	 * @return Captcha
	 */
	protected function getCaptcha() {
		return $this->captcha;
	}
	
	/**
	 * Get Input
	 *
	 * @return String
	 */
	protected function getInput() {
		return $this->input;
	}
	
	/**
	 * Set Title
	 *
	 * @return String
	 */
	protected function setTitle($title = null) {
		$this->title = $title;
	}
	
	/**
	 * Set Content
	 *
	 * @return String
	 */
	protected function setContent($content = null) {
		$this->content = $content;
	}
	
	/**
	 * Set ID
	 *
	 * @return Int/String
	 */
	protected function setID($value = 0) {
		$this->id = $value;
	}
	
	/**
	 * Set Post Status
	 *
	 * @return Boolean
	 */
	protected function setPost($status = false) {
		$this->post = $status;
	}
	
	/**
	 * Set Valid Status
	 *
	 * @return Boolean
	 */
	protected function setValid($status = false) {
		$this->valid = $status;
	}
	
	/**
	 * Set Submit Status
	 *
	 * @return Boolean
	 */
	protected function setSubmit($status = false) {
		$this->submit = $status;
	}
	
	/**
	 * Set Verified Status
	 *
	 * @return Boolean
	 */
	protected function setVerified($status = false) {
		$this->verified = $status;
	}
	
	/**
	 * Set Search Status
	 *
	 * @return Boolean
	 */
	protected function setSearch($status = false) {
		$this->search = $status;
	}
	
	/**
	 * Set Redirect
	 *
	 * @return String
	 */
	protected function setRedirect($redirect = null) {
		$this->redirect = $redirect;
	}
	
	/**
	 * Set Captcha
	 *
	 * @return Captcha
	 */
	protected function setCaptcha($captcha = null) {
		$this->captcha = $captcha;
	}
	
	/**
	 * Set Input
	 *
	 * @return String
	 */
	protected function setInput($input = null) {
		$this->input = $input;
	}
	
	/**
	 * Init Callback
	 *
	 * @return JS
	 */
	protected function setInitCallback($js = null) {
		$this->initcallback = $js;
	}
	
	/**
	 * Done Callback
	 *
	 * @return JS
	 */
	protected function setDoneCallback($js = null) {
		$this->donecallback = $js;
	}
	
	/**
	 * Fail Callback
	 *
	 * @return JS
	 */
	protected function setFailCallback($js = null) {
		$this->failcallback = $js;
	}
	
	/**
	 * Search Callback
	 *
	 * @return JS
	 */
	protected function setSearchCallback($js = null) {
		$this->searchcallback = $js;
	}
	
	/**
	 * Valid Callback
	 *
	 * @return JS
	 */
	protected function setValidCallback($js = null) {
		$this->validcallback = $js;
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		if (! $this->inputform instanceof FormInterface) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index' 
			);
			$this->inputform = new Form ( $options );
		}
		return $this->inputform;
	}
	
	/**
	 * Form Search
	 *
	 * @return Form
	 */
	protected function SearchForm() {
		if (! $this->searchform instanceof FormInterface) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Search' 
			);
			$this->searchform = new Form ( $options );
		}
		return $this->searchform;
	}
	
	/**
	 * Form Validator Relation
	 *
	 * @return String
	 */
	protected function getValidatorRelation() {
		return $this->validatorrelation;
	}
	
	/**
	 * Form Message
	 *
	 * @return Array
	 */
	protected function getMessages() {
		return $this->messages;
	}
	
	/**
	 * Form Message Total
	 *
	 * @return Number
	 */
	protected function getMessagesTotal() {
		return $this->messagestotal;
	}
	
	/**
	 * Form Set Validator Relation
	 *
	 * @return String
	 */
	protected function setValidatorRelation($relation = null) {
		$this->validatorrelation = $relation;
	}
	
	/**
	 * Form Set Message
	 *
	 * @return Array
	 */
	protected function setMessages($message = array()) {
		return $this->messages = $message;
	}
	
	/**
	 * Form Set Message Total
	 *
	 * @return Number
	 */
	protected function setMessagesTotal($messagetotal = 0) {
		return $this->messagestotal = $messagetotal;
	}
	
	/**
	 * Preview Data
	 *
	 * @return Form
	 */
	protected function PreviewData() {
		if (! $this->previewdata instanceof ViewInterface) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'variable' => array () 
			);
			$this->previewdata = new View ( $options );
		}
		return $this->previewdata;
	}
	
	/**
	 * Javascript
	 *
	 * @return Array
	 */
	protected function Javascript() {
		return $this->javascript;
	}
	
	/**
	 * CSS
	 *
	 * @return Array
	 */
	protected function CSS() {
		return $this->css;
	}
	
	/**
	 * Init Callback
	 *
	 * @return JS
	 */
	protected function initCallback() {
		if (! empty ( $this->initcallback ) || strlen ( $this->initcallback ) < 0) {
			return "";
		} else {
			return $this->initcallback;
		}
	}
	
	/**
	 * Done Callback
	 *
	 * @return JS
	 */
	protected function doneCallback() {
		if (! empty ( $this->donecallback ) || strlen ( $this->donecallback ) < 0) {
			return "
					$(\".ui-dialog-" . $this->getDialogID () . "-modal\").modal({
						dialogclass: \"ui-dialog-" . $this->getDialogID () . "-done-modal\",
						id: \"ui-dialog-" . $this->getDialogID () . "-done-content\",
						height : 170,
						width : 300,
						title : \"" . $this->getTranslate ( 'text_success_msg_' . $this->getModuleID () . '_title' ) . "\",
						content : \"" . $this->getTranslate ( 'text_success_msg_' . $this->getModuleID () . '_content' ) . "\",
						buttons : {
							\"" . $this->getTranslate ( 'text_ok' ) . "\" : function() {
								$(this).dialog(\"close\");
							},
						},
					});		
					";
		} else {
			return $this->donecallback;
		}
	}
	
	/**
	 * Fail Callback
	 *
	 * @return JS
	 */
	protected function failCallback() {
		if (! empty ( $this->failcallback ) || strlen ( $this->failcallback ) < 0) {
			return "
					$(\".ui-dialog-" . $this->getDialogID () . "-modal\").modal({
						dialogclass: \"ui-dialog-" . $this->getDialogID () . "-fail-modal\",
						id: \"ui-dialog-" . $this->getDialogID () . "-fail-content\",
						height : 170,
						width : 300,
						title : \"" . $this->getTranslate ( 'text_error_msg_' . $this->getModuleID () . '_title' ) . "\",
						content : \"" . $this->getTranslate ( 'text_error_msg_' . $this->getModuleID () . '_content' ) . "\",
						buttons : {
							\"" . $this->getTranslate ( 'text_ok' ) . "\" : function() {
								$(this).dialog(\"destroy\");
							},
						},
					});		
					";
		} else {
			return $this->failcallback;
		}
	}
	
	/**
	 * Search Callback
	 *
	 * @return JS
	 */
	protected function searchCallback() {
		if (! empty ( $this->searchcallback ) || strlen ( $this->searchcallback ) < 0) {
			return "";
		} else {
			return $this->searchcallback;
		}
	}
	
	/**
	 * Valid Callback
	 *
	 * @return JS
	 */
	protected function validCallback() {
		if (! empty ( $this->validcallback ) || strlen ( $this->validcallback ) < 0) {
			return "";
		} else {
			return $this->validcallback;
		}
	}
}

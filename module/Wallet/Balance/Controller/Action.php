<?php

namespace Wallet\Balance\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
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
	protected $module = 'balance';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = array ();
	/**
	 *
	 * @var Username
	 *
	 */
	protected $search_username = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		if (! $this->getUserAccess ()->isLoginWallet ()) {
			return $this->redirect ()->toRoute ( 'Wallet/Login', array (
					'action' => 'Index' 
			) );
		}
		
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.wallet.js", array (
				'walletformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'walletformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'walletformdialogtitle' => $this->getTranslate ( "text_dialog_wallet_update_" . $this->module . "_title" ),
				'walletformdialogcontent' => $this->getTranslate ( "text_dialog_wallet_update_" . $this->module . "_content" ) 
		) );
		
		$formModel = '';
		$userID = $this->getUserIDAction ();
		if ($this->isAdminUser ()) {
			$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
			if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
				$userID = $this->Decrypt ( $cryptId );
				$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
			}
			
			$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css" );
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
					'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
					'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
					'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Search' 
					) ),
					'searchformusername' => $this->search_username 
			) );
			
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/steps.js", array (
					'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
					'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Index' 
					) ) 
			) );
			$formModel = $this->ViewModel ( 'Search' );
		} else {
			$formModel = $this->ViewModel ( 'User', $userID );
		}
		
		if ($this->isXmlHttpRequest ()) {
			$encoded_id = $this->getPost ( 'modify_value' );
			if (! empty ( $encoded_id ) && strlen ( $encoded_id ) > 0) {
				$userID = $this->Decrypt ( $encoded_id );
			}
			if (! $this->isAdminUser ()) {
				$userID = $this->getUserIDAction ();
			}
			$subaction = null;
			$js = null;
			$valid = false;
			$redirect = null;
			$flashmessages = null;
			$this->getResponse ()->setContent ( Json::encode ( array (
					'id' => $userID,
					'subaction' => $subaction,
					'valid' => $valid,
					'redirect' => $redirect,
					'flashmessages' => $flashmessages,
					'js' => $js,
					'input' => "",
					'relation' => "",
					'messages' => "",
					'messagescount' => 0 
			) ) );
			return $this->getResponse ();
		} else {
			return array (
					'form' => $formModel,
					'isAdminUser' => $this->isAdminUser () 
			);
		}
	}
	
	/**
	 * Search Action
	 *
	 * @return ViewModel
	 */
	public function SearchAction() {
		$valid = false;
		$id = 0;
		$username = null;
		$messages = array ();
		$redirect = null;
		$InputForm = $this->ViewModel ( 'Search' );
		if ($InputForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $InputForm->getPost ( 'search_username', null ) );
			$id = $this->getUserManagement ()->getID ( $username );
			if ($id > 0) {
				$valid = true;
			} else {
				$id = 0;
				$messages = $this->getTranslate ( 'text_error_user_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Index' 
			) );
		}
		
		$InputForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $this->ViewModel ( 'Index', $id ),
				'messages' => $messages,
				'id' => $id,
				'username' => $username,
				'valid' => $valid,
				'js' => '' 
		) ) );
		
		return $InputForm->getResponse ();
	}
	
	/**
	 * Form ViewModel
	 *
	 * @return ViewModel
	 */
	private function ViewModel($action = null, $id = null) {
		switch ($action) {
			case 'Index' :
			case 'User' :
				$ViewModel = new ViewModel ();
				$ViewModel->setTerminal ( true );
				$ViewModel->setTemplate ( 'share/form/update' );
				$ViewModel->setVariables ( array (
						'form' => $this->InputForm ( $action, $id ),
						'js' => '$(document).ready(function() {  $(this).Wallet();  });' 
				) );
				return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
				break;
			default :
				return $this->InputForm ( $action, $id );
				break;
		}
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm($action = null, $id = null) {
		if (! array_key_exists ( $action, $this->inputform )) {
			$this->inputform [$action] = null;
		}
		if ((! is_object ( $this->inputform [$action] ) && empty ( $this->inputform [$action] )) || ! empty ( $id )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => ucfirst ( $action ) 
			);
			if ($id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id )) {
					$data = $this->getUserManagement ()->getData ( $id );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
					}
				}
			}
			$this->inputform [$action] = new WalletForm ( $options );
		}
		return $this->inputform [$action];
	}
}

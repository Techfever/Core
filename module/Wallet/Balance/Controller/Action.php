<?php

namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Wallet\Form\Defined as WalletForm;

class BalanceActionController extends AbstractActionController {
	protected $type = 'wallet';
	protected $module = 'balance';
	protected $inputform = array ();
	protected $search_username = null;
	public function IndexAction() {
		if (! $this->getUserAccess ()->isLoginWallet ()) {
			return $this->redirect ()->toRoute ( 'Wallet/Login', array (
					'action' => 'Index' 
			) );
		}
		
		$this->addCSS ( "ui-lightness/jquery-ui.css", "jquery" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/tooltip.css" );
		
		$viewModel = '';
		$userID = $this->getUserIDAction ();
		if ($this->isAdminUser ()) {
			$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
			if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
				$userID = $this->Decrypt ( $cryptId );
				$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
			}
			
			$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/steps.css" );
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.search.js", array (
					'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
					'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
					'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Search' 
					) ),
					'searchformusername' => $this->search_username 
			) );
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/steps.js" );
			$viewModel = 'Search';
		} else {
			$viewModel = 'User';
		}
		
		if (! $this->isXmlHttpRequest ()) {
			return array (
					'inputmodel' => $this->ViewModel ( $viewModel, $userID ),
					'isAdminUser' => $this->isAdminUser () 
			);
		}
	}
	public function SearchAction() {
		$valid = false;
		$id = 0;
		$username = null;
		$messages = array ();
		$InputModel = null;
		
		$SearchForm = $this->InputForm ( 'Search' );
		if ($SearchForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $SearchForm->getPost ( 'search_username', null ) );
			$userID = $this->getUserManagement ()->getID ( $username );
			if ($userID > 0) {
				$valid = true;
				
				$this->InputForm ( 'Index', $userID );
				$InputModel = $this->ViewModel ( 'Index' );
			} else {
				$messages = $this->getTranslate ( 'text_error_user_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Update' 
			) );
		}
		$SearchForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $InputModel,
				'messages' => $messages,
				'id' => $userID,
				'username' => $username,
				'valid' => $valid,
				'js' => '$(this).Steps({
							formname : "' . $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) . '",
							formuri : "' . $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ) . '",
							dialogtitle : "' . $this->getTranslate ( "text_dialog_wallet_update_" . $this->module . "_title" ) . '",
							dialogcontent : "' . $this->getTranslate ( "text_dialog_wallet_update_" . $this->module . "_content" ) . '",
						})' 
		) ) );
		
		return $SearchForm->getResponse ();
	}
	private function ViewModel($action = 'Index', $id = null) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		if ($action === 'Search') {
			$ViewModel->setTemplate ( 'share/user/searchupdate' );
			$ViewModel->setVariables ( array (
					'searchform' => $this->InputForm ( $action ) 
			) );
		} elseif ($action === 'Index') {
			$ViewModel->setTemplate ( 'share/form/update' );
			$ViewModel->setVariables ( array (
					'form' => $this->InputForm ( $action, $id ) 
			) );
		} elseif ($action === 'User') {
			$ViewModel->setTemplate ( 'share/wallet/update' );
			$ViewModel->setVariables ( array (
					'form' => $this->InputForm ( $action, $id ) 
			) );
		}
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	private function InputForm($action = 'Index', $id = null) {
		if (! array_key_exists ( $action, $this->inputform ) || ! is_object ( $this->inputform [$action] )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $this->getUserRankGroupID (),
					'action' => $action 
			);
			if (! empty ( $id ) && $id > 0) {
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

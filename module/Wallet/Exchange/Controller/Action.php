<?php

namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Wallet\Form\Defined as WalletForm;

class ExchangeActionController extends AbstractActionController {
	protected $type = 'wallet';
	protected $module = 'exchange';
	protected $inputform = array ();
	protected $search_username = null;
	private $action = '';
	private $from_user = null;
	private $to_user = null;
	private $from_wallet_type = null;
	private $to_wallet_type = null;
	private $from_user_rank = null;
	private $to_user_rank = null;
	private $transaction_status = 3;
	private $transaction = 2000;
	public function IndexAction() {
		$this->action = $this->module;
		
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
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.wallet.js", array (
				'walletformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'walletformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'walletcallback' => '' 
		) );
		
		if ($this->isXmlHttpRequest ()) {
			$id = 0;
			$subaction = null;
			$js = null;
			$valid = false;
			$redirect = null;
			$flashmessages = null;
			$InputForm = $this->InputForm ( 'Index', $id );
			if ($InputForm->isPost () && $InputForm->isValid ()) {
				$submit = strtolower ( $InputForm->getPost ( 'submit', 'preview' ) );
				$id = $this->Decrypt ( $this->getPost ( 'user_modify', 0 ) );
				if ($submit == 'submit') {
					$data = $InputForm->getData ();
					
					$username = $InputForm->getDataValue ( 'user_access_username' );
					$rank_group = $InputForm->getDataValue ( 'user_rank_group_id' );
					$from_user = $InputForm->getDataValue ( 'user_access_id' );
					$from_rank = $InputForm->getDataValue ( 'user_rank_id' );
					$from_wallet_type = $InputForm->getPost ( 'user_wallet_type_from' );
					$to_wallet_type = $InputForm->getPost ( 'user_wallet_type_to' );
					
					$walletoption = array (
							'action' => $this->action,
							'from_user' => $from_user,
							'to_user' => $from_user,
							'from_wallet_type' => $from_wallet_type,
							'to_wallet_type' => $to_wallet_type,
							'from_user_rank' => $from_rank,
							'to_user_rank' => $from_rank,
							'transaction_status' => $this->transaction_status,
							'transaction' => $this->transaction 
					);
					$this->getUserWallet ()->setOptions ( $walletoption );
					if (! $this->getUserManagement ()->verifyID ( $from_user, $rank_group, 1 )) {
						$msg = $this->getTranslate ( 'text_error_user_username_not_exist' );
						$flashmessages = sprintf ( $msg, $this->getUserManagement ()->getUsername ( $walletoption ['from_user'] ) );
						$messagescount = 1;
					} elseif (! $this->getUserWallet ()->validUserPocketAmount ( $data ['user_wallet_amount'] )) {
						$msg = $this->getTranslate ( 'text_error_user_wallet_amount_insufficient' );
						$flashmessages = sprintf ( $msg, $this->getUserManagement ()->getUsername ( $walletoption ['from_user'] ) );
						$messagescount = 1;
					} elseif (! $this->getUserWallet ()->validRankAllow ()) {
						$msg = $this->getTranslate ( 'text_error_user_wallet_rank_not_allow' );
						$flashmessages = sprintf ( $msg, $this->getUserWallet ()->getTypeMessage ( $walletoption ['from_user_rank'] ), $this->getUserWallet ()->getTypeMessage ( $walletoption ['to_user_rank'] ) );
						$messagescount = 1;
					} elseif (! $this->getUserWallet ()->validTypeAllow ()) {
						$msg = $this->getTranslate ( 'text_error_user_wallet_type_not_allow' );
						$flashmessages = sprintf ( $msg, $this->getUserWallet ()->getTypeMessage ( $walletoption ['from_wallet_type'] ), $this->getUserWallet ()->getTypeMessage ( $walletoption ['to_wallet_type'] ) );
						$messagescount = 1;
					} elseif ($this->getUserWallet ()->createUserHistory ( $data )) {
						$valid = true;
						$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_success_msg_wallet_update_' . $this->module ) );
					} else {
						$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_error_msg_wallet_update_' . $this->module ) );
					}
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
						});
										$(this).Wallet()' 
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
					'action' => $action 
			);
			
			$walletoptions = array (
					'action' => $this->module 
			);
			if (! empty ( $id ) && $id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id, null, 1 )) {
					$data = $this->getUserManagement ()->getData ( $id );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
						$options ['rank'] = $data ['user_rank_group_id'];
						$walletoptions ['from_user'] = $data ['user_access_id'];
						$walletoptions ['from_user_rank'] = $data ['user_rank_id'];
						$walletoptions ['to_user'] = $data ['user_access_id'];
						$walletoptions ['to_user_rank'] = $data ['user_rank_id'];
					}
				}
			}
			$options ['wallet'] = $walletoptions;
			$this->inputform [$action] = new WalletForm ( $options );
		}
		return $this->inputform [$action];
	}
}

<?php

namespace Wallet\Withdraw\Process\Controller;

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
	protected $module = 'withdraw_process';
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
	private $action = '';
	private $from_user = null;
	private $to_user = 1;
	private $from_wallet_type = null;
	private $to_wallet_type = 'val{from_wallet_type}';
	private $from_user_rank = null;
	private $to_user_rank = 8888;
	private $transaction_status = 1;
	private $transaction = 5000;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->action = 'withdraw';
		
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
		$userProfileId = $this->getUserManagement ()->getProfileID ( $userID );
		if ($this->getUserBank ()->getUserBankDefaultID ( $userProfileId ) < 1) {
			$this->FlashMessenger ()->addMessage ( sprintf ( $this->getTranslate ( 'text_error_msg_bank_default_not_exist' ), $this->getUserManagement ()->getUsername ( $userID ) ) );
			$redirect = $this->url ()->fromRoute ( 'Account/Bank', array (
					'action' => 'Index' 
			) );
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
			$InputForm = $this->InputForm ( 'Index', $userID );
			if ($InputForm->isPost () && $InputForm->isValid ()) {
				$submit = strtolower ( $InputForm->getPost ( 'submit', 'preview' ) );
				if ($submit == 'submit') {
					$data = $InputForm->getData ();
					$userdata = $InputForm->getDataValues ();
					$data = array_merge ( $userdata, $data );
					
					$username = $InputForm->getDataValue ( 'user_access_username' );
					$rank_group = $InputForm->getDataValue ( 'user_rank_group_id' );
					$from_user = $InputForm->getDataValue ( 'user_access_id' );
					$from_rank = $InputForm->getDataValue ( 'user_rank_id' );
					$from_wallet_type = $InputForm->getPost ( 'user_wallet_type_from' );
					
					$walletoption = array (
							'action' => $this->action,
							'from_user' => $from_user,
							'to_user' => $this->to_user,
							'from_wallet_type' => $from_wallet_type,
							'to_wallet_type' => $from_wallet_type,
							'from_user_rank' => $from_rank,
							'to_user_rank' => $this->to_user_rank,
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
					'id' => $userID,
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
		$InputForm = $this->InputForm ( 'Search' );
		if ($InputForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $InputForm->getPost ( 'search_username', null ) );
			$id = $this->getUserManagement ()->getID ( $username );
			if ($id > 0) {
				$valid = true;
				$userProfileId = $this->getUserManagement ()->getProfileID ( $id );
				if ($this->getUserBank ()->getUserBankDefaultID ( $userProfileId ) < 1) {
					$valid = false;
					$this->FlashMessenger ()->addMessage ( sprintf ( $this->getTranslate ( 'text_error_msg_bank_default_not_exist' ), $this->getUserManagement ()->getUsername ( $id ) ) );
					$redirect = $this->url ()->fromRoute ( 'Account/Bank', array (
							'action' => 'Index' 
					) );
				}
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
				'js' => '',
				'redirect' => $redirect 
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
			$walletoptions = array (
					'action' => 'withdraw',
					'to_user' => $this->to_user,
					'to_user_rank' => $this->to_user_rank,
					'to_wallet_type' => $this->to_wallet_type 
			);
			if (! empty ( $id ) && $id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id, null, 1 )) {
					$data = $this->getUserManagement ()->getData ( $id );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
						$options ['rank'] = $data ['user_rank_group_id'];
						$walletoptions ['from_user'] = $data ['user_access_id'];
						$walletoptions ['from_user_rank'] = $data ['user_rank_id'];
					}
				}
			}
			$options ['wallet'] = $walletoptions;
			$this->inputform [$action] = new WalletForm ( $options );
		}
		return $this->inputform [$action];
	}
}
			
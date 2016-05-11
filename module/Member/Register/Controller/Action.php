<?php

namespace Member\Register\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\User\Form\Defined as UserRegisterForm;
use Techfever\View\View as UserRegisterView;
use Techfever\Bonus\Bonus as UserBonus;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Rank Group
	 *     
	 */
	protected $rankgroup = 10000;
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
						$data = $InputForm->getData ();
						$data ['user_rank_group_id'] = $this->rankgroup;
						$Rank = $this->getUserRank ();
						$Rank->setOption ( 'group', $data ['user_rank_group_id'] );
						$Rank->setOption ( 'id', $data ['user_rank'] );
						if ($Rank->verifyRank ()) {
							$submit = true;
							$RankData = $Rank->getRank ( $data ['user_rank'] );
							$data ['user_rank_price_dl'] = $RankData ['price_dl'];
							$data ['user_rank_price_pv'] = $RankData ['price_pv'];
							$data ['user_wallet_amount'] = $data ['user_rank_price_pv'];
							$data ['user_rank_price_status'] = ($RankData ['price_status'] == "1" ? True : False);
							
							$data ['transaction'] = $data ['user_rank_group_id'];
							$data ['user_username_open_tag'] = null;
							$data ['user_username_min'] = null;
							$data ['user_username_max'] = null;
							$data ['user_username_end_tag'] = null;
							if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN" )) {
								$data ['user_username_open_tag'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_OPEN" );
							}
							if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN" )) {
								$data ['user_username_min'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MIN" );
							}
							if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX" )) {
								$data ['user_username_max'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_MAX" );
							}
							if (defined ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END" )) {
								$data ['user_username_end_tag'] = constant ( "USER_REGISTER_USERNAME_CODE_" . $this->rankgroup . "_END" );
							}
							if (defined ( "USER_REGISTER_STATUS_" . $this->rankgroup . "_LOGIN" )) {
								$data ['user_status'] = constant ( "USER_REGISTER_STATUS_" . $this->rankgroup . "_LOGIN" );
							}
							if (defined ( "USER_REGISTER_ACTIVATION_" . $this->rankgroup . "_LOGIN" )) {
								$data ['user_activation_status'] = constant ( "USER_REGISTER_ACTIVATION_" . $this->rankgroup . "_LOGIN" );
								if (defined ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_LOGIN" )) {
									$data ['user_status'] = constant ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_LOGIN" );
								}
							}
							if (defined ( "USER_REGISTER_WALLET_DEDUCT_" . $this->rankgroup . "_LOGIN" )) {
								$data ['user_wallet_deduct_status'] = constant ( "USER_REGISTER_WALLET_DEDUCT_" . $this->rankgroup . "_LOGIN" );
							} else {
								$data ['user_wallet_deduct_status'] = "False";
							}
							if ($data ['user_wallet_deduct_status'] == "True" && ($data ['user_rank_price_status'] && $data ['user_rank_price_pv'] > 0)) {
								$walletoption = array (
										'action' => 'register',
										'from_user' => $this->getUserAccess ()->getID (),
										'to_user' => 1,
										'from_wallet_type' => $data ['user_rank_wallet_type'],
										'to_wallet_type' => $data ['user_rank_wallet_type'],
										'from_user_rank' => $this->getUserAccess ()->getRankID (),
										'to_user_rank' => 8888,
										'transaction_status' => 3,
										'transaction' => $data ['user_rank_group_id'] 
								);
								$this->getUserWallet ()->setOptions ( $walletoption );
								if (! $this->getUserWallet ()->validUserPocketAmount ( $data ['user_rank_price_pv'] )) {
									$submit = false;
								}
							}
							if ($submit) {
								$id = $this->getUserManagement ()->createUser ( $data );
								if ($this->getUserManagement ()->verifyID ( $this->getID () )) {
									$this->setVerified ( true );
									$cryptID = $this->Encrypt ( $id );
									$datetime = new \DateTime ();
									$options = array (
											'servicelocator' => $this->getServiceLocator (),
											'user_access_id' => $id,
											'execute_date' => $datetime->format ( 'Y-m-d' ) 
									);
									$Bonus = new UserBonus ( $options );
									$Bonus->calculateBonus ();
								}
							} else {
								$this->setValid ( false );
								$this->setValidCallback ( "
					$(\".ui-dialog-" . $this->getDialogID () . "-modal\").modal({
						dialogclass: \"ui-dialog-" . $this->getDialogID () . "-valid-modal\",
						id: \"ui-dialog-" . $this->getDialogID () . "-valid-content\",
						height : 170,
						width : 300,
						title : \"" . $this->getTranslate ( 'text_error_msg_user_wallet_insufficient_title' ) . "\",
						content : \"" . $this->getTranslate ( 'text_error_msg_user_wallet_insufficient_content' ) . "\",
						buttons : {
							\"" . $this->getTranslate ( 'text_ok' ) . "\" : function() {
								$(this).dialog(\"close\");
							},
						},
					});		
					" );
							}
						} else {
							$this->setValid ( false );
							$this->setValidCallback ( "
					$(\".ui-dialog-" . $this->getDialogID () . "-modal\").modal({
						dialogclass: \"ui-dialog-" . $this->getDialogID () . "-valid-modal\",
						id: \"ui-dialog-" . $this->getDialogID () . "-valid-content\",
						height : 170,
						width : 300,
						title : \"" . $this->getTranslate ( 'text_error_msg_user_rank_not_exist_title' ) . "\",
						content : \"" . $this->getTranslate ( 'text_error_msg_user_rank_not_exist_content' ) . "\",
						buttons : {
							\"" . $this->getTranslate ( 'text_ok' ) . "\" : function() {
								$(this).dialog(\"close\");
							},
						},
					});		
					" );
						}
					}
				} else {
					$this->setValidatorRelation ( $InputForm->getValidatorRelation ( $this->getInput () ) );
					$this->setMessages ( $InputForm->getMessages () );
					$this->setMessagesTotal ( $InputForm->getMessagesTotal () );
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
			if ($this->getUserManagement ()->verifyID ( $id, $this->rankgroup, null )) {
				$data = $this->getUserManagement ()->getData ( $id );
				if (count ( $data ) > 0) {
					$date = $data ['user_access_created_date'];
					$timestampNow = new \DateTime ();
					$timestampCreated = new \DateTime ( $date );
					$timestampDiff = $timestampNow->format ( 'YmdHis' ) - $timestampCreated->format ( 'YmdHis' );
					if ($timestampDiff <= 3600) {
						$options = array (
								'servicelocator' => $this->getServiceLocator (),
								'variable' => $data 
						);
						$this->viewdata = new UserRegisterView ( $options );
					}
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
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $this->rankgroup 
			);
			$this->inputform = new UserRegisterForm ( $options );
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
				"Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css",
		);
	}
	
	/**
	 * Javascript
	 *
	 * @return Array
	 */
	protected function Javascript() {
		return array (
				"Theme/" . SYSTEM_THEME_LOAD . "/Js/address.js",
				"Theme/" . SYSTEM_THEME_LOAD . "/Js/bank.js",
				"Theme/" . SYSTEM_THEME_LOAD . "/Js/rank.js",
		);
	}
	
	/**
	 * Init Callback
	 *
	 * @return JS
	 */
	protected function initCallback() {
		return "
		if ( $.isFunction( $.fn.Address )) {
			var address = $('form[id=" . $this->getFormID () . "]').Address({
				country: 'user_address_country_text',
				state: 'user_address_state_text',
			});
		}
		if ( $.isFunction( $.fn.Bank )) {
			var bank = $('form[id=" . $this->getFormID () . "]').Bank({
				country: 'user_bank_country_text',
				state: 'user_bank_state_text',
			});
		}
		if ( $.isFunction( $.fn.Bank )) {	
			var rank = $('form[id=" . $this->getFormID () . "]').Rank({
				rank: 'user_rank',
			});
		}
		";
	}
}

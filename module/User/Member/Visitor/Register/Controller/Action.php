<?php

namespace User\Member\Visitor\Register\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserRegisterForm;
use Techfever\View\View as UserRegisterView;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Rank Group
	 *     
	 */
	protected $rankgroup = 10000;
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'member';
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
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_user_register_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_user_register_content" ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.address.js", array (
				'addressformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.bank.js", array (
				'bankformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.register.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_user_register_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_user_register_content" ) 
		) );
		
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
					if (defined ( "USER_REGISTER_STATUS_" . $this->rankgroup . "_VISITOR" )) {
						$data ['user_status'] = constant ( "USER_REGISTER_STATUS_" . $this->rankgroup . "_VISITOR" );
					}
					if (defined ( "USER_REGISTER_ACTIVATION_" . $this->rankgroup . "_VISITOR" )) {
						$data ['user_activation_status'] = constant ( "USER_REGISTER_ACTIVATION_" . $this->rankgroup . "_VISITOR" );
						if (defined ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_VISITOR" )) {
							$data ['user_status'] = constant ( "USER_REGISTER_ACTIVATION_STATUS_" . $this->rankgroup . "_VISITOR" );
						}
					}
					if (defined ( "USER_REGISTER_WALLET_DEDUCT_" . $this->rankgroup . "_VISITOR" )) {
						$data ['user_wallet_deduct_status'] = constant ( "USER_REGISTER_WALLET_DEDUCT_" . $this->rankgroup . "_VISITOR" );
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
						if ($id !== false && $id > 0) {
							/*
							 * $mail = new Mail\Message(); $mail->setBody('Thank you for your registration. To complete the registration process, please proceed with the payment and email to us the bank in proof. '. '' . $this->getTranslate ( 'text_user_profile_fullname') . ' :' . $data['user_profile_fullname']. '' . '' . $this->getTranslate ( 'text_user_profile_nric_passport') . ' :' . $data['user_profile_nric_passport']. '' . '' . $this->getTranslate ( 'text_user_profile_email_address') . ' :' . $data['user_profile_email_address']. '' . '' . $this->getTranslate ( 'text_user_profile_mobile_no') . ' :' . $data['user_profile_mobile_no']. '' . '' . '' . $this->getTranslate ( 'text_user_hierarchy_sponsor') . ' :' . $data['user_hierarchy_sponsor']. '' . '' . $this->getTranslate ( 'text_user_access_password') . ' :' . $data['user_access_password']. '' . '' . $this->getTranslate ( 'text_user_access_security') . ' :' . $data['user_access_security']. ''); $mail->setFrom('admin@ifxmoney.com', 'IFX Member Registration'); $mail->addTo($data['user_profile_email_address'], $data['user_profile_fullname']); $mail->addBcc('admin@ifxmoney.com', 'IFX Member Registration'); $mail->addBcc('ifxmoneygold@gmail.com', 'IFX Member Registration'); $mail->setSubject('IFX Member Registration'); $transport = new Mail\Transport\Sendmail(); $transport->send($mail);
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
					} else {
						$this->FlashMessenger ()->addMessage ( sprintf ( $this->getTranslate ( 'text_error_user_wallet_amount_insufficient' ), $this->getUserAccess ()->getUsername () ) );
						$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
								'action' => 'Index' 
						) );
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
					'messagescount' => $InputForm->getMessagesTotal () 
			) ) );
			return $InputForm->getResponse ();
		} else {
			if (defined ( "USER_REGISTER_RENDER_LAYOUT_" . $this->rankgroup . "_VISITOR" )) {
				$render_layout = constant ( "USER_REGISTER_RENDER_LAYOUT_" . $this->rankgroup . "_VISITOR" );
				if ($render_layout == "True") {
					$this->layout ( 'blank/layout' );
				}
			}
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
		
		if (defined ( "USER_REGISTER_RENDER_LAYOUT_" . $this->rankgroup . "_VISITOR" )) {
			$render_layout = constant ( "USER_REGISTER_RENDER_LAYOUT_" . $this->rankgroup . "_VISITOR" );
			if ($render_layout == "True") {
				$this->layout ( 'blank/layout' );
			}
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
}

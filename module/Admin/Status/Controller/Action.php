<?php

namespace Admin\Status\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as UserUpdateForm;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Rank Group
	 *     
	 */
	protected $rankgroup = 99999;
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'admin';
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'status';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = null;
	/**
	 *
	 * @var Search Form
	 *     
	 */
	protected $searchform = null;
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
		return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
				'action' => 'Update' 
		) );
	}
	
	/**
	 * Update Action
	 *
	 * @return ViewModel
	 */
	public function UpdateAction() {
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css" );
		
		$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
		if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
			$userID = $this->Decrypt ( $cryptId );
			
			$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
		}
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Search' 
				) ),
				'searchformusername' => $this->search_username 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/steps.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Update' 
				) ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.update.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'updateformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'update' 
				) ),
				'updateformdialogtitle' => $this->getTranslate ( "text_dialog_user_update_" . $this->module . "_title" ),
				'updateformdialogcontent' => $this->getTranslate ( "text_dialog_user_update_" . $this->module . "_content" ) 
		) );
		
		if ($this->isXmlHttpRequest ()) {
			$id = 0;
			$encoded_id = $this->getPost ( 'modify_value' );
			if (! empty ( $encoded_id ) && strlen ( $encoded_id ) > 0) {
				$id = $this->Decrypt ( $encoded_id );
			}
			$InputForm = $this->InputForm ( $id );
			$action = strtolower ( $this->getPost ( 'submit', 'preview' ) );
			$subaction = null;
			$js = null;
			$valid = false;
			$redirect = null;
			$flashmessages = null;
			if ($InputForm->isPost () && $InputForm->isValid () && $action == 'submit') {
				$valid = true;
				$id = $this->Decrypt ( $InputForm->getPost ( 'modify_value' ) );
				
				$data = $InputForm->getData ();
				if ($this->getUserManagement ()->verifyID ( $id, $this->rankgroup, null ) && $this->getUserManagement ()->updateStatus ( $id, $data )) {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_success_msg_user_update_' . $this->module ) );
				} else {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( 'text_error_msg_user_update_' . $this->module ) );
				}
				$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Update' 
				) );
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
					'search' => $this->SearchForm () 
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
		
		$SearchForm = $this->SearchForm ();
		if ($SearchForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $SearchForm->getPost ( 'search_username', null ) );
			$id = $this->getUserManagement ()->getID ( $username, $this->rankgroup, null );
			if ($id > 0) {
				$valid = true;
			} else {
				$id = 0;
				$messages = $this->getTranslate ( 'text_error_' . $this->type . '_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Update' 
			) );
		}
		
		$SearchForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $this->ViewModelUpdate ( $id ),
				'messages' => $messages,
				'id' => $id,
				'username' => $username,
				'valid' => $valid,
				'js' => '$(this).UserUpdate();' 
		) ) );
		
		return $SearchForm->getResponse ();
	}
	
	/**
	 * Search Action
	 *
	 * @return ViewModel
	 */
	private function ViewModelUpdate($id = null) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		$ViewModel->setTemplate ( 'share/form/update' );
		$ViewModel->setVariables ( array (
				'form' => $this->InputForm ( $id ) 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	
	/**
	 * Form Search
	 *
	 * @return Form
	 */
	protected function SearchForm() {
		if (! is_object ( $this->searchform ) && empty ( $this->searchform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $this->rankgroup,
					'action' => 'Search' 
			);
			$this->searchform = new UserUpdateForm ( $options );
		}
		return $this->searchform;
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm($id = null) {
		if ((! is_object ( $this->inputform ) && empty ( $this->inputform )) || ! empty ( $id )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $this->rankgroup,
					'action' => 'Update' 
			);
			if ($id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id, $this->rankgroup, null )) {
					$data = $this->getUserManagement ()->getData ( $id );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
					}
				}
			}
			$this->inputform = new UserUpdateForm ( $options );
		}
		return $this->inputform;
	}
}

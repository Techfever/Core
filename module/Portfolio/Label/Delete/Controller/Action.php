<?php

namespace Portfolio\Label\Delete\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Label as ContentLabelManagement;
use Techfever\Content\Label\Form\Defined as ContentForm;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'portfolio';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'label_delete';
	
	/**
	 *
	 * @var Portfolio Type
	 *     
	 */
	private $contenttype = '9000';
	
	/**
	 *
	 * @var Portfolio Object
	 *     
	 */
	private $labelobject = null;
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
	 * @var Portfolio
	 *
	 */
	protected $search_content = null;
	
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
		
		$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
		if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
			$labelID = $this->Decrypt ( $cryptId );
			
			$this->search_content = $this->getLabelObject ()->getLabelCode ( $labelID );
		}
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.search.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Search' 
				) ),
				'searchformquery' => $this->search_content 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.js", array (
				'formid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'formuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Update' 
				) ),
				'formdialogtitle' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_title" ),
				'formdialogcontent' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_content" ),
				'content' => "content_label" 
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
				$this->getLabelObject ()->setContentLabelID ( $id );
				if ($this->getLabelObject ()->verifyLabelID () && $this->getLabelObject ()->deleteLabelFactory ()) {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( "text_success_msg_" . $this->type . "_" . $this->module ) );
				} else {
					$this->FlashMessenger ()->addMessage ( $this->getTranslate ( "text_error_msg_" . $this->type . "_" . $this->module ) );
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
		$content = null;
		$messages = array ();
		$cryptID = null;
		$SearchForm = $this->SearchForm ();
		if ($SearchForm->isXmlHttpRequest ()) {
			$content = strtoupper ( $SearchForm->getPost ( 'search_content', null ) );
			$id = $this->getLabelObject ()->searchLabel ( $content );
			if ($id > 0) {
				$cryptID = $this->Encrypt ( $id );
				$valid = true;
			} else {
				$id = 0;
				$messages = $this->getTranslate ( 'text_error_' . $this->type . '_' . $this->module . '_not_exist' );
				$messages = sprintf ( $messages, $content );
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
				'query' => $content,
				'valid' => $valid,
				'js' => ' $(this).ContentDelete();' 
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
					'type' => $this->contenttype,
					'user' => $this->getUserID (),
					'label' => 0,
					'action' => 'Search' 
			);
			$this->searchform = new ContentForm ( $options );
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
			
			$user_id = null;
			if (! $this->isAdminUser ()) {
				$user_id = $this->getUserID ();
			}
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'type' => $this->contenttype,
					'user' => $user_id,
					'label' => $id,
					'modify' => $id,
					'action' => 'Update' 
			);
			if ($id > 0) {
				$this->getLabelObject ()->setContentLabelID ( $id );
				if ($this->getLabelObject ()->verifyLabelID ()) {
					$data = $this->getLabelObject ()->getLabelComplete ();
					$data = $this->getLabelObject ()->dataLabelArrange ( $data );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
					}
				}
			}
			$this->inputform = new ContentForm ( $options );
		}
		return $this->inputform;
	}
	
	/**
	 * Get Content Object
	 *
	 * @return Object
	 */
	private function getLabelObject() {
		if (! is_object ( $this->labelobject ) && empty ( $this->labelobject )) {
			$Translator = $this->getTranslator ();
			
			$user_id = null;
			if (! $this->isAdminUser ()) {
				$user_id = $this->getUserID ();
			}
			$type_id = $this->contenttype;
			$language_id = $Translator->getLocaleID ();
			
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'user_id' => $user_id,
					'type_id' => $type_id,
					'language_id' => $language_id 
			);
			$this->labelobject = new ContentLabelManagement ( $options );
		}
		return $this->labelobject;
	}
}

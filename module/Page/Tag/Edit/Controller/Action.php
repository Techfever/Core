<?php

namespace Page\Tag\Edit\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Tag as ContentTagManagement;
use Techfever\Content\Tag\Form\Defined as ContentForm;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'page';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'tag_edit';
	
	/**
	 *
	 * @var Page Type
	 *     
	 */
	private $contenttype = '2000';
	
	/**
	 *
	 * @var Page Object
	 *     
	 */
	private $tagobject = null;
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
	 * @var Page
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
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css" );
		$this->addJavascript ( "vendor/Techfever/Javascript/tinymce/tinymce.js" );
		
		$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
		if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
			$tagID = $this->Decrypt ( $cryptId );
			
			$this->search_content = $this->getTagObject ()->getTagCode ( $tagID );
		}
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.search.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Search' 
				) ),
				'searchformquery' => $this->search_content 
		) );
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
				'searchformid' => $this->convertToUnderscore ( 'Ajax/User/addPermissionUserSearch', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( 'Ajax/User', array (
						'action' => 'addPermissionUserSearch' 
				) ),
				'searchformusername' => "" 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/steps.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Update' 
				) ),
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_content" ) 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/permissions.js", array (
				'formid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'content' => "content_tag" 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.js", array (
				'formid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Update', '/' ),
				'content' => "content_tag" 
		) );
		
		if ($this->isXmlHttpRequest ()) {
			$id = 0;
			$encoded_id = $this->getPost ( 'modify' );
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
				$data = $InputForm->getData ();
				$this->getTagObject ()->setContentTagID ( $id );
				if ($this->getTagObject ()->verifyTagID () && $this->getTagObject ()->updateTagFactory ( $data )) {
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
			$id = $this->getTagObject ()->searchTag ( $content );
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
				'js' => '$(this).StepsSetting({ modify: "' . $cryptID . '" }); $(this).ContentEditor({ content: "content_tag", action : "edit" });' 
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
					'tag' => 0,
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
					'tag' => $id,
					'modify' => $id,
					'action' => 'Update' 
			);
			if ($id > 0) {
				$this->getTagObject ()->setContentTagID ( $id );
				if ($this->getTagObject ()->verifyTagID ()) {
					$data = $this->getTagObject ()->getTagComplete ();
					$data = $this->getTagObject ()->dataTagArrange ( $data );
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
	private function getTagObject() {
		if (! is_object ( $this->tagobject ) && empty ( $this->tagobject )) {
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
			$this->tagobject = new ContentTagManagement ( $options );
		}
		return $this->tagobject;
	}
}

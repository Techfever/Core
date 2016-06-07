<?php

namespace Content\Note\Label\Create\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Label as ContentLabelManagement;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Content\Label\Form\Defined as ContentForm;
use Techfever\View\View as ContentView;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'note';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'label_create';
	
	/**
	 *
	 * @var Note Type
	 *     
	 */
	private $contenttype = '3000';
	
	/**
	 *
	 * @var Note Object
	 *     
	 */
	private $labelobject = null;
	
	/**
	 *
	 * @var Note Input
	 *     
	 */
	protected $inputform = null;
	
	/**
	 *
	 * @var Note View
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
		$this->addJavascript ( "vendor/Techfever/Javascript/tinymce/tinymce.js" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/steps.js", array (
				'stepsformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'stepsformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Index' 
				) ),
				'stepsformdialogtitle' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_title" ),
				'stepsformdialogcontent' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_content" ) 
		) );
		
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
				'searchformid' => $this->convertToUnderscore ( 'Ajax/User/addPermissionUserSearch', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( 'Ajax/User', array (
						'action' => 'addPermissionUserSearch' 
				) ),
				'searchformusername' => "" 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/permissions.js", array (
				'formid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'content' => "content_label" 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.js", array (
				'formid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'content' => "content_label" 
		) );
		$this->getLabelObject ()->setOption ( 'user_id', $this->getUserID () );
		$this->getLabelObject ()->setOption ( 'type_id', $this->contenttype );
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
				if ($this->getLabelObject ()->verifyContentTypeID ()) {
					$id = $this->getLabelObject ()->createLabelFactory ( $data );
					if ($id !== false && $id > 0) {
						$this->FlashMessenger ()->addMessage ( $this->getTranslate ( "text_success_msg_" . $this->type . "_" . $this->module ) );
						$cryptID = $this->Encrypt ( $id );
						$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
								'action' => 'Preview',
								'crypt' => $cryptID 
						) );
					} else {
						$this->FlashMessenger ()->addMessage ( $this->getTranslate ( "text_error_msg_" . $this->type . "_" . $this->module ) );
						
						$redirect = $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
								'action' => 'Index' 
						) );
					}
				}
			} else if ($this->isOverrideRequest ()) {
				$InputForm->getResponse ()->setContent ( Json::encode ( array (
						'valid' => true,
						'callback' => '',
						'title' => $this->getTranslate ( "text_dialog_" . $this->type . "_" . $this->module . "_title" ),
						'content' => $this->ViewModel ( 'index' ) 
				) ) );
				return $InputForm->getResponse ();
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
			$this->getLabelObject ()->setContentLabelID ( $id );
			if ($this->getLabelObject ()->verifyLabelID ()) {
				$data = $this->getLabelObject ()->getLabelComplete ();
				if (count ( $data ) > 0) {
					$date = $data ['label'] ['content_label_created_date'];
					$timestampNow = new \DateTime ();
					$timestampCreated = new \DateTime ( $date );
					$timestampDiff = $timestampNow->format ( 'YmdHis' ) - $timestampCreated->format ( 'YmdHis' );
					if ($timestampDiff <= 3600) {
						$data = $this->getLabelObject ()->dataLabelArrange ( $data );
						$options = array (
								'servicelocator' => $this->getServiceLocator (),
								'variable' => $data 
						);
						$this->viewdata = new ContentView ( $options );
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
			$user_id = $this->getUserID ();
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'type' => $this->contenttype,
					'user' => $user_id,
					'label' => 0 
			);
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
			$user_id = $this->getUserID ();
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

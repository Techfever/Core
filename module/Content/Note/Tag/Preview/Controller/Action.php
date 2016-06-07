<?php

namespace Content\Note\Tag\Preview\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Tag as ContentTagManagement;
use Techfever\Content\Tag\Form\Defined as ContentForm;
use Techfever\View\View as ContentView;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

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
	protected $module = 'tag_preview';
	
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
	private $tagobject = null;
	
	/**
	 *
	 * @var Note View
	 *     
	 */
	protected $viewdata = null;
	/**
	 *
	 * @var Search Form
	 *     
	 */
	protected $searchform = null;
	/**
	 *
	 * @var Note
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
				'action' => 'Show' 
		) );
	}
	
	/**
	 * Show Action
	 *
	 * @return ViewModel
	 */
	public function ShowAction() {
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		
		$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
		if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
			$tagID = $this->Decrypt ( $cryptId );
			
			$this->search_content = $this->getTagObject ()->getTagCode ( $tagID );
		}
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/content.search.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Show', '/' ),
				'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Search' 
				) ),
				'searchformquery' => $this->search_content 
		) );
		
		if ($this->isXmlHttpRequest ()) {
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
					'action' => 'Show' 
			) );
		}
		
		$SearchForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $this->ViewModelPreview ( $id ),
				'messages' => $messages,
				'id' => $id,
				'query' => $content,
				'valid' => $valid,
				'js' => '' 
		) ) );
		
		return $SearchForm->getResponse ();
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
	 * Search Action
	 *
	 * @return ViewModel
	 */
	private function ViewModelPreview($id = null) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		$ViewModel->setTemplate ( 'share/form/preview' );
		$ViewModel->setVariables ( array (
				'view' => $this->PreviewData ( $id ) 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	
	/**
	 * Preview Data
	 *
	 * @return Data
	 */
	protected function PreviewData($id = null) {
		if (! is_object ( $this->viewdata ) && empty ( $this->viewdata ) && ! empty ( $id )) {
			$this->getTagObject ()->setContentTagID ( $id );
			if (! $this->isAdminUser ()) {
				$this->getDataObject ()->setContentUserID ( $this->getUserID () );
			}
			if ($this->getTagObject ()->verifyTagID ()) {
				$data = $this->getTagObject ()->getTagComplete ();
				if (count ( $data ) > 0) {
					$data = $this->getTagObject ()->dataTagArrange ( $data );
					$options = array (
							'servicelocator' => $this->getServiceLocator (),
							'variable' => $data,
							'action' => 'show' 
					);
					$this->viewdata = new ContentView ( $options );
				}
			}
		}
		return $this->viewdata;
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

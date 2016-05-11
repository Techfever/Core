<?php

namespace File\Data\Preview\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Data as ContentDataManagement;
use Techfever\Content\Data\Form\Defined as ContentForm;
use Techfever\View\View as ContentView;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'file';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'data_preview';
	
	/**
	 *
	 * @var File Type
	 *     
	 */
	private $contenttype = '10000';
	
	/**
	 *
	 * @var File Object
	 *     
	 */
	private $dataobject = null;
	
	/**
	 *
	 * @var File View
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
	 * @var File
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
			$dataID = $this->Decrypt ( $cryptId );
			
			$this->search_content = $this->getDataObject ()->getDataCode ( $dataID );
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
			$id = $this->getDataObject ()->searchData ( $content );
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
					'data' => 0,
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
			$this->getDataObject ()->setContentDataID ( $id );
			if (! $this->isAdminUser ()) {
				$this->getDataObject ()->setContentUserID ( $this->getUserID () );
			}
			if ($this->getDataObject ()->verifyDataID ()) {
				$data = $this->getDataObject ()->getDataComplete ();
				if (count ( $data ) > 0) {
					$data = $this->getDataObject ()->dataDataArrange ( $data );
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
	private function getDataObject() {
		if (! is_object ( $this->dataobject ) && empty ( $this->dataobject )) {
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
			$this->dataobject = new ContentDataManagement ( $options );
		}
		return $this->dataobject;
	}
}

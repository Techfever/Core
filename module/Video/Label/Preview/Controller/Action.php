<?php

namespace Video\Label\Preview\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\Content\Label as ContentLabelManagement;
use Techfever\Content\Label\Form\Defined as ContentForm;
use Techfever\View\View as ContentView;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'video';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'label_preview';
	
	/**
	 *
	 * @var Video Type
	 *     
	 */
	private $contenttype = '5000';
	
	/**
	 *
	 * @var Video Object
	 *     
	 */
	private $labelobject = null;
	
	/**
	 *
	 * @var Video View
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
	 * @var Video
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
			$labelID = $this->Decrypt ( $cryptId );
			
			$this->search_content = $this->getLabelObject ()->getLabelCode ( $labelID );
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
					'label' => 0,
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
			$this->getLabelObject ()->setContentLabelID ( $id );
			if (! $this->isAdminUser ()) {
				$this->getDataObject ()->setContentUserID ( $this->getUserID () );
			}
			if ($this->getLabelObject ()->verifyLabelID ()) {
				$data = $this->getLabelObject ()->getLabelComplete ();
				if (count ( $data ) > 0) {
					$data = $this->getLabelObject ()->dataLabelArrange ( $data );
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

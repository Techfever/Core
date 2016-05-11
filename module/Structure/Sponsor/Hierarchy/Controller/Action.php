<?php

namespace Structure\Sponsor\Hierarchy\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as StructureForm;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'structure';
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'sponsor_hierarchy';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = null;
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
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/hierarchy.css" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/hierarchy.js", array (
				'hierarchylevel' => $this->getUserStructure ()->getOption ( 'level' ) 
		) );
		$strucutureModel = '';
		$searchForm = '';
		$userID = $this->getUserIDAction ();
		if ($this->isAdminUser ()) {
			$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
			if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
				$userID = $this->Decrypt ( $cryptId );
				$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
			}
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
					'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
					'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
					'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Search' 
					) ),
					'searchformusername' => $this->search_username 
			) );
			$searchForm = $this->SearchForm ();
		} else {
			$strucutureModel = $this->StuctureViewModel ( $userID );
		}
		
		if (! $this->isXmlHttpRequest ()) {
			return array (
					'search' => $searchForm,
					'structuremodel' => $strucutureModel,
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
		
		$SearchForm = $this->SearchForm ();
		if ($SearchForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $SearchForm->getPost ( 'search_username', null ) );
			$id = $this->getUserManagement ()->getID ( $username );
			if ($id > 0) {
				$valid = true;
			} else {
				$id = 0;
				$messages = $this->getTranslate ( 'text_error_user_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Update' 
			) );
		}
		
		$SearchForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $this->StuctureViewModel ( $id ),
				'messages' => $messages,
				'id' => $id,
				'username' => $username,
				'valid' => $valid,
				'js' => '$(this).HierarchyInit();' 
		) ) );
		
		return $SearchForm->getResponse ();
	}
	
	/**
	 * Get Structure
	 *
	 * @return ViewModel
	 */
	private function StuctureViewModel($id = null) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		$data = array ();
		if (! empty ( $id ) && $id > 0) {
			if ($this->getUserManagement ()->verifyID ( $id )) {
				$this->getUserStructure ()->setOption ( 'type', 'sponsor' );
				$this->getUserStructure ()->setOption ( 'user', $id );
				$data = $this->getUserStructure ()->getStructureHierarchy ();
			}
		}
		$ViewModel->setTemplate ( 'share/structure/hierarchy' );
		$ViewModel->setVariables ( array (
				'structure' => (is_string ( $data ) ? $data : "") 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function SearchForm() {
		if (! is_object ( $this->inputform ) && empty ( $this->inputform )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Search' 
			);
			$this->inputform = new StructureForm ( $options );
		}
		return $this->inputform;
	}
}

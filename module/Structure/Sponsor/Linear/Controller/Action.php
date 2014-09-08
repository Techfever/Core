<?php

namespace Structure\Sponsor\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\User\Form\Defined as StructureForm;

class LinearActionController extends AbstractActionController {
	protected $type = 'Structure_Sponsor';
	protected $module = 'linear';
	protected $inputform = array ();
	protected $search_username = null;
	public function IndexAction() {
		$this->addCSS ( "ui-lightness/jquery-ui.css", "jquery" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/CSS/hierarchy.css" );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/hierarchy.js", array (
				'hierarchylevel' => $this->getUserStructure ()->getOption ( 'level' ) 
		) );
		$viewModel = '';
		$userID = $this->getUserIDAction ();
		if ($this->isAdminUser ()) {
			$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
			if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
				$userID = $this->Decrypt ( $cryptId );
				$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
			}
			$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/user.search.js", array (
					'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
					'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
					'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
							'action' => 'Search' 
					) ),
					'searchformusername' => $this->search_username 
			) );
			$viewModel = 'Search';
		} else {
			$viewModel = 'Index';
		}
		
		if (! $this->isXmlHttpRequest ()) {
			return array (
					'inputmodel' => $this->ViewModel ( $viewModel, $userID ),
					'isAdminUser' => $this->isAdminUser () 
			);
		}
	}
	public function SearchAction() {
		$valid = false;
		$id = 0;
		$username = null;
		$messages = array ();
		$InputModel = null;
		
		$SearchForm = $this->InputForm ( 'Search' );
		if ($SearchForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $SearchForm->getPost ( 'search_username', null ) );
			$userID = $this->getUserManagement ()->getID ( $username );
			if ($userID > 0) {
				$valid = true;
				$InputModel = $this->ViewModel ( 'Index', $userID );
			} else {
				$messages = $this->getTranslate ( 'text_error_user_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Update' 
			) );
		}
		$SearchForm->getResponse ()->setContent ( Json::encode ( array (
				'inputmodel' => $InputModel,
				'messages' => $messages,
				'id' => $userID,
				'username' => $username,
				'valid' => $valid,
				'js' => '' 
		) ) );
		
		return $SearchForm->getResponse ();
	}
	private function ViewModel($action = 'Index', $id = null) {
		$ViewModel = new ViewModel ();
		$ViewModel->setTerminal ( true );
		if ($action === 'Search') {
			$ViewModel->setTemplate ( 'share/user/searchupdate' );
			$ViewModel->setVariables ( array (
					'searchform' => $this->InputForm ( $action ) 
			) );
		} elseif ($action === 'Index') {
			$data = array ();
			if (! empty ( $id ) && $id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id )) {
					$this->getUserStructure ()->setOption ( 'type', 'sponsor' );
					$this->getUserStructure ()->setOption ( 'user', $id );
					$data = $this->getUserStructure ()->getStructureLinear ();
				}
			}
			$ViewModel->setTemplate ( 'share/structure/linear' );
			$ViewModel->setVariables ( array (
					'structure' => $data 
			) );
		}
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
	}
	private function InputForm($action = 'Index') {
		if (! array_key_exists ( $action, $this->inputform ) || ! is_object ( $this->inputform [$action] )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => $action 
			);
			$this->inputform [$action] = new StructureForm ( $options );
		}
		return $this->inputform [$action];
	}
}

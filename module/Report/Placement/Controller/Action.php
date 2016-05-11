<?php

namespace Report\Placement\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Form\Form as UserReportForm;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'report';
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'placement';
	/**
	 *
	 * @var Input Form
	 *     
	 */
	protected $inputform = array ();
	/**
	 *
	 * @var Username
	 *
	 */
	protected $search_username = null;
	/**
	 *
	 * @var Filter Query
	 *     
	 */
	protected $query = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
				'action' => 'Review' 
		) );
	}
	public function ReviewAction() {
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/tooltip.css" );
		$this->addCSS ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/steps.css" );
		$cryptId = ( string ) $this->params ()->fromRoute ( 'crypt', null );
		if (! empty ( $cryptId ) && strlen ( $cryptId ) > 0) {
			$userID = $this->Decrypt ( $cryptId );
			
			$this->search_username = $this->getUserManagement ()->getUsername ( $userID );
		}
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/user.search.js", array (
				'updateformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Filter', '/' ),
				'searchformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Search', '/' ),
				'searchformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Search' 
				) ),
				'searchformusername' => $this->search_username 
		) );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/report.filter.js", array (
				'filterformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Filter', '/' ),
				'filterformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName (), array (
						'action' => 'Filter' 
				) ) 
		) );
		
		$return = array (
				'isAdminUser' => $this->isAdminUser () 
		);
		if ($this->isAdminUser ()) {
			$return ['form'] = $this->ViewModel ( 'Search' );
			$return ['filter'] = "";
		} else {
			$userID = $this->getUserIDAction ();
			$return ['form'] = "";
			$return ['filter'] = $this->ViewModel ( 'Filter', $userID );
		}
		return $return;
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
		$redirect = null;
		$InputForm = $this->ViewModel ( 'Search' );
		if ($InputForm->isXmlHttpRequest ()) {
			$username = strtoupper ( $InputForm->getPost ( 'search_username', null ) );
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
					'action' => 'Index' 
			) );
		}
		
		$InputForm->getResponse ()->setContent ( Json::encode ( array (
				'filtermodel' => $this->ViewModel ( 'Filter', $id ),
				'reviewmodel' => $this->ViewModel ( 'Review', $id ),
				'messages' => $messages,
				'id' => $id,
				'username' => $username,
				'valid' => $valid,
				'js' => '$(this).FilterInitial();' 
		) ) );
		
		return $InputForm->getResponse ();
	}
	
	/**
	 * Filter Action
	 *
	 * @return ViewModel
	 */
	public function FilterAction() {
		$valid = false;
		$id = 0;
		$username = null;
		$messages = array ();
		$ReviewModel = null;
		
		$ReportForm = $this->InputForm ( 'Review' );
		if ($ReportForm->isXmlHttpRequest ()) {
			if (! $this->isAdminUser ()) {
				$id = $this->getUserID ();
			} else {
				$id = $this->Decrypt ( $ReportForm->getPost ( 'modify_value' ) );
			}
			$start_date_raw = $ReportForm->getPost ( 'filter_date_from', null );
			$start_date_day = $start_date_raw ['day'];
			$start_date_month = $start_date_raw ['month'];
			$start_date_year = $start_date_raw ['year'];
			$end_date_raw = $ReportForm->getPost ( 'filter_date_to', null );
			$end_date_day = $end_date_raw ['day'];
			$end_date_month = $end_date_raw ['month'];
			$end_date_year = $end_date_raw ['year'];
			
			$start_date = $start_date_year . '-' . $start_date_month . '-' . $start_date_day;
			$end_date = $end_date_year . '-' . $end_date_month . '-' . $end_date_day;
			$this->query = array (
					'start_date' => $start_date,
					'end_date' => $end_date 
			);
			if ($id > 0) {
				$valid = true;
				$ReviewModel = $this->ViewModel ( 'Review', $id );
			} else {
				$messages = $this->getTranslate ( 'text_error_user_username_not_exist' );
				$messages = sprintf ( $messages, $username );
			}
		} else {
			return $this->redirect ()->toRoute ( $this->getMatchedRouteName (), array (
					'action' => 'Review' 
			) );
		}
		$ReportForm->getResponse ()->setContent ( Json::encode ( array (
				'reviewmodel' => $ReviewModel,
				'messages' => $messages,
				'id' => $id,
				'username' => $username,
				'valid' => $valid 
		) ) );
		return $ReportForm->getResponse ();
	}
	
	/**
	 * Form ViewModel
	 *
	 * @return ViewModel
	 */
	private function ViewModel($action = null, $id = null) {
		switch ($action) {
			case 'Filter' :
				$ViewModel = new ViewModel ();
				$ViewModel->setTemplate ( 'share/report/filter' );
				$ViewModel->setVariables ( array (
						'filter' => $this->InputForm ( $action, $id ) 
				) );
				return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
				break;
			case 'Review' :
				$ViewModel = new ViewModel ();
				$ViewModel->setTemplate ( $this->convertToForwardSlash ( 'report/' . $this->module . '/controller/action/result', '_' ) );
				$ViewModel->setVariables ( array (
						'details' => $this->ReviewData ( $id ) 
				) );
				return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ViewModel );
				break;
			default :
				return $this->InputForm ( $action, $id );
				break;
		}
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm($action = null, $id = null) {
		if (! $this->isAdminUser ()) {
			$id = $this->getUserID ();
		}
		if (! array_key_exists ( $action, $this->inputform )) {
			$this->inputform [$action] = null;
		}
		if ((! is_object ( $this->inputform [$action] ) && empty ( $this->inputform [$action] )) || ! empty ( $id )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => ucfirst ( $action ) 
			);
			if ($id > 0) {
				if ($this->getUserManagement ()->verifyID ( $id )) {
					$data = $this->getUserManagement ()->getData ( $id );
					if (count ( $data ) > 0) {
						$options ['datavalues'] = $data;
					}
				}
			}
			$datetime = new \DateTime ();
			$today_date = $datetime->format ( '00:00:00 d-m-Y' );
			if (array_key_exists ( 'datavalues', $options ) && count ( $options ['datavalues'] ) > 0) {
				$options ['datavalues'] ['filter_date_from'] = $today_date;
				$options ['datavalues'] ['filter_date_to'] = $today_date;
			} else {
				$options ['datavalues'] = array (
						'filter_date_from' => $today_date,
						'filter_date_to' => $today_date 
				);
			}
			$this->inputform [$action] = new UserReportForm ( $options );
		}
		return $this->inputform [$action];
	}
	
	/**
	 * Review Data
	 *
	 * @return Array
	 */
	private function ReviewData($id = null) {
		if (! $this->isAdminUser ()) {
			$id = $this->getUserID ();
		}
		$query = $this->query;
		$datetime = new \DateTime ();
		$date = $datetime->format ( 'Y-m-d' );
		$start_date = (is_array ( $query ) && array_key_exists ( 'start_date', $query ) ? $query ['start_date'] : $date);
		$end_date = (is_array ( $query ) && array_key_exists ( 'end_date', $query ) ? $query ['end_date'] : $date);
		$data = null;
		if ($id > 0) {
			$this->getUserStructure ()->setOption ( 'start_date', $start_date );
			$this->getUserStructure ()->setOption ( 'end_date', $end_date );
			$this->getUserStructure ()->setOption ( 'type', 'placement' );
			$this->getUserStructure ()->setOption ( 'user', $id );
			$data = $this->getUserStructure ()->getStructureReportData ();
		}
		return $data;
	}
}
	
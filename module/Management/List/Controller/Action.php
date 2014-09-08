<?php

namespace Management\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\Parameter\Parameter;
use Techfever\Nationality\Nationality;
use Techfever\User\Rank;

class ListActionController extends AbstractActionController {
	protected $rankgroup = 88888;
	protected $type = 'management';
	protected $module = 'list';
	protected $datatable = null;
	protected $datacolumn = null;
	protected $datasearch = null;
	public function IndexAction() {
		$this->datacolumn = $this->getDatatable ()->getColumnData ();
		$this->datasearch = $this->getDatatable ()->getSearchData ();
		$this->addCSS ( "vendor/Techfever/Javascript/jquery/themes/jtable/lightcolor/gray/jtable.css" );
		$this->addJavascript ( 'vendor/Techfever/Javascript/jquery/ui/jquery.jtable.js' );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME . "/Js/datatable.menu.js", array (
				'datatableid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Datatable', '/' ),
				'datalisturi' => $this->url ()->fromRoute ( ucfirst ( $this->type ) . '/List' ),
				'datacolumn' => $this->datacolumn,
				'datasearch' => $this->datasearch 
		) );
		
		return array (
				'menumodel' => $this->MenuModel () 
		);
	}
	private function getDatatable() {
		if (! is_object ( $this->datatable )) {
			$options = array (
					'route' => $this->getMatchedRouteName (),
					'action' => 'Index',
					'variable' => $this->getVariable (),
					'servicelocator' => $this->getServiceLocator () 
			);
			$this->datatable = new Datatable ( $options );
		}
		return $this->datatable;
	}
	public function GetAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'ua.user_access_id ASC';
			
			$routequery = ( string ) $this->params ()->fromRoute ( 'query', null );
			$routequery = (substr ( $routequery, 0, 1 ) == '/' ? substr ( $routequery, 1 ) : $routequery);
			$routequery = explode ( '&', $routequery );
			if (is_array ( $routequery ) && count ( $routequery ) > 0) {
				foreach ( $routequery as $routequeryvalue ) {
					$routequeryraw = explode ( '=', $routequeryvalue );
					if ($routequeryraw [0] === "jtStartIndex") {
						$index = $routequeryraw [1];
					} elseif ($routequeryraw [0] === "jtPageSize") {
						$perpage = $routequeryraw [1];
					} elseif ($routequeryraw [0] === "jtSorting") {
						$order = $routequeryraw [1];
					}
				}
			}
			
			$cache = $this->convertToUnderscore ( $order, ',' );
			$cache = $this->convertToUnderscore ( $cache, ',' );
			$cache = $this->convertToUnderscore ( $cache, ' ' );
			$cache = $this->rankgroup . '_' . $cache . '_' . $perpage . '_' . $index;
			if (strpos ( $order, ',' ) !== false) {
				$order = explode ( ',', $order );
			} else {
				$order = array (
						$order 
				);
			}
			if (is_array ( $order ) && count ( $order ) > 0) {
				$orderraw = array ();
				foreach ( $order as $ordervalue ) {
					$arrange = explode ( ' ', $ordervalue );
					$column = $this->getDatatable ()->getColumnFieldByColumn ( $arrange [0] );
					$table = $this->getDatatable ()->getColumnTableByColumn ( $arrange [0] );
					if ($table == 'user_access') {
						$orderraw [] = 'ua.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_profile') {
						$orderraw [] = 'up.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'user_hierarchy') {
						$orderraw [] = 'uh.' . $column . ' ' . $arrange [1];
					}
				}
				$order = $orderraw;
			}
			
			$search = $Request->getPost ()->toArray ();
			if (is_array ( $search ) && count ( $search ) > 0) {
				$searchraw = array ();
				foreach ( $search as $searchkey => $searchvalue ) {
					$column = $this->getDatatable ()->getSearchFieldByColumn ( $searchkey );
					$table = $this->getDatatable ()->getSearchTableByColumn ( $searchkey );
					$pattern = $this->getDatatable ()->getSearchPatternByColumn ( $searchkey );
					if ($table == 'user_access' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'ua.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					} elseif ($table == 'user_profile' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'up.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					} elseif ($table == 'user_hierarchy' && ! empty ( $searchvalue )) {
						$searchraw [$table] [] = 'uh.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
					}
				}
				$search = $searchraw;
			} else {
				$search = null;
			}
			
			$listdataraw = $this->getUserManagement ()->getListing ( $this->rankgroup, $search, $order, $index, $perpage, $cache, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = count ( $listdataraw );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	public function ManageAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$id = ( string ) $this->params ()->fromRoute ( 'query', null );
			$legend = array ();
			$legend ['address'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'address-book-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_address_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Address',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['bank'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'bank-book-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_bank_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Bank',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['password'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'password-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_password_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Password',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['security'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'security-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_security_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Security',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['permission'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'permission-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_permission_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Permission',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['profile'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'profile-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_profile_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Profile',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['status'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'status-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_status_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Status',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$legend ['username'] = $this->HrefLink ( array (
					'value' => $this->Img ( array (
							'folder' => 'Icons',
							'image' => 'username-24x24.png' 
					) ) . '<span>' . $this->getTranslate ( 'text_user_username_action' ) . '</span>',
					'route' => ucfirst ( $this->type ) . '/Username',
					'params' => array (
							'action' => 'Update',
							'crypt' => $id 
					) 
			) );
			$managedata [] = array (
					'legend' => '<div id="listlegend">' . implode ( '</div><div id="listlegend">', $legend ) . '</div>' 
			);
			
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $managedata;
			$jsonData ['TotalRecordCount'] = count ( $managedata );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	private function MenuModel() {
		$menu = array (
				array (
						'label' => $this->getTranslate ( 'text_search' ),
						'tab' => 'tabs-search',
						'istab' => True,
						'content' => $this->SearchModel () 
				),
				array (
						'label' => $this->getTranslate ( 'text_column' ),
						'tab' => 'tabs-column',
						'istab' => True,
						'content' => $this->ColumnModel () 
				),
				array (
						'label' => $this->getTranslate ( 'text_register' ),
						'tab' => 'tabs-register',
						'islink' => True,
						'href' => $this->url ()->fromRoute ( ucfirst ( $this->type ) . '/Register' ) 
				) 
		);
		$MenuModel = new ViewModel ();
		$MenuModel->setTerminal ( true );
		$MenuModel->setTemplate ( 'share/datatable/menu' );
		$MenuModel->setVariables ( array (
				'menu' => $menu 
		) );
		
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $MenuModel );
	}
	private function SearchModel() {
		$SearchSimpleModel = new ViewModel ();
		$SearchSimpleModel->setTerminal ( true );
		$SearchSimpleModel->setTemplate ( 'share/user/searchsimple' );
		$SearchSimpleModel->setVariables ( array (
				'search' => $this->datasearch 
		) );
		$SearchSimpleModel = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchSimpleModel );
		
		$SearchAdvanceModel = new ViewModel ();
		$SearchAdvanceModel->setTerminal ( true );
		$SearchAdvanceModel->setTemplate ( 'share/user/searchadvance' );
		$SearchAdvanceModel->setVariables ( array (
				'search' => $this->datasearch 
		) );
		$SearchAdvanceModel = $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchAdvanceModel );
		
		$SearchModel = new ViewModel ();
		$SearchModel->setTerminal ( true );
		$SearchModel->setTemplate ( 'share/datatable/menu/search' );
		$SearchModel->setVariables ( array (
				'simple' => $SearchSimpleModel,
				'advance' => $SearchAdvanceModel 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $SearchModel );
	}
	private function ColumnModel() {
		$ColumnModel = new ViewModel ();
		$ColumnModel->setTerminal ( true );
		$ColumnModel->setTemplate ( 'share/datatable/menu/column' );
		$ColumnModel->setVariables ( array (
				'column' => $this->datacolumn 
		) );
		return $this->getServiceLocator ()->get ( 'viewrenderer' )->render ( $ColumnModel );
	}
	private function getVariable() {
		$Status = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Status = $Status->toForm ();
		
		$Position = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Position = $Position->toForm ();
		
		$Gender = new Parameter ( array (
				'key' => 'user_profile_gender',
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Gender = $Gender->toForm ();
		
		$Designation = new Parameter ( array (
				'key' => 'user_profile_designation',
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Designation = $Designation->toForm ();
		
		$Nationality = new Nationality ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Nationality = $Nationality->countryToForm ();
		
		$Rank = new Rank ( array (
				'group' => $this->rankgroup,
				'servicelocator' => $this->getServiceLocator () 
		) );
		$Rank = $Rank->rankToForm ();
		return array (
				'rankgroup' => $this->rankgroup,
				'user_rank_text' => $Rank,
				'user_access_status_text' => $Status,
				'user_hierarchy_placement_position_text' => $Position,
				'user_profile_designation_text' => $Designation,
				'user_profile_gender_text' => $Gender,
				'user_profile_nationality_text' => $Nationality 
		);
	}
}

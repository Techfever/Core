<?php

namespace Mt4\Listing\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\Parameter\Parameter;
use Techfever\Mt4\Management as MT4Management;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'mt4';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'listing';
	
	/**
	 *
	 * @var Datatable
	 *
	 */
	protected $datatable = null;
	
	/**
	 *
	 * @var Variables
	 *
	 */
	protected $variable = null;
	
	/**
	 *
	 * @var MT4 Management Obj
	 *     
	 */
	protected $mt4Management = null;
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->addJavascript ( 'vendor/Techfever/Javascript/datatable/jquery.jtable.js' );
		$this->addJavascript ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Js/datatable.menu.js", array (
				'datatableformid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Index', '/' ),
				'datatableformuri' => $this->url ()->fromRoute ( $this->getMatchedRouteName () ),
				'datatableid' => $this->convertToUnderscore ( $this->getMatchedRouteName () . '/Datatable', '/' ),
				'datacolumn' => $this->getDatatable ()->getColumnData () 
		) );
		
		return array (
				'menumodel' => $this->getDatatable () 
		);
	}
	
	/**
	 * Get Action
	 *
	 * @return Json Response
	 */
	public function GetAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		$StatusParameter = new Parameter ( array (
				'key' => 'user_access_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'ua.user_access_id ASC';
			
			$routequery = ( string ) $_SERVER ['QUERY_STRING'];
			// $routequery = ( string ) $this->params ()->fromRoute ( 'query', null );
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
					} elseif ($table == 'user_mt4') {
						$orderraw [] = 'mt.' . $column . ' ' . $arrange [1];
					}
				}
				$order = $orderraw;
			}
			
			$search = $Request->getPost ()->toArray ();
			if (is_array ( $search ) && count ( $search ) > 0) {
				$searchraw = array ();
				foreach ( $search as $searchkey => $searchvalue ) {
					$is_date = false;
					if (is_array ( $searchvalue )) {
						if (array_key_exists ( 'day', $searchvalue )) {
							$date_day = $searchvalue ['day'];
							if (! empty ( $date_day )) {
								$date_day = $searchvalue ['day'];
							}
						}
						if (array_key_exists ( 'month', $searchvalue )) {
							$date_month = $searchvalue ['month'];
							if (! empty ( $date_month )) {
								$date_month = $searchvalue ['month'] . "-";
							}
						}
						if (array_key_exists ( 'year', $searchvalue )) {
							$date_year = $searchvalue ['year'];
							if (! empty ( $date_year )) {
								$date_year = $searchvalue ['year'] . "-";
							}
						}
						$searchvalue = $date_year . $date_month . $date_day;
						if (! empty ( $searchvalue )) {
							$is_date = true;
						}
					}
					$column = $this->getDatatable ()->getSearchFieldByColumn ( $searchkey );
					$table = $this->getDatatable ()->getSearchTableByColumn ( $searchkey );
					$pattern = $this->getDatatable ()->getSearchPatternByColumn ( $searchkey );
					$tablealias = "";
					if ($table == 'user_access') {
						if ($column == 'user_access_status') {
							if ($StatusParameter->hasResult ()) {
								$searchvalue = $StatusParameter->getValueByKey ( $searchvalue );
							}
						}
						$tablealias = "ua";
					} elseif ($table == 'user_mt4') {
						$tablealias = "mt";
					}
					if (! empty ( $searchvalue ) && ! empty ( $tablealias ) && ! empty ( $column )) {
						if ($is_date) {
							$searchraw [$table] [] = 'date(' . $tablealias . '.' . $column . ') ' . sprintf ( $pattern, $searchvalue );
						} else {
							$searchraw [$table] [] = $tablealias . '.' . $column . ' ' . sprintf ( $pattern, $searchvalue );
						}
					}
				}
				$search = $searchraw;
			} else {
				$search = null;
			}
			$listdataraw = $this->getMT4Management ()->getListing ( $search, $order, $index, $perpage, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = $this->getMT4Management ()->getListingTotal ( $search, true );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	
	/**
	 * Manage Action
	 *
	 * @return Json Response
	 */
	public function ManageAction() {
		$this->layout ( 'blank/layout' );
		$Request = $this->getRequest ();
		$Response = $this->getResponse ();
		
		if ($Request->isXmlHttpRequest ()) {
			$id = ( string ) $this->params ()->fromRoute ( 'query', null );
			$legend = array ();
			$managedata = array ();
			/*
			 * $legend ['preview'] = $this->HrefLink ( array ( 'value' => $this->Img ( array ( 'folder' => 'Icons', 'image' => 'address-book-24x24.png' ) ) . '<span>' . $this->getTranslate ( 'text_mt4_preview_action' ) . '</span>', 'route' => ucfirst ( $this->type ) . '/Preview', 'params' => array ( 'action' => 'Preview', 'crypt' => $id ) ) ); $managedata [] = array ( 'legend' => '<div id="listlegend">' . implode ( '</div><div id="listlegend">', $legend ) . '</div>' );
			 */
			
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $managedata;
			$jsonData ['TotalRecordCount'] = count ( $managedata );
			$Response->setContent ( Json::encode ( $jsonData ) );
		}
		return $Response;
	}
	
	/**
	 * Get Variable
	 *
	 * @return array
	 */
	private function getVariables() {
		if (! is_array ( $this->variable ) && empty ( $this->variable )) {
			$Status = $this->getUserWallet ()->StatusToForm ();
			$Transaction = $this->getUserWallet ()->TransactionToForm ();
			$Type_From = $this->getUserWallet ()->TypeToForm ();
			$Type_To = $this->getUserWallet ()->TypeToForm ();
			
			$this->variable = array (
					'wallet_status_id' => $Status,
					'wallet_transaction_id' => $Transaction,
					'wallet_type_id_from' => $Type_From,
					'wallet_type_id_to' => $Type_To 
			);
		}
		return $this->variable;
	}
	
	/**
	 * Get Datatable
	 *
	 * @return datatable
	 */
	private function getDatatable() {
		if (! is_object ( $this->datatable )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'action' => 'Index',
					'variable' => $this->getVariables () 
			);
			$this->datatable = new Datatable ( $options );
		}
		return $this->datatable;
	}
	
	/**
	 * Get MT4 Management
	 *
	 * @return mt4management
	 */
	private function getMT4Management() {
		if (! isset ( $this->mt4Management )) {
			$options = array (
					'servicelocator' => $this->getServiceLocator () 
			);
			$this->mt4Management = new MT4Management ( $options );
		}
		return $this->mt4Management;
	}
}

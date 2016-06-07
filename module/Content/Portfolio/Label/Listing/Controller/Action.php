<?php

namespace Content\Portfolio\Label\Listing\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Datatable\Datatable;
use Techfever\Content\Label as ContentLabelManagement;
use Techfever\Parameter\Parameter;

class ActionController extends AbstractActionController {
	
	/**
	 *
	 * @var Portfolio Type
	 *     
	 */
	protected $contenttype = '9000';
	
	/**
	 *
	 * @var Type
	 *
	 */
	protected $type = 'portfolio';
	
	/**
	 *
	 * @var Module
	 *
	 */
	protected $module = 'label_listing';
	
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
	 * @var Portfolio Object
	 *     
	 */
	private $labelobject = null;
	
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
		
		$PublishParameter = new Parameter ( array (
				'key' => 'content_label_publish_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		$LoginParameter = new Parameter ( array (
				'key' => 'content_label_login_status',
				'servicelocator' => $this->getServiceLocator () 
		) );
		
		if ($Request->isXmlHttpRequest ()) {
			$index = 0;
			$perpage = 10;
			$order = 'cd.content_label_id ASC';
			
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
					$ordervalue = str_replace ( "%20", " ", $ordervalue );
					$arrange = explode ( ' ', $ordervalue );
					$column = $this->getDatatable ()->getColumnFieldByColumn ( $arrange [0] );
					$table = $this->getDatatable ()->getColumnTableByColumn ( $arrange [0] );
					if ($table == 'content_label') {
						$orderraw [] = 'cd.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'content_label_detail') {
						$orderraw [] = 'cdd.' . $column . ' ' . $arrange [1];
					} elseif ($table == 'content_label_url') {
						$orderraw [] = 'cdu.' . $column . ' ' . $arrange [1];
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
					if ($table == 'content_label') {
						if ($column == 'content_label_publish_status') {
							if ($PublishParameter->hasResult ()) {
								$searchvalue = $PublishParameter->getValueByKey ( $searchvalue );
							}
						}
						if ($column == 'content_label_login_status') {
							if ($LoginParameter->hasResult ()) {
								$searchvalue = $LoginParameter->getValueByKey ( $searchvalue );
							}
						}
						$tablealias = "cd";
					} elseif ($table == 'content_label_detail') {
						$tablealias = "cdd";
					} elseif ($table == 'content_label_url') {
						$tablealias = "cdu";
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
			if (! $this->isAdminUser ()) {
				$search ['content_label'] [] = 'cd.user_access_id = "' . $this->getUserID () . '"';
				$this->getLabelObject ()->setContentUserID ( $this->getUserID () );
			}
			$listdataraw = $this->getLabelObject ()->getLabelListing ( $search, $order, $index, $perpage, true );
			$jsonData = array ();
			$jsonData ['Result'] = "OK";
			$jsonData ['Records'] = $listdataraw;
			$jsonData ['TotalRecordCount'] = $this->getLabelObject ()->getLabelListingTotal ( $search, true );
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
			if ($this->getLabelObject ()->verifyLabelID ()) {
				$legend ['edit'] = $this->HrefLink ( array (
						'value' => $this->Img ( array (
								'folder' => 'Icons',
								'image' => 'edit-24x24.png' 
						) ) . '<span>' . $this->getTranslate ( 'text_edit_action' ) . '</span>',
						'route' => ucfirst ( $this->type ) . '/Label/Edit',
						'params' => array (
								'action' => 'Update',
								'crypt' => $id 
						) 
				) );
				if (! $this->getLabelObject ()->isLabelFixed ()) {
					$legend ['delete'] = $this->HrefLink ( array (
							'value' => $this->Img ( array (
									'folder' => 'Icons',
									'image' => 'delete-24x24.png' 
							) ) . '<span>' . $this->getTranslate ( 'text_delete_action' ) . '</span>',
							'route' => ucfirst ( $this->type ) . '/Label/Delete',
							'params' => array (
									'action' => 'Update',
									'crypt' => $id 
							) 
					) );
				}
				$legend ['preview'] = $this->HrefLink ( array (
						'value' => $this->Img ( array (
								'folder' => 'Icons',
								'image' => 'preview-24x24.png' 
						) ) . '<span>' . $this->getTranslate ( 'text_preview_action' ) . '</span>',
						'route' => ucfirst ( $this->type ) . '/Label/Preview',
						'params' => array (
								'action' => 'Show',
								'crypt' => $id 
						) 
				) );
			}
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
	
	/**
	 * Get Variable
	 *
	 * @return array
	 */
	private function getVariables() {
		if (! is_array ( $this->variable ) && empty ( $this->variable )) {
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

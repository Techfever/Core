<?php

namespace Image\Controller;

use Techfever\Functions\DirConvert;
use Techfever\Template\Plugin\AbstractActionController;
use Zend\View\Model\ViewModel;
use DateTime;
use DateInterval;
use Zend\Json\Json;

class ActionController extends AbstractActionController {
	protected $_path = null;
	protected $_theme = null;
	protected $_expirddate = null;
	public function __construct() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
	}
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->layout ( 'blank/layout' );
	}
	public function DownloadAction() {
		$this->layout ( 'blank/layout' );
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = false;
		$valid = false;
		$screenshot = $request->getPost ( "screenshot" );
		if ($request->isXmlHttpRequest ()) {
			$success = true;
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid 
		) ) );
		return $response;
	}
	public function UploadAction() {
		$this->layout ( 'blank/layout' );
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		$success = false;
		$valid = false;
		$data = $request->getPost ( "data" );
		$screenshot = $request->getPost ( "screenshot" );
		$filename = $request->getPost ( "filename" );
		$file = "";
		if ($request->isXmlHttpRequest ()) {
			$success = true;
			$filepath = null;
			if ($screenshot == true) {
				$filepath = 'data/screenshot/';
				$DirConvert = new DirConvert ( CORE_PATH . '/' . $filepath );
				$filepath = $DirConvert->__toString ();
				$file = $this->Encrypt ( $this->getUserID () ) . '-' . $filename . '.png';
				
				if (file_exists ( $filepath . $file )) {
					unlink ( $filepath . $file );
				}
				// remove "data:image/png;base64,"
				$uri = substr ( $data, strpos ( $data, "," ) + 1 );
				// save to file
				file_put_contents ( $filepath . $file, base64_decode ( $uri ) );
				if (file_exists ( $filepath . $file )) {
					$valid = true;
				}
			}
		} else {
			return $this->redirect ()->toRoute ( 'Index' );
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'valid' => $valid,
				'file' => $file 
		) ) );
		return $response;
	}
	public function DisplayAction() {
		$this->layout ( 'blank/layout' );
		
		$this->type = ( string ) $this->params ()->fromRoute ( 'type', null );
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		$filepath = null;
		if ($this->type == "screenshot") {
			$filepath = 'data/screenshot/' . $this->path;
		} else {
			$filepath = 'data/document/photo/' . $this->path;
		}
		$DirConvert = new DirConvert ( CORE_PATH . '/' . $filepath );
		$filepath = $DirConvert->__toString ();
		$viewModel = new ViewModel ();
		$viewModel->setTemplate ( 'image/controller/action/index' );
		$viewModel->setVariable ( 'image', $filepath );
		$viewModel->setVariable ( 'expire', $this->_expirddate );
		return $viewModel;
	}
	public function CaptchaAction() {
		$this->layout ( 'blank/layout' );
		
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		$filepath = null;
		if (! empty ( $this->path )) {
			$filepath = 'data/captcha/' . $this->path;
		}
		$DirConvert = new DirConvert ( CORE_PATH . '/' . $filepath );
		$filepath = $DirConvert->__toString ();
		$viewModel = new ViewModel ();
		$viewModel->setTemplate ( 'image/controller/action/index' );
		$viewModel->setVariable ( 'image', $filepath );
		$viewModel->setVariable ( 'expire', $this->_expirddate );
		return $viewModel;
	}
}

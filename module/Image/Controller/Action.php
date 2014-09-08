<?php

namespace Image\Controller;

use Techfever\Functions\DirConvert;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DateTime;
use DateInterval;

class ActionController extends AbstractActionController {
	protected $_path = null;
	protected $_theme = null;
	protected $_expirddate = null;
	public function __construct() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
	}
	public function IndexAction() {
		$this->layout ( 'blank/layout' );
	}
	public function CaptchaAction() {
		$this->layout ( 'blank/layout' );
		
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		$contenttype = null;
		$filepath = null;
		if (! empty ( $this->path )) {
			$filepath = 'data/captcha/' . $this->path;
		}
		$DirConvert = new DirConvert ( CORE_PATH . '/' . $filepath );
		$filepath = $DirConvert->__toString ();
		$viewModel = new ViewModel ();
		$viewModel->setTemplate ( 'image/action/index' );
		$viewModel->setVariable ( 'image', $filepath );
		$viewModel->setVariable ( 'expire', $this->_expirddate );
		return $viewModel;
	}
}

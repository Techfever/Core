<?php

namespace Theme\Controller;

use Techfever\Functions\DirConvert;
use Zend\Mvc\Controller\AbstractActionController;
use DateTime;
use DateInterval;
use Zend\Session\Container as SessionContainer;

class GetActionController extends AbstractActionController {
	protected $_path = null;
	protected $_theme = null;
	protected $_expirddate = null;
	protected $_cache = null;
	public function __construct() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
		$this->_container = new SessionContainer ( 'Template' );
	}
	public function indexAction() {
		$this->layout ( 'blank/layout' );
	}
	public function CSSAction() {
		$this->layout ( 'blank/layout' );
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$rawcss = array ();
		if (! empty ( $this->path )) {
			$rawcss ['vendor/Techfever/' . $this->path] = True;
		} elseif ($this->_container->offsetExists ( 'CSS' )) {
			$rawcss = $this->_container->offsetGet ( 'CSS' );
		}
		$css = array ();
		if (count ( $rawcss ) > 0) {
			foreach ( $rawcss as $css_key => $css_value ) {
				$DirConvert = new DirConvert ( $css_value );
				$filepath = $DirConvert->__toString ();
				$css [$css_key] = $filepath;
			}
		}
		$this->_container->offsetUnset ( 'CSS' );
		return array (
				'css' => $css,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
	public function ImageAction() {
		$this->layout ( 'blank/layout' );
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$contenttype = null;
		$filepath = null;
		if (! empty ( $this->path )) {
			if (strpos ( $this->path, '/' ) !== false) {
				$pathraw = explode ( '/', $this->path );
				if (is_array ( $pathraw ) && count ( $pathraw ) > 2 && $pathraw [0] == 'Jquery') {
					$pathraw = array_splice ( $pathraw, 1, count ( $pathraw ) );
					$totalpathraw = count ( $pathraw );
					$theme = null;
					$image = null;
					if ($totalpathraw > 2) {
						$image = $pathraw [($totalpathraw - 1)];
						$theme = implode ( '/', array_splice ( $pathraw, 0, ($totalpathraw - 1) ) );
					} else {
						$theme = $pathraw [0];
						$image = $pathraw [1];
					}
					$filepath = 'vendor/Techfever/Javascript/jquery/themes/' . $theme . '/images/' . $image;
				}
			}
			if (empty ( $filepath )) {
				$filepath = 'vendor/Techfever/Theme/' . SYSTEM_THEME . '/Image/' . $this->path;
			}
		}
		$DirConvert = new DirConvert ( $filepath );
		$filepath = $DirConvert->__toString ();
		return array (
				'image' => $filepath,
				'expire' => $this->_expirddate 
		);
	}
	public function JavascriptAction() {
		$this->layout ( 'blank/layout' );
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$rawjavascript = array ();
		if (! empty ( $this->path )) {
			$rawjavascript ['vendor/Techfever/' . $this->path] = True;
		} elseif ($this->_container->offsetExists ( 'Javascript' )) {
			$rawjavascript = $this->_container->offsetGet ( 'Javascript' );
		}
		$javascript = array ();
		if (count ( $rawjavascript ) > 0) {
			foreach ( $rawjavascript as $javascript_key => $javascript_value ) {
				$DirConvert = new DirConvert ( $javascript_key );
				$filepath = $DirConvert->__toString ();
				$javascript [str_replace ( "\\", "\\\\", $filepath )] = $javascript_value;
			}
		}
		$this->_container->offsetUnset ( 'Javascript' );
		return array (
				'javascript' => $javascript,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
	public function HTCAction() {
		$this->layout ( 'blank/layout' );
		$this->path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$contenttype = null;
		$filepath = null;
		if (! empty ( $this->path )) {
			$DirConvert = new DirConvert ( 'vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/' . $this->path );
			$filepath = $DirConvert->__toString ();
		}
		
		return array (
				'htc' => $filepath,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
}

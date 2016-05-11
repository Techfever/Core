<?php

namespace Theme\Get\Controller;

use Techfever\Functions\DirConvert;
use Techfever\Template\Plugin\AbstractActionController;
use DateTime;
use DateInterval;

class ActionController extends AbstractActionController {
	protected $_referral = null;
	protected $_path = null;
	protected $_expirddate = null;
	protected $_cache = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->layout ( 'blank/layout' );
	}
	public function ImageAction() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
		
		$this->layout ( 'blank/layout' );
		$this->_path = ( string ) $this->params ()->fromRoute ( 'path', null );
		$contenttype = null;
		$filepath = null;
		if (! empty ( $this->_path )) {
			if (strpos ( $this->_path, '/' ) !== false) {
				$pathraw = explode ( '/', $this->_path );
				if (is_array ( $pathraw ) && count ( $pathraw ) > 2) {
					if (strtolower ( $pathraw [0] ) == 'jquery') {
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
					} elseif (strtolower ( $pathraw [0] ) == 'backend') {
						$pathraw = array_splice ( $pathraw, 1, count ( $pathraw ) );
						$image = implode ( '/', $pathraw );
						$filepath = "vendor/Techfever/Theme/Backend/Image/" . $image;
					}
				}
			}
			if (empty ( $filepath )) {
				$filepath = "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/Image/" . $this->_path;
			}
		}
		$DirConvert = new DirConvert ( $filepath );
		$filepath = $DirConvert->__toString ();
		return array (
				'image' => $filepath,
				'expire' => $this->_expirddate 
		);
	}
	public function CSSAction() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
		
		$this->layout ( 'blank/layout' );
		$this->_referral = ( string ) $this->params ()->fromRoute ( 'referral', null );
		$this->_path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$Template = $this->getTemplate ();
		$Session = $Template->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		
		$isDepend = false;
		$rawcss = array ();
		if (! empty ( $this->_path )) {
			$isDepend = true;
			$rawcss ['vendor/Techfever/' . $this->_path] = True;
		} elseif ($Container->offsetExists ( 'CSS' )) {
			$rawcss = $Container->offsetGet ( 'CSS' );
			$Container->offsetUnset ( 'CSS' );
		}
		$css = array ();
		if (count ( $rawcss ) > 0) {
			foreach ( $rawcss as $css_key => $css_value ) {
				$DirConvert = new DirConvert ( $css_value );
				$filepath = $DirConvert->__toString ();
				$css [$css_key] = $filepath;
			}
		}
		$key = 'CSS/' . $this->Decrypt ( $this->_referral, false ) . '/' . ($isDepend ? $this->_path : null);
		$key = $this->Encrypt ( $key, false );
		return array (
				'key' => $key,
				'css' => $css,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
	public function JavascriptAction() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
		
		$this->layout ( 'blank/layout' );
		$this->_referral = ( string ) $this->params ()->fromRoute ( 'referral', null );
		$this->_path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$Template = $this->getTemplate ();
		$Session = $Template->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		
		$isDepend = false;
		$rawjavascript = array ();
		if (! empty ( $this->_path )) {
			$isDepend = true;
			$rawjavascript ['vendor/Techfever/' . $this->_path] = True;
		} elseif ($Container->offsetExists ( 'Javascript' )) {
			$rawjavascript = $Container->offsetGet ( 'Javascript' );
			$Container->offsetUnset ( 'Javascript' );
		}
		$javascript = array ();
		if (count ( $rawjavascript ) > 0) {
			foreach ( $rawjavascript as $javascript_key => $javascript_value ) {
				$DirConvert = new DirConvert ( $javascript_key );
				$filepath = $DirConvert->__toString ();
				$javascript [str_replace ( "\\", "\\\\", $filepath )] = $javascript_value;
			}
		}
		$key = 'Javascript/' . $this->Decrypt ( $this->_referral, false ) . '/' . ($isDepend ? $this->_path : null);
		$key = $this->Encrypt ( $key, false );
		return array (
				'key' => $key,
				'javascript' => $javascript,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
	public function HTCAction() {
		$Date = new DateTime ( 'NOW' );
		$Date->sub ( new DateInterval ( 'PT2M' ) );
		$this->_expirddate = $Date->format ( 'D, j M Y H:i:s e' );
		
		$this->InitSetting ();
		$this->layout ( 'blank/layout' );
		$this->_referral = ( string ) $this->params ()->fromRoute ( 'referral', null );
		$this->_path = ( string ) $this->params ()->fromRoute ( 'path', null );
		
		$contenttype = null;
		$filepath = null;
		if (! empty ( $this->_path )) {
			$DirConvert = new DirConvert ( "vendor/Techfever/Theme/" . SYSTEM_THEME_LOAD . "/CSS/" . $this->_path );
			$filepath = $DirConvert->__toString ();
		}
		$key = 'HTC/' . $this->Decrypt ( $this->_referral, false ) . '/' . $this->_path;
		$key = $this->Encrypt ( $key, false );
		return array (
				'key' => $key,
				'htc' => $filepath,
				'expire' => $this->_expirddate,
				'cache' => $this->getPageCache () 
		);
	}
}

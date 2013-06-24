<?php
namespace Theme\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Headers;
use DateTime;
use DateInterval;

class GetActionController extends AbstractActionController {
	protected $_path = null;

	protected $_theme = null;

	protected $_expirddate = null;

	public function __construct() {
		$Date = new DateTime('NOW');
		$Date->sub(new DateInterval('PT2M'));
		$this->_expirddate = $Date->format('D, j M Y H:i:s e');
	}
	public function indexAction() {
		$this->layout('blank/layout');
	}
	public function CSSAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$CSSConfig = $ThemeConfig['css'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$css = array();
		if (is_array($CSSConfig) && count($CSSConfig) > 0) {
			$css = $CSSConfig;
		}
		if (!empty($this->path)) {
			$css[] = 'Vendor/Techfever/' . $this->path;
			echo "\n";
		}

		return array(
			'css' => $css, 'expire' => $this->_expirddate
		);
	}
	public function ImageAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$contenttype = null;
		$filepath = null;
		if (!empty($this->path)) {
			$filepath = 'Vendor/Techfever/Theme/' . $this->_theme . '/Image/' . $this->path;
		}
		
		return array(
			'image' => $filepath, 'expire' => $this->_expirddate
		);
	}
	public function JavascriptAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$JavascriptConfig = $ThemeConfig['javascript'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$javascript = array();
		if (is_array($JavascriptConfig) && count($JavascriptConfig) > 0) {
			$javascript = $JavascriptConfig;
		}
		if (!empty($this->path)) {
			$javascript[] = 'Vendor/Techfever/' . $this->path;
			echo "\n";
		}

		$headers = new Headers();
		$headers->addHeaderLine('Cache-Control: no-cache, must-revalidate');
		$headers->addHeaderLine('Expires: ' . $this->_expirddate);
		$headers->addHeaderLine('Content-Type: application/x-javascript');
		$this->getRequest()->setHeaders($headers);
		return array(
			'javascript' => $javascript, 'expire' => $this->_expirddate
		);
	}
	public function HTCAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$contenttype = null;
		$filepath = null;
		if (!empty($this->path)) {
			$filepath = 'Vendor/Techfever/Theme/' . $this->_theme . '/CSS/' . $this->path;
		}
		
		return array(
			'htc' => $filepath, 'expire' => $this->_expirddate
		);
	}
}

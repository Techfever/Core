<?php
namespace Theme\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Headers;
use DateTime;
use DateInterval;
use Zend\Session\Container as SessionContainer;

class GetActionController extends AbstractActionController {
	protected $_path = null;

	protected $_theme = null;

	protected $_expirddate = null;

	public function __construct() {
		$Date = new DateTime('NOW');
		$Date->sub(new DateInterval('PT2M'));
		$this->_expirddate = $Date->format('D, j M Y H:i:s e');
		$this->_container = new SessionContainer('Template');
	}
	public function indexAction() {
		$this->layout('blank/layout');
	}
	public function CSSAction() {
		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$css = array();
		if (!empty($this->path)) {
			$css['Vendor/Techfever/' . $this->path] = True;
		} elseif ($this->_container->offsetExists('CSS')) {
			$css = $this->_container->offsetGet('CSS');
		}
		return array(
			'css' => $css, 'expire' => $this->_expirddate
		);
	}
	public function ImageAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$contenttype = null;
		$filepath = null;
		if (!empty($this->path)) {
			$filepath = 'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/Image/' . $this->path;
		}

		return array(
			'image' => $filepath, 'expire' => $this->_expirddate
		);
	}
	public function JavascriptAction() {
		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$javascript = array();
		if (!empty($this->path)) {
			$javascript['Vendor/Techfever/' . $this->path] = True;
		} elseif ($this->_container->offsetExists('Javascript')) {
			$javascript = $this->_container->offsetGet('Javascript');
		}
		return array(
			'javascript' => $javascript, 'expire' => $this->_expirddate
		);
	}
	public function HTCAction() {

		$this->layout('blank/layout');
		$this->path = (string) $this->params()->fromRoute('path', null);

		$contenttype = null;
		$filepath = null;
		if (!empty($this->path)) {
			$filepath = 'Vendor/Techfever/Theme/' . SYSTEM_THEME . '/CSS/' . $this->path;
		}

		return array(
			'htc' => $filepath, 'expire' => $this->_expirddate
		);
	}
}

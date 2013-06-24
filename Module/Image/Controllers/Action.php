<?php
namespace Image\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Headers;
use DateTime;
use DateInterval;

class ActionController extends AbstractActionController {
	protected $_path = null;

	protected $_theme = null;

	protected $_expirddate = null;

	public function __construct() {
		$Date = new DateTime('NOW');
		$Date->sub(new DateInterval('PT2M'));
		$this->_expirddate = $Date->format('D, j M Y H:i:s e');
	}
	public function IndexAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');
	}
	public function CaptchaAction() {
		$Config = $this->getServiceLocator()->get('Config');
		$ThemeConfig = $Config['theme'];
		$this->_theme = $ThemeConfig['default'];

		$this->layout('blank/layout');

		$this->path = (string) $this->params()->fromRoute('path', null);
		$contenttype = null;
		$filepath = null;
		if (!empty($this->path)) {
			$filepath = 'Data/Captcha/' . $this->path;
		}
		$viewModel = new ViewModel();
		$viewModel->setTemplate('image/action/index');
		$viewModel->setVariable('image', $filepath);
		$viewModel->setVariable('expire', $this->_expirddate);
		return $viewModel;
	}
}

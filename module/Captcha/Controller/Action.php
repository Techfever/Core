<?php

namespace Captcha\Controller;

use Techfever\Template\Plugin\AbstractActionController;

class ActionController extends AbstractActionController {
	protected $_path = null;
	public function __construct() {
	}
	public function RefreshAction() {
		$this->layout ( 'blank/layout' );
		$viewModel = null;
		return $viewModel;
	}
}

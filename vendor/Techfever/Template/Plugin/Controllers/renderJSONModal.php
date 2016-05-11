<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Json\Json;

class renderJSONModal extends AbstractPlugin {
	public function __invoke($variables = array()) {
		$Response = $this->getController ()->getServiceLocator ()->get ( 'response' );
		if (! is_array ( $variables ) || ! array_key_exists ( "title", $variables )) {
			$variables ['title'] = "";
		}
		if (! is_array ( $variables ) || ! array_key_exists ( "content", $variables )) {
			$variables ['content'] = "";
		}
		$Response->setContent ( Json::encode ( $variables ) );
		return $Response;
	}
}

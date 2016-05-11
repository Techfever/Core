<?php

namespace Language\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Locale;

class ActionController extends AbstractActionController {
	public function SwitchAction() {
		$success = false;
		$locale = ( string ) $this->params ()->fromRoute ( 'locale', null );
		$verify = $this->getServiceLocator ()->get ( 'translator' )->checkLocale ( $locale );
		if ($verify) {
			$Session = $this->getSession ();
			$Container = $Session->getContainer ( 'Translator' );
			$Container->offsetSet ( 'locale', $locale );
			
			Locale::setDefault ( $locale );
			$this->getServiceLocator ()->get ( 'translator' )->setLocale ( $locale );
			$success = true;
		}
		
		return $this->getSnapshot ()->redirect ();
	}
}

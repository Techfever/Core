<?php

namespace Techfever\Mt4\Form;

use Techfever\Form\Form as BaseForm;

class Defined extends BaseForm {
	public function getVariables() {
		$request = $this->getRequest ();
		return array ();
	}
}

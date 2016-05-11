<?php

namespace Account\Password\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Techfever\User\Form\Defined as UserUpdateForm;

class ActionController extends AbstractActionController {
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		if ($this->isXmlHttpRequest ()) {
			$InputForm = $this->InputForm ();
			if ($InputForm->isPost ()) {
				$this->setInput ( $InputForm->getPost ( 'input', null ) );
				$this->setPost ( true );
				if ($InputForm->isValid ()) {
					$this->setValid ( true );
					if ($InputForm->isSubmit ()) {
						$this->setSubmit ( true );
						$data = $InputForm->getData ();
						if ($this->getUserManagement ()->verifyID ( $this->getID () ) && $this->getUserManagement ()->updatePassword ( $this->getID (), $data )) {
							$this->setVerified ( true );
						}
					}
				} else {
					$this->setValidatorRelation ( $InputForm->getValidatorRelation ( $this->getInput () ) );
					$this->setMessages ( $InputForm->getMessages () );
					$this->setMessagesTotal ( $InputForm->getMessagesTotal () );
				}
			}
			$this->setContent ( $this->ViewModal ( array (
					'form' => $InputForm 
			),'share/form/input' ) );
			return $this->renderModal ();
		} else {
			$this->redirectHome ();
		}
	}
	
	/**
	 * Form Input
	 *
	 * @return Form
	 */
	protected function InputForm() {
		$this->setID ( $this->getUserID () );
		if (! $this->inputform instanceof \Zend\Form\FormInterface) {
			$id = $this->getID ();
			$rank_group = $this->getUserRankGroupID ();
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'rank' => $rank_group,
					'action' => 'Index' 
			);
			if ($this->getUserManagement ()->verifyID ( $id, $rank_group, null )) {
				$data = $this->getUserManagement ()->getData ( $id, $rank_group );
				if (count ( $data ) > 0) {
					$options ['datavalues'] = $data;
				}
			}
			$this->inputform = new UserUpdateForm ( $options );
		}
		return $this->inputform;
	}
}

<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;

class FormStepGroup extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'id' => true 
	);
	
	/**
	 * Render a form <div> element from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element) {
		$name = $element->getName ();
		if (empty ( $name ) && $name !== 0) {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		$translator = $this->getTranslator ();
		
		$firerender = $this->fireRender ( $element );
		
		if (! empty ( $firerender ['content'] )) {
			$show_preview_tab = $element->getAttribute ( 'show_preview_tab' );
			$show_finish_button = $element->getAttribute ( 'show_finish_button' );
			
			$rawstep = $firerender ['step'];
			$rawcontent = $firerender ['content'];
			$rawpreview = $firerender ['preview'];
			
			$step = null;
			if (is_array ( $rawstep ) && count ( $rawstep ) > 0) {
				if ($show_preview_tab == "True") {
					$rawstep [] = '			<div class="ui-step-tabcontrol ui-state-default ui-corner-all" id="' . $name . '_preview">' . $translator->translate ( 'text_preview' ) . '</div>' . "\n";
				}
				foreach ( $rawstep as $rawstep_value ) {
					$step .= $rawstep_value;
				}
			}
			$step_content = sprintf ( '		<div class="ui-step-tab">' . "\n" . "\n" . '%s		</div>' . "\n", $step ) . "\n";
			
			$preview = null;
			if (is_array ( $rawpreview ) && count ( $rawpreview ) > 0) {
				foreach ( $rawpreview as $preview_value ) {
					$preview .= $preview_value;
				}
			}
			
			$content = null;
			if (is_array ( $rawcontent ) && count ( $rawcontent ) > 0) {
				if ($show_preview_tab == "True") {
					$rawcontent [] = '			<div class="ui-step-contentcontrol" id="' . $name . '_preview">' . "\n" . $preview . '				<div class="row"></div>' . "\n" . '			</div>' . "\n";
				}
				foreach ( $rawcontent as $rawcontent_value ) {
					$content .= $rawcontent_value;
				}
			}
			$step_content .= sprintf ( '		<div class="ui-step-content">' . "\n" . '%s		</div>', $content ) . "\n";
			
			return sprintf ( '	<div id="%s" class="ui-step">' . "\n" . '%s	</div>', $name, $step_content );
		} else {
			return $this;
		}
	}
	
	/**
	 * Fire Content for render content
	 *
	 * @return string
	 */
	public function fireRender(ElementInterface $element) {
		$content = array ();
		$step = array ();
		$preview = array ();
		$node = $element->getOption ( 'node' );
		$data = $this->getFormFactoryHelper ()->getFormData ();
		$form = $this->getFormFactoryHelper ()->getFormObj ();
		$elements = $this->getFormFactoryHelper ()->getFormElements ();
		if (count ( $elements ) > 0) {
			$button = array ();
			foreach ( $elements as $element_key => $element ) {
				$field = $form->get ( $element_key );
				if ($field instanceof \Zend\Form\ElementInterface) {
					$name = $field->getName ();
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getOption ( 'parent' );
					$previewElement = null;
					if ($node == $parent && $type == "step") {
						$element_call = call_user_func_array ( array (
								$this->getView (),
								'form' . ucfirst ( $type ) 
						), array (
								$element 
						) );
						$content [] = $element_call ['content'];
						$step [] = $element_call ['step'];
						
						if (! empty ( $element_call ['content'] )) {
							$preview [] = $element_call ['preview'];
							$previewElement = '				<div class="row" id="' . $name . '_seperator">' . "\n";
							$previewElement .= '					' . call_user_func_array ( array (
									$this->getView (),
									'formSeperator' 
							), array (
									$element 
							) ) . "\n";
							$previewElement .= '				</div>' . "\n";
							$preview [] = $previewElement;
						}
					}
				}
			}
		}
		array_pop ( $preview );
		return array (
				'content' => $content,
				'step' => $step,
				'preview' => $preview 
		);
	}
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string FormStep
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
}

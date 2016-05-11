<?php

namespace Techfever\Template\Plugin\Helpers;

class FormFactory extends AbstractFormHelper {
	
	/**
	 * Form
	 *
	 * @var Form null
	 */
	protected $form_obj = null;
	
	/**
	 * Invoke helper as functor
	 *
	 * @param Form|null $form        	
	 * @return string FormGroup
	 */
	public function __invoke($form = null) {
		$this->formobj = $form;
		
		return $this->render ();
	}
	
	/**
	 * Render a form element
	 *
	 * @return html
	 */
	public function render() {
		$content = null;
		$form = $this->getFormObj ();
		$elements = $this->getFormElements ();
		$form->setAttribute ( 'enctype', 'multipart/form-data' );
		$form->setAttribute ( 'class', 'ui-form' );
		$content .= $this->getView ()->form ()->openTag ( $form ) . "\n";
		$button = array ();
		if (count ( $elements ) > 0) {
			foreach ( $elements as $element_key => $element ) {
				$field = $form->get ( $element_key );
				if ($field instanceof \Zend\Form\ElementInterface) {
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getOption ( 'parent' );
					$disable_help = $field->getOption ( 'disable_help' );
					$name = $field->getName ();
					$isRequire = $field->getAttribute ( 'is_require' );
					$renderElement = null;
					$elementObj = null;
					$formElement = null;
					if ($parent == 0) {
						$isHidden = false;
						if ($type == 'hidden') {
							$isHidden = true;
						}
						$formElement = call_user_func_array ( array (
								$this->getView (),
								'form' . ucfirst ( $type ) 
						), array (
								$element 
						) );
						$messageTemplates = $form->getMessageTemplates ( $element->getName () );
						switch ($type) {
							case 'captcha' :
								$renderElement = '				<div class="row" type="input" id="' . $name . '">' . "\n";
								$renderElement .= '					<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabel' 
								), array (
										$element 
								) ) . '&nbsp;:' . ($isRequire ? '<span class="required">*</span>' : null) . '</div>' . "\n";
								$renderElement .= '					<div class="captcha value"' . (! $isHidden && count ( $messageTemplates ) > 0 ? ' title=\'* ' . implode ( "<br>* ", $messageTemplates ) . '\'' : null) . '>' . $formElement . "</div>\n";
								if (! $disable_help) {
									$renderElement .= '					<div class="help"></div>' . "\n";
								}
								$renderElement .= '				</div>' . "\n";
								break;
							case 'searchcontent' :
							case 'searchuser' :
								$renderElement = '				<div class="search">' . "\n";
								$renderElement .= '					' . $formElement . "\n";
								$renderElement .= '				</div>' . "\n";
								break;
							case 'button' :
								$button [] = $formElement;
								break;
							case 'checkboxgroup' :
								$renderElement = $formElement . "\n";
							case 'tab' :
							case 'step' :
							case 'reportfilter' :
								break;
							case 'group' :
							case 'tabgroup' :
							case 'stepgroup' :
							case 'reportfiltergroup' :
								$renderElement = '	' . $formElement . "\n";
								break;
							case 'hidden' :
								$renderElement = '				<div class="hidden" id="' . $name . '">' . "\n";
								$renderElement .= '					' . $formElement . "\n";
								$renderElement .= '				</div>' . "\n";
								break;
							case 'paragraph' :
								$renderElement = '				<div class="row" type="preview" id="' . $name . '">' . "\n";
								$renderElement .= '					<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabelDisplay' 
								), array (
										$element 
								) ) . '&nbsp;:</div>' . "\n";
								$renderElement .= '					<div class="value">' . $formElement . '</div>' . "\n";
								$renderElement .= '				</div>' . "\n";
								break;
							case 'seperator' :
								$renderElement = '				<div class="row" id="' . $name . '">' . "\n";
								$renderElement .= '					' . $formElement . "\n";
								$renderElement .= '				</div>' . "\n";
								break;
							case 'htmleditor' :
							case 'textarea' :
								$renderElement = '				<div class="row" type="input" id="' . $name . '">' . "\n";
								$renderElement .= '					<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabel' 
								), array (
										$element 
								) ) . '&nbsp;:' . ($isRequire ? '<span class="required">*</span>' : null) . '</div>' . "\n";
								$renderElement .= '					<div class="textarea">' . "\n";
								$renderElement .= '						<div class="value"' . (! $isHidden && count ( $messageTemplates ) > 0 ? ' title=\'* ' . implode ( "<br>* ", $messageTemplates ) . '\'' : null) . '>' . $formElement . "</div>\n";
								$renderElement .= '					</div>' . "\n";
								if (! $disable_help) {
									$renderElement .= '					<div class="help"></div>' . "\n";
								}
								$renderElement .= '				</div>' . "\n";
							default :
								$renderElement = '				<div class="row" type="input" id="' . $name . '">' . "\n";
								$renderElement .= '					<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabel' 
								), array (
										$element 
								) ) . '&nbsp;:' . ($isRequire ? '<span class="required">*</span>' : null) . '</div>' . "\n";
								$renderElement .= '					<div class="value"' . (! $isHidden && count ( $messageTemplates ) > 0 ? ' title=\'* ' . implode ( "<br>* ", $messageTemplates ) . '\'' : null) . '>' . $formElement . "</div>\n";
								if (! $disable_help) {
									$renderElement .= '					<div class="help"></div>' . "\n";
								}
								$renderElement .= '				</div>' . "\n";
								break;
						}
						$content .= $renderElement;
					}
				}
			}
		}
		if (is_array ( $button ) && count ( $button ) > 0) {
			$content .= '				<div class="button">' . "\n";
			foreach ( $button as $button_value ) {
				$content .= '					' . $button_value . "\n";
			}
			$content .= '				</div>' . "\n";
		}
		$content .= $this->getView ()->form ()->closeTag ( $form ) . "\n";
		return $content;
	}
	
	/**
	 * Get Form
	 *
	 * @return Form null
	 */
	public function getFormObj() {
		return $this->formobj;
	}
	
	/**
	 * Get Elements
	 *
	 * @return Form null
	 */
	public function getFormElements() {
		return $this->getFormObj ()->getElements ();
	}
	
	/**
	 * Get Form Data
	 *
	 * @return Form null
	 */
	public function getFormData() {
		return $this->getFormObj ()->getElementData ();
	}
}

<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;

class FormStep extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'name' => true,
			'id' => true,
			'class' => true,
			'href' => true 
	);
	
	/**
	 * Render a form content <div> element from the provided $element
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
		
		$escapeHtml = $this->getEscapeHtmlHelper ();
		
		$render = array (
				'step' => "",
				'content' => "",
				'preview' => "" 
		);
		
		$attributes_content ['class'] = 'ui-step-contentcontrol';
		$attributes_content ['id'] = $name;
		$content = $this->fireRender ( $element );
		if (! empty ( $content ['content'] )) {
			$content_data = $content ['content'];
			$content_data = sprintf ( '			<div %s>' . "\n" . '%s				<div class="row"></div>' . "\n" . '			</div>', $this->createAttributesString ( $attributes_content ), $content_data ) . "\n";

			$attributes_step ['id'] = $name;
			$attributes_step ['class'] = "ui-step-tabcontrol";
			$label = $element->getLabel ();
			if (empty ( $label )) {
				throw new Exception\DomainException ( sprintf ( '%s expects either label content as the second argument, ' . 'or that the element provided has a label attribute; neither found', __METHOD__ ) );
			}
			if (null !== ($translator = $this->getTranslator ())) {
				$label = $translator->translate ( $label, $this->getTranslatorTextDomain () );
			}
			$step_data = sprintf ( '				<div %s>%s</div>', $this->createAttributesString ( $attributes_step ), $escapeHtml ( $label ) ) . "\n";
			
			$preview_data = $content ['preview'];
			$render = array (
					'step' => $step_data,
					'content' => $content_data,
					'preview' => $preview_data 
			);
		}
		return $render;
	}
	
	/**
	 * Fire Content for render content
	 *
	 * @return string
	 */
	public function fireRender(ElementInterface $element) {
		$content = null;
		$previewContent = null;
		$node = $element->getOption ( 'node' );
		$data = $this->getFormFactoryHelper ()->getFormData ();
		$form = $this->getFormFactoryHelper ()->getFormObj ();
		$elements = $this->getFormFactoryHelper ()->getFormElements ();
		$button = array ();
		if (count ( $elements ) > 0) {
			$button = array ();
			foreach ( $elements as $element_key => $element ) {
				$field = $form->get ( $element_key );
				if ($field instanceof \Zend\Form\ElementInterface) {
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getOption ( 'parent' );
					$disable_help = $field->getOption ( 'disable_help' );
					$name = $field->getName ();
					$isRequire = $field->getAttribute ( 'is_require' );
					$renderElement = null;
					$renderPreviewElement = null;
					$formElement = null;
					if ($node == $parent) {
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
							case 'accordion' :
							case 'reportfilter' :
								break;
							case 'tabgroup' :
								$renderElement = $formElement ['content'];
								$renderPreviewElement = $formElement ['preview'];
								break;
							case 'stepgroup' :
							case 'accordiongroup' :
							case 'reportfiltergroup' :
							case 'group' :
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
								
								$renderPreviewElement = $renderElement;
								break;
							case 'seperator' :
								$renderElement = '				<div class="row" id="' . $name . '">' . "\n";
								$renderElement .= '					' . $formElement . "\n";
								$renderElement .= '				</div>' . "\n";
								
								$renderPreviewElement = $renderElement;
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
								
								$renderPreviewElement = '				<div class="row" type="preview" id="' . $name . '_preview">' . "\n";
								$renderPreviewElement .= '					<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabelDisplay' 
								), array (
										$element 
								) ) . '&nbsp;:</div>' . "\n";
								$renderPreviewElement .= '					<div class="value">N/A</div>' . "\n";
								$renderPreviewElement .= '				</div>' . "\n";
								break;
						}
						$content .= $renderElement;
						$previewContent .= $renderPreviewElement;
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
		
		return array (
				'content' => $content,
				'preview' => $previewContent 
		);
	}
	
	/**
	 * Invoke helper as functor
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

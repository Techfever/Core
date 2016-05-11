<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;

class FormTab extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'name' => true,
			'id' => true,
			'class' => true,
			'href' => true,
			'onclick' => true 
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
		$tab_data = null;
		$content_data = null;
		$tab_preview_data = null;
		$content_preview_data = null;
		
		$label = $element->getLabel ();
		if (empty ( $label )) {
			throw new Exception\DomainException ( sprintf ( '%s expects either label content as the second argument, ' . 'or that the element provided has a label attribute; neither found', __METHOD__ ) );
		}
		if (null !== ($translator = $this->getTranslator ())) {
			$label = $translator->translate ( $label, $this->getTranslatorTextDomain () );
		}
		
		$render = $this->fireRender ( $element );
		
		$islink = $element->getAttribute ( 'islink' );
		if ($islink) {
			$link = $element->getAttribute ( 'link' );
			if (method_exists ( $this->view, 'plugin' )) {
				$serverUrl = $this->view->plugin ( 'serverUrl' );
				$baseHref = $this->view->plugin ( 'baseHref' );
				$link = "window.location.replace('" . $baseHref () . ucfirst ( $link ) . "');";
			}
			$attributes_tab ['id'] = 'href';
			$attributes_tab ['class'] = $name;
			$attributes_tab ['onclick'] = $link;
			$attributes_tab ['href'] = '';
			$tab_data = sprintf ( '			<li id="href"><a %s>%s</a></li>', $this->createAttributesString ( $attributes_tab ), $escapeHtml ( $label ) ) . "\n";
		} else {
			$attributes_content ['id'] = $name;
			$content_data = sprintf ( '			<div %s>' . "\n" . '%s				<div class="row"></div>' . "\n" . '			</div>', $this->createAttributesString ( $attributes_content ), $render ['content'] ) . "\n";
			
			$attributes_content_preview ['id'] = $name . '_preview';
			$content_preview_data = sprintf ( '		<div %s>' . "\n" . '%s			<div class="row"></div>' . "\n" . '		</div>', $this->createAttributesString ( $attributes_content_preview ), $render ['preview'] ) . "\n";
			
			$attributes_tab ['class'] = $name;
			$attributes_tab ['href'] = '#' . $name;
			$tab_data = sprintf ( '			<li><a %s>%s</a></li>', $this->createAttributesString ( $attributes_tab ), $escapeHtml ( $label ) ) . "\n";
			
			$attributes_tab_preview ['class'] = $name . '_preview';
			$attributes_tab_preview ['href'] = '#' . $name . '_preview';
			$tab_preview_data = sprintf ( '			<li><a %s>%s</a></li>', $this->createAttributesString ( $attributes_tab_preview ), $escapeHtml ( $label ) ) . "\n";
		}
		$render = array (
				'tab' => $tab_data,
				'content' => $content_data,
				'tab_preview' => $tab_preview_data,
				'content_preview' => $content_preview_data 
		);
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
							case 'group' :
							case 'tabgroup' :
							case 'stepgroup' :
							case 'accordiongroup' :
							case 'reportfiltergroup' :
								$renderElement = '				' . $formElement . "\n";
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
								
								$renderPreviewElement = '			<div class="row" type="preview" id="' . $name . '_preview">' . "\n";
								$renderPreviewElement .= '				<div class="label">' . call_user_func_array ( array (
										$this->getView (),
										'formLabelDisplay' 
								), array (
										$element 
								) ) . '&nbsp;:</div>' . "\n";
								$renderPreviewElement .= '				<div class="value">N/A</div>' . "\n";
								$renderPreviewElement .= '			</div>' . "\n";
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
	 * @return string FormTab
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
}

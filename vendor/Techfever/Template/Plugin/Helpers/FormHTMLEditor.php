<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormHTMLEditor extends AbstractFormHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'autocomplete' => true,
			'autofocus' => true,
			'cols' => true,
			'dirname' => true,
			'disabled' => true,
			'form' => true,
			'maxlength' => true,
			'name' => true,
			'placeholder' => true,
			'readonly' => true,
			'required' => true,
			'rows' => true,
			'wrap' => true 
	);
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string FormTextarea
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
	
	/**
	 * Render a form <textarea> element from the provided $element
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
		
		$attributes = $element->getAttributes ();
		$attributes ['name'] = $name;
		$content = ( string ) $element->getValue ();
		$escapeHtml = $this->getEscapeHtmlHelper ();
		
		return sprintf ( '<textarea %s>%s</textarea>%s', $this->createAttributesString ( $attributes ), $escapeHtml ( $content ), $this->renderJS ( $element ) );
	}
	
	/**
	 * Render a js from the provided $element
	 *
	 * @param ElementInterface $element        	
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function renderJS(ElementInterface $element) {
		$name = $element->getName ();
		if (empty ( $name ) && $name !== 0) {
			throw new Exception\DomainException ( sprintf ( '%s requires that the element has an assigned name; none discovered', __METHOD__ ) );
		}
		$locale = $element->getOption ( 'locale' );
		
		$js_content = '<script type="text/javascript">
		$(document).ready(function() {
			tinymce.init({
				selector: "textarea[id=%s]",
				setup: function(editor){
					editor.on("keyup", function(e){
						$(this).contentDetailEvent({  
							locale : "%s",
						});
					});
				},
    		});
    	});
</script>';
		return sprintf ( $js_content, $name, strtolower ( $locale ) );
	}
}

<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;

class FormAccordionGroup extends AbstractFormHelper {
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
		
		$content = $this->fireRender ( $element );
		$accordion_inactive = $element->getAttribute ( 'accordion_inactive' );
		return sprintf ( '<div id="%s" class="wizardaccordion">' . "\n" . '%s' . "\n" . '	</div>
		<script type="text/javascript">
		$(document).ready(function() {	
			$( "div[id=%s]" ).accordion({      
				heightStyle: "content",
				collapsible: true,  
				active: %s
			});
    	});
		</script>', $name, $content, $name, ($accordion_inactive == True ? "false" : "0") );
	}
	
	/**
	 * Fire Content for render content
	 *
	 * @return string
	 */
	public function fireRender(ElementInterface $element) {
		$content = null;
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
					if ($node == $parent && $type == "accordion") {
						$content .= call_user_func_array ( array (
								$this->getView (),
								'form' . ucfirst ( $type ) 
						), array (
								$element 
						) );
					}
				}
			}
		}
		return $content;
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

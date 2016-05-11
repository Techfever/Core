<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\Form\ElementInterface;
use Techfever\Exception;

class FormTabGroup extends AbstractFormHelper {
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
		
		$firerender = $this->fireRender ( $element );
		
		$tab = $firerender ['tab'];
		$tab_content = sprintf ( '		<ul>' . "\n" . '%s		</ul>', $tab ) . "\n";
		
		$content = $firerender ['content'];
		$tab_content .= sprintf ( '%s', $content );
		
		$tab_preview = $firerender ['tab_preview'];
		$tab_content_preview = sprintf ( '		<ul>' . "\n" . '%s		</ul>', $tab_preview ) . "\n";
		
		$tab_preview_content = $firerender ['content_preview'];
		$tab_content_preview .= sprintf ( '%s', $tab_preview_content );
		
		$render = array (
				'content' => sprintf ( '<div %s class="wizardtab">' . "\n" . '%s	</div>
		<script type="text/javascript">
		$(document).ready(function() {
			$("div[id=%s]").tabs();
    	});
		</script>', 'id="' . $name . '"', $tab_content, $name ),
				'preview' => sprintf ( '<div %s>' . "\n" . '%s	</div>
		<script type="text/javascript">
		$(document).ready(function() {
			$("div[id=%s]").tabs();
    	});
		</script>', 'id="' . $name . '_preview"', $tab_content_preview, $name . '_preview' ) 
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
		$tab = null;
		$content_preview = null;
		$tab_preview = null;
		$node = $element->getOption ( 'node' );
		$data = $this->getFormFactoryHelper ()->getFormData ();
		$form = $this->getFormFactoryHelper ()->getFormObj ();
		$elements = $this->getFormFactoryHelper ()->getFormElements ();
		if (count ( $elements ) > 0) {
			$button = array ();
			foreach ( $elements as $element_key => $element ) {
				$field = $form->get ( $element_key );
				if ($field instanceof \Zend\Form\ElementInterface) {
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getOption ( 'parent' );
					if ($node == $parent && $type == "tab") {
						$element_call = call_user_func_array ( array (
								$this->getView (),
								'form' . ucfirst ( $type ) 
						), array (
								$element 
						) );
						$content .= $element_call ['content'];
						$tab .= $element_call ['tab'];
						$content_preview .= $element_call ['content_preview'];
						$tab_preview .= $element_call ['tab_preview'];
					}
				}
			}
		}
		return array (
				'content' => $content,
				'tab' => $tab,
				'content_preview' => $content_preview,
				'tab_preview' => $tab_preview 
		);
	}
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
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

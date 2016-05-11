<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Techfever\Exception;

class ViewTab extends AbstractViewHelper {
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
	 * Render a view content <div> element from the provided $element
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
		
		$attributes_content ['id'] = $name;
		$content = $this->fireRender ( $element );
		$content_data = sprintf ( '		<div %s>' . "\n" . '%s' . "\n" . '		</div>', $this->createAttributesString ( $attributes_content ), $content ) . "\n";
		
		$attributes_tab ['class'] = $name;
		$attributes_tab ['href'] = '#' . $name;
		$label = $element->getLabel ();
		if (empty ( $label )) {
			throw new Exception\DomainException ( sprintf ( '%s expects either label content as the second argument, ' . 'or that the element provided has a label attribute; neither found', __METHOD__ ) );
		}
		if (null !== ($translator = $this->getTranslator ())) {
			$label = $translator->translate ( $label, $this->getTranslatorTextDomain () );
		}
		$tab_data = sprintf ( '			<li><a %s>%s</a></li>', $this->createAttributesString ( $attributes_tab ), $escapeHtml ( $label ) ) . "\n";
		
		$render = array (
				'tab' => $tab_data,
				'content' => $content_data 
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
		$node = $element->getNode ();
		$data = $this->getViewFactoryHelper ()->getViewData ();
		$elements = $this->getViewFactoryHelper ()->getViewElements ();
		$view = $this->getViewFactoryHelper ()->getViewObj ();
		if (count ( $elements ) > 0) {
			$button = array ();
			foreach ( $elements as $element_key => $element ) {
				$field = $view->get ( $element_key );
				if ($field instanceof \Techfever\View\ElementInterface) {
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getParent ();
					if ($node == $parent) {
						$content .= '			' . call_user_func_array ( array (
								$this->getView (),
								'view' . ucfirst ( $type ) 
						), array (
								$element 
						) ) . "\n";
					}
				}
			}
		}
		return $content;
	}
	
	/**
	 * Invoke helper as functor
	 *
	 * @param ElementInterface|null $element        	
	 * @return string ViewTab
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
}

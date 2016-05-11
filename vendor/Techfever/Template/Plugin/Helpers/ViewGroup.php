<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Techfever\Exception;

class ViewGroup extends AbstractViewHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'name' => true,
			'id' => true,
			'class' => true 
	);
	
	/**
	 * Render a view <div> element from the provided $element
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
		$content = ( string ) $this->fireRender ( $element );
		
		return sprintf ( '<div %s>' . "\n" . '%s</div>' . "\n" . '<div style="clear: both;"></div>', $this->createAttributesString ( $attributes ), $content ) . "\n";
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
						$content .= "	" . call_user_func_array ( array (
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
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string ViewGroup
	 */
	public function __invoke(ElementInterface $element = null) {
		if (! $element) {
			return $this;
		}
		
		return $this->render ( $element );
	}
}

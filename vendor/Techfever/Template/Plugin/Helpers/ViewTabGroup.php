<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\ElementInterface;
use Techfever\Exception;

class ViewTabGroup extends AbstractViewHelper {
	/**
	 * Attributes valid for the input tag
	 *
	 * @var array
	 */
	protected $validTagAttributes = array (
			'id' => true 
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
		
		$firerender = $this->fireRender ( $element );
		
		$tab = $firerender ['tab'];
		$tab_content = sprintf ( '		<ul>' . "\n" . '%s		</ul>', $tab ) . "\n";
		
		$content = $firerender ['content'];
		$tab_content .= sprintf ( '%s', $content );
		
		return sprintf ( '<div id="%s">' . "\n" . '%s	</div>
		<script type="text/javascript">
		$(document).ready(function() {	
			$("div[id=%s]").tabs();
    	});
		</script>', $name, $tab_content, $name, $name, $name, $name, $name );
	}
	
	/**
	 * Fire Content for render content
	 *
	 * @return string
	 */
	public function fireRender(ElementInterface $element) {
		$content = null;
		$tab = null;
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
						if ($type == "tab") {
							$element_call = call_user_func_array ( array (
									$this->getView (),
									'view' . ucfirst ( $type ) 
							), array (
									$element 
							) );
							$content .= $element_call ['content'];
							$tab .= $element_call ['tab'];
						} else {
							$content .= call_user_func_array ( array (
									$this->getView (),
									'view' . ucfirst ( $type ) 
							), array (
									$element 
							) ) . "\n";
						}
					}
				}
			}
		}
		return array (
				'content' => $content,
				'tab' => $tab 
		);
	}
	
	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
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

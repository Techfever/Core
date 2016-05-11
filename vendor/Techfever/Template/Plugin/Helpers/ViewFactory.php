<?php

namespace Techfever\Template\Plugin\Helpers;

use Techfever\View\View as ViewObject;

class ViewFactory extends AbstractViewHelper {
	
	/**
	 * View
	 *
	 * @var View null
	 */
	protected $view_obj = null;
	
	/**
	 * Invoke helper as functor
	 *
	 * @param View|null $view        	
	 * @return string ViewGroup
	 */
	public function __invoke(ViewObject $view) {
		$this->viewobj = $view;
		
		return $this->render ();
	}
	
	/**
	 * Render a view element
	 *
	 * @return html
	 */
	public function render() {
		$renderElement = null;
		$view = $this->getViewObj ();
		$elements = $this->getViewElements ();
		$renderElement .= '<div class="preview">' . "\n";
		if (count ( $elements ) > 0) {
			foreach ( $elements as $element_key => $element ) {
				$field = $view->get ( $element_key );
				if ($field instanceof \Techfever\View\ElementInterface) {
					$type = $field->getAttribute ( 'type' );
					$parent = $field->getParent ();
					if ($parent == 0) {
						$renderElement .= call_user_func_array ( array (
								$this->getView (),
								'view' . ucfirst ( $type ) 
						), array (
								$element 
						) ) . "\n";
					}
				}
			}
		}
		$renderElement .= '</div>' . "\n";
		return $renderElement;
	}
	
	/**
	 * Get View
	 *
	 * @return View null
	 */
	public function getViewObj() {
		return $this->viewobj;
	}
	
	/**
	 * Get Elements
	 *
	 * @return View null
	 */
	public function getViewElements() {
		return $this->getViewObj ()->getElements ();
	}
	
	/**
	 * Get View Data
	 *
	 * @return View null
	 */
	public function getViewData() {
		return $this->getViewObj ()->getViewData ();
	}
}

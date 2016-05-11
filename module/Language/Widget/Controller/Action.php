<?php

namespace Language\Widget\Controller;

use Techfever\Widget\Controller\General;

class ActionController extends General {
	public function InitialAction() {
		$this->setControllerName ( __NAMESPACE__ );
		
		$layout_type = WIDGET_LANGUAGE_LAYOUT_TYPE;
		
		$Translator = $this->getTranslator ();
		$Locale = $Translator->getLocale ();
		$AllLocale = $Translator->getAllLocale ();
		
		$content = array ();
		if (is_array ( $AllLocale ) && count ( $AllLocale ) > 0) {
			foreach ( $AllLocale as $locale_value ) {
				if (count ( $AllLocale ) == 1) {
					if ($layout_type == "selector") {
						$content [$locale_value ['iso']] = $this->getTranslate ( 'text_language_' . strtolower ( $locale_value ['iso'] ) );
					} elseif ($layout_type == "navigator") {
						$content [$locale_value ['iso']] = array (
								'value' => '<span>' . $this->getTranslate ( 'text_language_' . strtolower ( $locale_value ['iso'] ) ) . '</span>',
								'route' => 'Language',
								'action' => 'Switch',
								'locale' => $locale_value ['iso'] 
						);
					}
				} elseif ($Locale !== $locale_value ['iso']) {
					if ($layout_type == "selector") {
						$content [$locale_value ['iso']] = $this->getTranslate ( 'text_language_' . strtolower ( $locale_value ['iso'] ) );
					} elseif ($layout_type == "navigator") {
						$content [$locale_value ['iso']] = array (
								'value' => '<span>' . $this->getTranslate ( 'text_language_' . strtolower ( $locale_value ['iso'] ) ) . '</span>',
								'route' => 'Language',
								'action' => 'Switch',
								'locale' => $locale_value ['iso'] 
						);
					}
				}
			}
			
			if ($layout_type == "selector") {
				$value = $content;
				$content = new \Zend\Form\Form ( 'language_swap' );
				$content->add ( array (
						'type' => 'Techfever\Template\Plugin\Forms\Selection',
						'name' => 'language_selector',
						'attributes' => array (
								'onchange' => '$(this).widgetLanguageOnchange(this);' 
						),
						'options' => array (
								'empty_option' => $this->getTranslate ( 'text_form_select' ),
								'label' => $this->getTranslate ( 'text_language' ),
								'value_options' => $value 
						) 
				) );
			}
		}
		
		$this->setContent ( array (
				'title' => $this->getTranslate ( 'text_widget_language' ),
				'type' => $layout_type,
				'content' => $content,
				'success' => True 
		) );
		$this->setSuccess ( True );
		
		return $this->getWidgetModel ( $this->getOptions () );
	}
}

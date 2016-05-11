<?php

namespace Techfever\Content\Label\Form;

use Techfever\Form\Form as BaseForm;
use Techfever\User\Rank;

class Defined extends BaseForm {
	public function getVariables() {
		$request = $this->getRequest ();
		
		$user = array (
				'0' => '-=' . $this->getTranslate ( 'text_not_listed' ) . '=-' 
		);
		$rank = array (
				'0' => '-=' . $this->getTranslate ( 'text_not_listed' ) . '=-' 
		);
		
		$RankObj = new Rank ( array (
				'servicelocator' => $this->getServiceLocator () 
		) );
		$rawrank = $RankObj->rankToForm ();
		$rank = array_merge ( $rank, $rawrank );
		
		$language = array ();
		$AllLocale = $this->getTranslator ()->getAllLocale ();
		$DefaultLocale = $this->getTranslator ()->getLocaleIDbyISO ( SYSTEM_DEFAULT_LOCALE );
		if (is_array ( $AllLocale ) && count ( $AllLocale ) > 0) {
			foreach ( $AllLocale as $locale_value ) {
				$language [] = $locale_value ['iso'];
			}
		}
		$variable = array (
				'user' => $user,
				'rank' => $rank,
				'language' => $language,
				'user_access_id' => $this->getOption ( 'user' ),
				'content_type_id' => $this->getOption ( 'type' ),
				'content_label_id' => $this->getOption ( 'label' ) 
		);
		return $variable;
	}
}

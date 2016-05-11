<?php

namespace Techfever\Document\Data\Form;

use Techfever\Form\Form as BaseForm;
use Techfever\User\Rank;

class Defined extends BaseForm {
	
	/**
	 *
	 * @var Document User
	 *     
	 */
	private $documentuser = null;
	
	/**
	 *
	 * @var Document Type
	 *     
	 */
	private $documenttype = null;
	
	/**
	 *
	 * @var Document Data
	 *     
	 */
	private $documentdata = null;
	
	/**
	 * Get Variable
	 *
	 * @return Array
	 */
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
		$this->documentuser = $this->getOption ( 'user' );
		$this->documenttype = $this->getOption ( 'type' );
		$this->documentdata = $this->getOption ( 'data' );
		$variable = array (
				'user' => $user,
				'rank' => $rank,
				'language' => $language,
				'user_access_id' => $this->documentuser,
				'document_type_id' => $this->documenttype,
				'document_data_id' => $this->documentdata 
		);
		return $variable;
	}
}

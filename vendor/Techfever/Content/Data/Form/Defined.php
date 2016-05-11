<?php

namespace Techfever\Content\Data\Form;

use Techfever\Form\Form as BaseForm;
use Techfever\User\Rank;
use Techfever\Content\Label as ContentLabelManagement;
use Techfever\Content\Tag as ContentTagManagement;

class Defined extends BaseForm {
	
	/**
	 *
	 * @var Content User
	 *     
	 */
	private $contentuser = null;
	
	/**
	 *
	 * @var Content Type
	 *     
	 */
	private $contenttype = null;
	
	/**
	 *
	 * @var Content Data
	 *     
	 */
	private $contentdata = null;
	
	/**
	 *
	 * @var Content Label Object
	 *     
	 */
	private $labelobject = null;
	
	/**
	 *
	 * @var Content Tag Object
	 *     
	 */
	private $tagobject = null;
	
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
		$this->contentuser = $this->getOption ( 'user' );
		$this->contenttype = $this->getOption ( 'type' );
		$this->contentdata = $this->getOption ( 'data' );
		
		$label_array = $this->getLabelObject ()->getLabelListing ( null, null, null, null, true );
		$label_data = array ();
		if (is_array ( $label_array ) && count ( $label_array ) > 0) {
			foreach ( $label_array as $label_value ) {
				$label_data [$label_value ['id']] = $label_value ['content_label_detail_title'];
			}
		} else {
			$cryptID = $this->Encrypt ( 0 );
			$label_data [$cryptID] = 'N/L';
		}
		
		$tag_array = $this->getTagObject ()->getTagListing ( null, null, null, null, true );
		$tag_data = array ();
		if (is_array ( $tag_array ) && count ( $tag_array ) > 0) {
			foreach ( $tag_array as $tag_value ) {
				$tag_data [$tag_value ['id']] = $tag_value ['content_tag_detail_title'];
			}
		} else {
			$cryptID = $this->Encrypt ( 0 );
			$tag_data [$cryptID] = 'N/L';
		}
		$variable = array (
				'user' => $user,
				'rank' => $rank,
				'language' => $language,
				'content_data_label' => $label_data,
				'content_data_tag' => $tag_data,
				'user_access_id' => $this->contentuser,
				'content_type_id' => $this->contenttype,
				'content_data_id' => $this->contentdata 
		);
		return $variable;
	}
	
	/**
	 * Get Content Label Object
	 *
	 * @return Object
	 */
	private function getLabelObject() {
		if (! is_object ( $this->labelobject ) && empty ( $this->labelobject )) {
			
			$user_id = $this->contentuser;
			$type_id = $this->contenttype;
			$language_id = $this->getTranslator ()->getLocaleID ();
			
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'user_id' => $user_id,
					'type_id' => $type_id,
					'language_id' => $language_id 
			);
			$this->labelobject = new ContentLabelManagement ( $options );
		}
		return $this->labelobject;
	}
	
	/**
	 * Get Content Tag Object
	 *
	 * @return Object
	 */
	private function getTagObject() {
		if (! is_object ( $this->tagobject ) && empty ( $this->tagobject )) {
			
			$user_id = $this->contentuser;
			$type_id = $this->contenttype;
			$language_id = $this->getTranslator ()->getLocaleID ();
			
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'user_id' => $user_id,
					'type_id' => $type_id,
					'language_id' => $language_id 
			);
			$this->tagobject = new ContentTagManagement ( $options );
		}
		return $this->tagobject;
	}
}

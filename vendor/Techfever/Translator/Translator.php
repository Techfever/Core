<?php

namespace Techfever\Translator;

use Traversable;
use Zend\Cache;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\I18n\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\I18n\Translator\TextDomain;
use Zend\I18n\Translator\Translator as BaseTranslator;
use Zend\Validator\Translator\TranslatorInterface;
use Techfever\Functions\General as GeneralBase;

/**
 * Translator.
 */
class Translator extends BaseTranslator implements TranslatorInterface {
	
	/**
	 *
	 * @var Database
	 */
	private $loaddatabase = null;
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array ();
	
	/**
	 *
	 * @var Variables
	 */
	private $variables = array ();
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	public function __construct($options = null) {
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray ( $options );
		} elseif (! is_array ( $options )) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s expects an array or Traversable object; received "%s"', __METHOD__, (is_object ( $options ) ? get_class ( $options ) : gettype ( $options )) ) );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		if (isset ( $options ['variable'] )) {
			$this->setVariables ( $options ['variable'] );
			unset ( $options ['variable'] );
		}
		$this->setOptions ( $options );
		
		// locales
		if (isset ( $options ['locale'] )) {
			$locales = ( array ) $options ['locale'];
			$this->setLocale ( array_shift ( $locales ) );
			if (count ( $locales ) > 0) {
				$this->setFallbackLocale ( array_shift ( $locales ) );
			}
		}
		
		// database
		if (isset ( $options ['translation_database'] )) {
			if (! is_array ( $options ['translation_database'] )) {
				throw new Exception\InvalidArgumentException ( '"translation_database" should be an array' );
			}
			
			$pattern = $options ['translation_database'];
			$this->addTranslationDatabase ( $pattern ['type'], isset ( $pattern ['text_domain'] ) ? $pattern ['text_domain'] : 'default' );
		}
		
		// file patterns
		if (isset ( $options ['translation_file_patterns'] )) {
			if (! is_array ( $options ['translation_file_patterns'] )) {
				throw new Exception\InvalidArgumentException ( '"translation_file_patterns" should be an array' );
			}
			
			$requiredKeys = array (
					'type',
					'base_dir',
					'pattern' 
			);
			foreach ( $options ['translation_file_patterns'] as $pattern ) {
				foreach ( $requiredKeys as $key ) {
					if (! isset ( $pattern [$key] )) {
						throw new Exception\InvalidArgumentException ( "'{$key}' is missing for translation pattern options" );
					}
				}
				
				$this->addTranslationFilePattern ( $pattern ['type'], $pattern ['base_dir'], $pattern ['pattern'], isset ( $pattern ['text_domain'] ) ? $pattern ['text_domain'] : 'default' );
			}
		}
		
		// files
		if (isset ( $options ['translation_files'] )) {
			if (! is_array ( $options ['translation_files'] )) {
				throw new Exception\InvalidArgumentException ( '"translation_files" should be an array' );
			}
			
			$requiredKeys = array (
					'type',
					'filename' 
			);
			foreach ( $options ['translation_files'] as $file ) {
				foreach ( $requiredKeys as $key ) {
					if (! isset ( $file [$key] )) {
						throw new Exception\InvalidArgumentException ( "'{$key}' is missing for translation file options" );
					}
				}
				
				$this->addTranslationFile ( $file ['type'], $file ['filename'], isset ( $file ['text_domain'] ) ? $file ['text_domain'] : 'default', isset ( $file ['locale'] ) ? $file ['locale'] : null );
			}
		}
		
		// remote
		if (isset ( $options ['remote_translation'] )) {
			if (! is_array ( $options ['remote_translation'] )) {
				throw new Exception\InvalidArgumentException ( '"remote_translation" should be an array' );
			}
			
			$requiredKeys = array (
					'type' 
			);
			foreach ( $options ['remote_translation'] as $remote ) {
				foreach ( $requiredKeys as $key ) {
					if (! isset ( $remote [$key] )) {
						throw new Exception\InvalidArgumentException ( "'{$key}' is missing for remote translation options" );
					}
				}
				
				$this->addRemoteTranslations ( $remote ['type'], isset ( $remote ['text_domain'] ) ? $remote ['text_domain'] : 'default' );
			}
		}
		
		// cache
		if (isset ( $options ['cache'] )) {
			if ($options ['cache'] instanceof CacheStorage) {
				$this->setCache ( $options ['cache'] );
			} else {
				$this->setCache ( Cache\StorageFactory::factory ( $options ['cache'] ) );
			}
		}
		
		// event manager enabled
		if (isset ( $options ['event_manager_enabled'] ) && $options ['event_manager_enabled']) {
			$this->enableEventManager ();
		}
	}
	
	/**
	 * function call handler
	 *
	 * @param string $function
	 *        	Function name to call
	 * @param array $args
	 *        	Function arguments
	 * @return mixed
	 * @throws Exception\RuntimeException
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		if (is_object ( $this->generalobject )) {
			$obj = $this->generalobject;
			if (method_exists ( $obj, $name )) {
				if (is_array ( $arguments ) && count ( $arguments ) > 0) {
					return call_user_func_array ( array (
							$obj,
							$name 
					), $arguments );
				} else {
					return call_user_func ( array (
							$obj,
							$name 
					) );
				}
			}
		}
		return null;
	}
	
	/**
	 * Translate a message.
	 *
	 * @param string $message        	
	 * @param string $textDomain        	
	 * @param string $locale        	
	 * @return string
	 */
	public function translate($message, $textDomain = 'default', $locale = null) {
		$message = strtolower ( $message );
		if (strtolower ( SYSTEM_LANGUAGE_DEFINATION_LOG ) == "true") {
			$id = $this->getLocaleID ();
			$hasKey = false;
			$key_id = 0;
			$key_count = 0;
			
			$QKey = $this->getDatabase ();
			$QKey->select ();
			$QKey->columns ( array (
					'id' => 'system_language_defination_id',
					'count' => 'system_language_defination_count' 
			) );
			$QKey->from ( array (
					'sl' => 'system_language_defination' 
			) );
			$QKey->where ( array (
					'system_language_id' => $id,
					'system_language_defination_key' => $message 
			) );
			$QKey->limit ( 1 );
			$QKey->execute ();
			if ($QKey->hasResult ()) {
				$hasKey = true;
				$rawdata = $QKey->current ();
				$key_id = $rawdata ['id'];
				$key_count = $rawdata ['count'];
			} else {
				$IKey = $this->getDatabase ();
				$IKey->insert ();
				$IKey->into ( 'system_language_defination' );
				$IKey->values ( array (
						'system_language_id' => $id,
						'system_language_defination_key' => $message,
						'system_language_defination_value' => "" 
				) );
				$IKey->execute ();
				if ($IKey->affectedRows ()) {
					$hasKey = true;
					$key_id = $IKey->getLastGeneratedValue ();
				}
			}
			
			if ($hasKey && $key_id > 0) {
				$UCount = $this->getDatabase ();
				$UCount->update ();
				$UCount->table ( 'system_language_defination' );
				$UCount->set ( array (
						'system_language_defination_count' => ($key_count + 1) 
				) );
				$UCount->where ( array (
						'system_language_defination_id' => $key_id 
				) );
				$UCount->setDisableCache(true);
				$UCount->execute ();
			}
		}
		
		return parent::translate ( $message, $textDomain, $locale );
	}
	
	/**
	 * Add translations with a database.
	 *
	 * @param string $type        	
	 * @param string $textDomain        	
	 * @return Translator
	 */
	public function addTranslationDatabase($type, $textDomain = 'default') {
		if (! isset ( $this->loaddatabase [$textDomain] )) {
			$this->loaddatabase [$textDomain] = null;
		}
		
		$this->loaddatabase [$textDomain] = array (
				'type' => $type 
		);
		
		return $this;
	}
	
	/**
	 * Load messages for a given language and domain.
	 *
	 * @triggers loadMessages.no-messages-loaded
	 *
	 * @param string $textDomain        	
	 * @param string $locale        	
	 * @throws Exception\RuntimeException
	 * @return void
	 */
	protected function loadMessages($textDomain, $locale) {
		if (! isset ( $this->messages [$textDomain] )) {
			$this->messages [$textDomain] = array ();
		}
		
		if (null !== ($cache = $this->getCache ())) {
			$cacheId = 'Zend_I18n_Translator_Messages_' . md5 ( $textDomain . $locale );
			
			if (null !== ($result = $cache->getItem ( $cacheId ))) {
				$this->messages [$textDomain] [$locale] = $result;
				
				return;
			}
		}
		
		$messagesLoaded = false;
		$messagesLoaded |= $this->loadMessagesFromDatabase ( $textDomain, $locale );
		$messagesLoaded |= $this->loadMessagesFromRemote ( $textDomain, $locale );
		$messagesLoaded |= $this->loadMessagesFromPatterns ( $textDomain, $locale );
		$messagesLoaded |= $this->loadMessagesFromFiles ( $textDomain, $locale );
		
		if (! $messagesLoaded) {
			$discoveredTextDomain = null;
			if ($this->isEventManagerEnabled ()) {
				$results = $this->getEventManager ()->trigger ( self::EVENT_NO_MESSAGES_LOADED, $this, array (
						'locale' => $locale,
						'text_domain' => $textDomain 
				), function ($r) {
					return ($r instanceof TextDomain);
				} );
				$last = $results->last ();
				if ($last instanceof TextDomain) {
					$discoveredTextDomain = $last;
				}
			}
			
			$this->messages [$textDomain] [$locale] = $discoveredTextDomain;
			$messagesLoaded = true;
		}
		
		if ($messagesLoaded && $cache !== null) {
			$cache->setItem ( $cacheId, $this->messages [$textDomain] [$locale] );
		}
	}
	
	/**
	 * Load messages from database.
	 *
	 * @param string $textDomain        	
	 * @param string $locale        	
	 * @return bool
	 * @throws Exception\RuntimeException When specified loader is not a file loader
	 */
	protected function loadMessagesFromDatabase($textDomain, $locale) {
		$messagesLoaded = false;
		if (isset ( $this->loaddatabase [$textDomain] )) {
			if ($this->loaddatabase [$textDomain] ['type'] == 'database') {
				$themelanguage = $this->getThemeLanguage ();
				if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
					$message = array ();
					
					$QActive = $this->getDatabase ();
					$QActive->select ();
					$QActive->columns ( array (
							'id' => 'system_language_id',
							'name' => 'system_language_name',
							'iso' => 'system_language_iso' 
					) );
					$QActive->from ( array (
							'sl' => 'system_language' 
					) );
					$QActive->where ( array (
							'sl.system_language_status = 1',
							'sl.system_language_iso = "' . $locale . '"',
							'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
					) );
					$QActive->order ( array (
							'system_language_iso ASC' 
					) );
					$QActive->limit ( 1 );
					$QActive->execute ();
					if ($QActive->hasResult ()) {
						$data = $QActive->current ();
						
						$QDefination = $this->getDatabase ();
						$QDefination->select ();
						$QDefination->columns ( array (
								'id' => 'system_language_defination_id',
								'key' => 'system_language_defination_key',
								'value' => 'system_language_defination_value' 
						) );
						$QDefination->from ( array (
								'sld' => 'system_language_defination' 
						) );
						$QDefination->where ( array (
								'sld.system_language_id = ' . $data ['id'] 
						) );
						$QDefination->order ( array (
								'system_language_defination_key ASC' 
						) );
						$QDefination->execute ();
						if ($QDefination->hasResult ()) {
							while ( $QDefination->valid () ) {
								$search = "/U+([0-9A-F]{4})/";
								$replace = "&#x\\1;";
								$message [$QDefination->get ( 'key' )] = html_entity_decode ( preg_replace ( $search, $replace, $QDefination->get ( 'value' ) ), ENT_NOQUOTES, 'UTF-8' );
								$QDefination->next ();
							}
						}
						
						$QTDefination = $this->getDatabase ();
						$QTDefination->select ();
						$QTDefination->columns ( array (
								'key' => 'theme_system_language_defination_key',
								'value' => 'theme_system_language_defination_value' 
						) );
						$QTDefination->from ( array (
								'tsld' => 'theme_system_language_defination' 
						) );
						$QTDefination->where ( array (
								'tsld.system_language_id = ' . $data ['id'],
								'tsld.theme_id = ' . THEME_ID 
						) );
						$QTDefination->order ( array (
								'theme_system_language_defination_key ASC' 
						) );
						$QTDefination->execute ();
						if ($QTDefination->hasResult ()) {
							while ( $QTDefination->valid () ) {
								$search = "/U+([0-9A-F]{4})/";
								$replace = "&#x\\1;";
								$message [$QTDefination->get ( 'key' )] = html_entity_decode ( preg_replace ( $search, $replace, $QTDefination->get ( 'value' ) ), ENT_NOQUOTES, 'UTF-8' );
								$QTDefination->next ();
							}
						}
					}
					if (isset ( $this->messages [$textDomain] [$locale] )) {
						$this->messages [$textDomain] [$locale]->merge ( $message );
					} else {
						$this->messages [$textDomain] [$locale] = $message;
					}
					
					$messagesLoaded = true;
				}
			}
		}
		
		return $messagesLoaded;
	}
	
	/**
	 * Get Locale from database.
	 *
	 * @param string $locale        	
	 * @return bool
	 */
	public function getLocaleIDbyISO($locale) {
		$existlocale = false;
		$themelanguage = $this->getThemeLanguage ();
		$id = 0;
		if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
			$QActive = $this->getDatabase ();
			$QActive->select ();
			$QActive->columns ( array (
					'id' => 'system_language_id' 
			) );
			$QActive->from ( array (
					'sl' => 'system_language' 
			) );
			$QActive->where ( array (
					'sl.system_language_status = 1',
					'sl.system_language_iso = "' . $locale . '"',
					'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
			) );
			$QActive->order ( array (
					'system_language_iso ASC' 
			) );
			$QActive->limit ( 1 );
			$QActive->execute ();
			if ($QActive->hasResult ()) {
				while ( $QActive->valid () ) {
					$rawdata = $QActive->current ();
					$id = $rawdata ['id'];
					$QActive->next ();
				}
			}
		}
		
		return $id;
	}
	
	/**
	 * Get Locale from database.
	 *
	 * @param string $locale        	
	 * @return bool
	 */
	public function getLocaleISObyID($id) {
		$existlocale = false;
		$themelanguage = $this->getThemeLanguage ();
		$iso = 0;
		if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
			$QActive = $this->getDatabase ();
			$QActive->select ();
			$QActive->columns ( array (
					'iso' => 'system_language_iso' 
			) );
			$QActive->from ( array (
					'sl' => 'system_language' 
			) );
			$QActive->where ( array (
					'sl.system_language_status = 1',
					'sl.system_language_id = "' . $id . '"',
					'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
			) );
			$QActive->order ( array (
					'system_language_iso ASC' 
			) );
			$QActive->limit ( 1 );
			$QActive->execute ();
			if ($QActive->hasResult ()) {
				while ( $QActive->valid () ) {
					$rawdata = $QActive->current ();
					$iso = $rawdata ['iso'];
					$QActive->next ();
				}
			}
		}
		
		return $iso;
	}
	
	/**
	 * Check Locale from database.
	 *
	 * @param string $locale        	
	 * @return bool
	 */
	public function checkLocale($locale) {
		$existlocale = false;
		$themelanguage = $this->getThemeLanguage ();
		if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
			$QActive = $this->getDatabase ();
			$QActive->select ();
			$QActive->columns ( array (
					'id' => 'system_language_id',
					'name' => 'system_language_name',
					'iso' => 'system_language_iso' 
			) );
			$QActive->from ( array (
					'sl' => 'system_language' 
			) );
			$QActive->where ( array (
					'sl.system_language_status = 1',
					'sl.system_language_iso = "' . $locale . '"',
					'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
			) );
			$QActive->order ( array (
					'system_language_iso ASC' 
			) );
			$QActive->limit ( 1 );
			$QActive->execute ();
			if ($QActive->hasResult ()) {
				$existlocale = true;
			}
		}
		
		return $existlocale;
	}
	
	/**
	 * Get All Locale from database.
	 *
	 * @param string $locale        	
	 * @return array
	 */
	public function getAllLocale() {
		$data = array ();
		$themelanguage = $this->getThemeLanguage ();
		if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
			$QActive = $this->getDatabase ();
			$QActive->select ();
			$QActive->columns ( array (
					'id' => 'system_language_id',
					'name' => 'system_language_name',
					'iso' => 'system_language_iso' 
			) );
			$QActive->from ( array (
					'sl' => 'system_language' 
			) );
			$QActive->where ( array (
					'sl.system_language_status = 1',
					'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
			) );
			$QActive->order ( array (
					'system_language_iso ASC' 
			) );
			$QActive->execute ();
			if ($QActive->hasResult ()) {
				$data = array ();
				while ( $QActive->valid () ) {
					$data [] = $QActive->current ();
					$QActive->next ();
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Get Locale ID.
	 *
	 * @return array
	 */
	public function getLocaleID() {
		$locale = $this->getLocale ();
		$id = 0;
		$themelanguage = $this->getThemeLanguage ();
		if (is_array ( $themelanguage ) && count ( $themelanguage ) > 0) {
			$QActive = $this->getDatabase ();
			$QActive->select ();
			$QActive->columns ( array (
					'id' => 'system_language_id',
					'name' => 'system_language_name',
					'iso' => 'system_language_iso' 
			) );
			$QActive->from ( array (
					'sl' => 'system_language' 
			) );
			$QActive->where ( array (
					'sl.system_language_status = 1',
					'sl.system_language_iso = "' . $locale . '"',
					'sl.system_language_id in (' . implode ( ", ", $themelanguage ) . ')' 
			) );
			$QActive->order ( array (
					'system_language_iso ASC' 
			) );
			$QActive->limit ( 1 );
			$QActive->execute ();
			if ($QActive->hasResult ()) {
				$rawdata = $QActive->current ();
				$id = $rawdata ['id'];
			}
		}
		
		return $id;
	}
	
	/**
	 * Get Theme Language from database.
	 *
	 * @return array
	 */
	public function getThemeLanguage() {
		$data = array ();
		
		$QActive = $this->getDatabase ();
		$QActive->select ();
		$QActive->columns ( array (
				'id' => 'system_language_id' 
		) );
		$QActive->from ( array (
				'tsl' => 'theme_system_language' 
		) );
		$QActive->where ( array (
				'tsl.theme_id = ' . THEME_ID,
				'tsl.theme_system_language_status = 1' 
		) );
		$QActive->execute ();
		if ($QActive->hasResult ()) {
			while ( $QActive->valid () ) {
				$rawdata = $QActive->current ();
				$data [] = $rawdata ['id'];
				$QActive->next ();
			}
		}
		
		return $data;
	}
}

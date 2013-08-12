<?php
namespace Techfever\Translator;

use Traversable;
use Zend\Cache;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\I18n\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\I18n\Translator\TextDomain;
use Zend\I18n\Translator\Translator as BaseTranslator;
use Techfever\Database\Database;
use Zend\Validator\Translator\TranslatorInterface;

/**
 * Translator.
 */
class Translator extends BaseTranslator implements TranslatorInterface {

	/**
	 * @var Database
	 */
	private $database = null;

	/**
	 * Database used for loading messages.
	 *
	 * @var array
	 */
	protected $db = null;

	/**
	 * Instantiate a translator
	 *
	 * @param  array|Traversable                  $options
	 * @return Translator
	 * @throws Exception\InvalidArgumentException
	 */
	public static function factory($options, Database $database = null) {

		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		} elseif (!is_array($options)) {
			throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Traversable object; received "%s"', __METHOD__, (is_object($options) ? get_class($options) : gettype($options))));
		}

		$translator = new static();

		if (isset($database)) {
			$translator->db = $database;
		}

		// locales
		if (isset($options['locale'])) {
			$locales = (array) $options['locale'];
			$translator->setLocale(array_shift($locales));
			if (count($locales) > 0) {
				$translator->setFallbackLocale(array_shift($locales));
			}
		}

		// database
		if (isset($options['translation_database'])) {
			if (!is_array($options['translation_database'])) {
				throw new Exception\InvalidArgumentException('"translation_database" should be an array');
			}

			$pattern = $options['translation_database'];
			$translator->addTranslationDatabase($pattern['type'], isset($pattern['text_domain']) ? $pattern['text_domain'] : 'default');
		}

		// file patterns
		if (isset($options['translation_file_patterns'])) {
			if (!is_array($options['translation_file_patterns'])) {
				throw new Exception\InvalidArgumentException('"translation_file_patterns" should be an array');
			}

			$requiredKeys = array(
					'type',
					'base_dir',
					'pattern'
			);
			foreach ($options['translation_file_patterns'] as $pattern) {
				foreach ($requiredKeys as $key) {
					if (!isset($pattern[$key])) {
						throw new Exception\InvalidArgumentException("'{$key}' is missing for translation pattern options");
					}
				}

				$translator->addTranslationFilePattern($pattern['type'], $pattern['base_dir'], $pattern['pattern'], isset($pattern['text_domain']) ? $pattern['text_domain'] : 'default');
			}
		}

		// files
		if (isset($options['translation_files'])) {
			if (!is_array($options['translation_files'])) {
				throw new Exception\InvalidArgumentException('"translation_files" should be an array');
			}

			$requiredKeys = array(
					'type',
					'filename'
			);
			foreach ($options['translation_files'] as $file) {
				foreach ($requiredKeys as $key) {
					if (!isset($file[$key])) {
						throw new Exception\InvalidArgumentException("'{$key}' is missing for translation file options");
					}
				}

				$translator->addTranslationFile($file['type'], $file['filename'], isset($file['text_domain']) ? $file['text_domain'] : 'default', isset($file['locale']) ? $file['locale'] : null);
			}
		}

		// remote
		if (isset($options['remote_translation'])) {
			if (!is_array($options['remote_translation'])) {
				throw new Exception\InvalidArgumentException('"remote_translation" should be an array');
			}

			$requiredKeys = array(
					'type'
			);
			foreach ($options['remote_translation'] as $remote) {
				foreach ($requiredKeys as $key) {
					if (!isset($remote[$key])) {
						throw new Exception\InvalidArgumentException("'{$key}' is missing for remote translation options");
					}
				}

				$translator->addRemoteTranslations($remote['type'], isset($remote['text_domain']) ? $remote['text_domain'] : 'default');
			}
		}

		// cache
		if (isset($options['cache'])) {
			if ($options['cache'] instanceof CacheStorage) {
				$translator->setCache($options['cache']);
			} else {
				$translator->setCache(Cache\StorageFactory::factory($options['cache']));
			}
		}

		// event manager enabled
		if (isset($options['event_manager_enabled']) && $options['event_manager_enabled']) {
			$translator->enableEventManager();
		}

		return $translator;
	}

	/**
	 * getDatabase()
	 *
	 * @throws Exception\RuntimeException
	 * @return Database\Database
	 */
	public function getDatabase() {
		if ($this->db == null) {
			throw new Exception\RuntimeException('Database has not been set or configured.');
		}
		return clone $this->db;
	}

	/**
	 * Add translations with a database.
	 *
	 * @param  string     $type
	 * @param  string     $textDomain
	 * @return Translator
	 */
	public function addTranslationDatabase($type, $textDomain = 'default') {
		if (!isset($this->database[$textDomain])) {
			$this->database[$textDomain] = null;
		}

		$this->database[$textDomain] = array(
				'type' => $type
		);

		return $this;
	}

	/**
	 * Load messages for a given language and domain.
	 *
	 * @triggers loadMessages.no-messages-loaded
	 * @param    string $textDomain
	 * @param    string $locale
	 * @throws   Exception\RuntimeException
	 * @return   void
	 */
	protected function loadMessages($textDomain, $locale) {
		if (!isset($this->messages[$textDomain])) {
			$this->messages[$textDomain] = array();
		}

		if (null !== ($cache = $this->getCache())) {
			$cacheId = 'Zend_I18n_Translator_Messages_' . md5($textDomain . $locale);

			if (null !== ($result = $cache->getItem($cacheId))) {
				$this->messages[$textDomain][$locale] = $result;

				return;
			}
		}

		$messagesLoaded = false;
		$messagesLoaded |= $this->loadMessagesFromDatabase($textDomain, $locale);
		$messagesLoaded |= $this->loadMessagesFromRemote($textDomain, $locale);
		$messagesLoaded |= $this->loadMessagesFromPatterns($textDomain, $locale);
		$messagesLoaded |= $this->loadMessagesFromFiles($textDomain, $locale);

		if (!$messagesLoaded) {
			$discoveredTextDomain = null;
			if ($this->isEventManagerEnabled()) {
				$results = $this->getEventManager()->trigger(self::EVENT_NO_MESSAGES_LOADED, $this, array(
								'locale' => $locale,
								'text_domain' => $textDomain,
						), function ($r) {
							return ($r instanceof TextDomain);
						});
				$last = $results->last();
				if ($last instanceof TextDomain) {
					$discoveredTextDomain = $last;
				}
			}

			$this->messages[$textDomain][$locale] = $discoveredTextDomain;
			$messagesLoaded = true;
		}

		if ($messagesLoaded && $cache !== null) {
			$cache->setItem($cacheId, $this->messages[$textDomain][$locale]);
		}
	}

	/**
	 * Load messages from database.
	 *
	 * @param  string $textDomain
	 * @param  string $locale
	 * @return bool
	 * @throws Exception\RuntimeException When specified loader is not a file loader
	 */
	protected function loadMessagesFromDatabase($textDomain, $locale) {
		$messagesLoaded = false;
		if (isset($this->database[$textDomain])) {
			if ($this->database[$textDomain]['type'] == 'database') {

				$message = array();
				$QActive = $this->getDatabase();
				$QActive->select();
				$QActive->columns(array(
								'id' => 'system_language_id',
								'name' => 'system_language_name',
								'iso' => 'system_language_iso'
						));
				$QActive->from(array(
								'sl' => 'system_language'
						));
				$QActive->where(array(
								'sl.system_language_status = 1',
								'sl.system_language_iso = "' . $locale . '"',
						));
				$QActive->order(array(
								'system_language_iso ASC'
						));
				$QActive->setCacheName('system_language');
				$QActive->limit(1);
				$QActive->execute();
				if ($QActive->hasResult()) {
					$data = $QActive->current();

					$QDefination = $this->getDatabase();
					$QDefination->select();
					$QDefination->columns(array(
									'id' => 'system_language_defination_id',
									'key' => 'system_language_defination_key',
									'value' => 'system_language_defination_value'
							));
					$QDefination->from(array(
									'sld' => 'system_language_defination'
							));
					$QDefination->where(array(
									'sld.system_language_id = ' . $data['id'],
							));
					$QDefination->order(array(
									'system_language_defination_key ASC'
							));
					$QDefination->setCacheName('system_language_defination_' . strtolower($locale));
					$QDefination->execute();
					if ($QDefination->hasResult()) {
						$data = array();
						while ($QDefination->valid()) {
							$search = "/U\+([0-9A-F]{4})/";
							$replace = "&#x\\1;";
							$message[$QDefination->get('key')] = html_entity_decode(preg_replace($search, $replace, $QDefination->get('value')), ENT_NOQUOTES, 'UTF-8');
							$QDefination->next();
						}
					}
				}
				if (isset($this->messages[$textDomain][$locale])) {
					$this->messages[$textDomain][$locale]->merge($message);
				} else {
					$this->messages[$textDomain][$locale] = $message;
				}

				$messagesLoaded = true;
			}
		}

		return $messagesLoaded;
	}
}

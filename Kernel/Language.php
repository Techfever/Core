<?php
namespace Kernel;

use Zend\Config\Reader\Xml;

class Language {
	/**
	 * @var Locale
	 **/
	private $_locale = null;

	/**
	 * @var Languages
	 **/
	private $_language = null;

	/**
	 * @var Active
	 **/
	private $_active = array();

	/**
	 * @var Has Active
	 **/
	private $_hasactive = false;

	/**
	 * @var Defination
	 **/
	private $_defination = array();

	/**
	 * @var Has Defination
	 **/
	private $_hasdefination = false;

	/**
	 * Constructor
	 */
	public function __construct($locale) {
		$this->_locale = $locale;
		$this->getActive();
	}

	/**
	 * getLanguage
	 */
	public function getLanguage($language) {
		$this->_language = $language;
		return $this->getDefination($this->_language);
	}

	public function hasActive() {
		return $this->_hasactive;
	}

	public function getActive() {
		if (!is_array($this->_active) || count($this->_active) < 1) {
			$DBActive = new Database('select');
			$DBActive->columns(array(
						'id' => 'system_language_id', 'name' => 'system_language_name', 'iso' => 'system_language_iso'
					));
			$DBActive->from(array(
						'sl' => 'system_language'
					));
			$DBActive->where(array(
						'sl.system_language_status = 1',
					));
			$DBActive->order(array(
						'system_language_iso ASC'
					));
			$DBActive->setCacheName('system_language');
			$DBActive->execute();
			if ($DBActive->hasResult()) {
				$this->_active = $DBActive->toArray();
				$this->_hasactive = true;
			}
		}
		return $this->_active;
	}

	public function hasDefination() {
		return $this->_hasdefination;
	}

	public function getDefination($locale = null) {
		if ($this->_hasactive && is_array($this->_active) && !empty($locale)) {
			foreach ($this->_active as $active) {
				$id = $active['id'];
				$iso = $active['iso'];
				if ($iso == $locale) {
					$DBDefination = new Database('select');
					$DBDefination->columns(array(
								'id' => 'system_language_defination_id', 'key' => 'system_language_defination_key', 'value' => 'system_language_defination_value'
							));
					$DBDefination->from(array(
								'sld' => 'system_language_defination'
							));
					$DBDefination->where(array(
								'sld.system_language_id = ' . $id,
							));
					$DBDefination->order(array(
								'system_language_defination_key ASC'
							));
					$DBDefination->setCacheName('system_language_defination_' . strtolower($locale));
					$DBDefination->execute();
					if ($DBDefination->hasResult()) {
						$data = array();
						while ($DBDefination->valid()) {
							$data[$DBDefination->get('key')] = $DBDefination->get('value');
							$DBDefination->next();
						}
						$this->_defination[$locale] = $data;
						$this->_hasdefination = true;
					}
				}
			}
		}
		if (!empty($locale)) {
			return $this->_defination[$locale];
		}
		return false;
	}
}

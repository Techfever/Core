<?php
namespace Techfever\Navigator;

use Techfever\Translator\Translator;
use Techfever\Database\Database;
use Techfever\Exception;

class Navigator {
	/**
	 * @var Data
	 **/
	private $structure = null;

	/**
	 * @var Query
	 **/
	private $navigator = array();

	/**
	 * @var Database\Database
	 */
	private $database = null;

	/**
	 * @var Translator\Translator
	 */
	private $translator = null;

	/**
	 * Constructor
	 */
	public function __construct(Database $database = null, Translator $translator = null) {
		$this->database = $database;
		$this->translator = $translator;
	}

	/**
	 * getDatabase()
	 *
	 * @throws Exception\RuntimeException
	 * @return Database\Database
	 */
	public function getDatabase() {
		if ($this->database == null) {
			throw new Exception\RuntimeException('Database has not been set or configured.');
		}
		return clone $this->database;
	}

	/**
	 * getTranslate()
	 *
	 * @throws Exception\RuntimeException
	 * @return Translator\Translator
	 */
	public function getTranslate($key) {
		if (!is_object($this->translator)) {
			$this->translator = $this->getServiceLocator()->get('translator');
		}
		$message = $this->translator->translate($key);
		if (!empty($message) && strlen($message) > 0) {
			return $message;
		}
		return null;
	}

	/**
	 * Get Data
	 * 
	 * @return void
	 **/
	public function getNavigator() {
		if (!is_array($this->navigator) || count($this->navigator) < 1) {
			$QNavigator = $this->getDatabase();
			$QNavigator->select();
			$QNavigator
					->columns(
							array(
									'root' => 'system_navigator_id',
									'label' => 'system_navigator_label',
									'route' => 'system_navigator_route',
									'action' => 'system_navigator_action',
									'controller' => 'system_navigator_controller',
									'uri' => 'system_navigator_uri',
									'class' => 'system_navigator_css_class',
									'order' => 'system_navigator_order',
									'visible' => 'system_navigator_visible',
									'parent' => 'system_navigator_parent'
							));
			$QNavigator->from(array(
							'sn' => 'system_navigator'
					));
			$QNavigator->order(array(
							'system_navigator_parent ASC',
							'system_navigator_order ASC',
							'system_navigator_label ASC'
					));
			$QNavigator->setCacheName('system_navigator');
			$QNavigator->execute();
			if ($QNavigator->hasResult()) {
				$this->navigator = $QNavigator->toArray();
			}
		}
		return $this->navigator;
	}

	/**
	 * Prepare
	 */
	public function getTree($parent = 0) {
		$data = null;
		$navigator = $this->getNavigator();
		if (is_array($navigator) && count($navigator) > 0) {
			foreach ($navigator as $dbdata) {
				if ($dbdata['parent'] == $parent) {
					$rawdata = $this->getTree($dbdata['root']);
					if (is_array($rawdata) && count($rawdata) > 0) {
						$dbdata['pages'] = $rawdata;
					}
					$structure = array();
					if (!empty($dbdata['label'])) {
						$structure['label'] = $this->getTranslate('text_navigator_' . strtolower($dbdata['label']));
						$structure['id'] = strtolower($dbdata['label']);
					}
					if (!empty($dbdata['route'])) {
						$structure['route'] = $dbdata['route'];
					}
					if (!empty($dbdata['action'])) {
						$structure['action'] = $dbdata['action'];
					}
					if (!empty($dbdata['controller'])) {
						$structure['controller'] = $dbdata['controller'];
					}
					if (!empty($dbdata['uri'])) {
						$structure['uri'] = $dbdata['uri'];
					}
					if (!empty($dbdata['class'])) {
						$structure['class'] = $dbdata['class'];
					}
					if (!empty($dbdata['order'])) {
						$structure['order'] = $dbdata['order'];
					}
					if (!empty($dbdata['visible'])) {
						$structure['visible'] = ($dbdata['visible'] == 1 ? True : False);
					}
					if (!empty($dbdata['pages'])) {
						$structure['pages'] = $dbdata['pages'];
					}
					$data[] = $structure;
				}
			}
		}
		return $data;
	}

	/**
	 * getStructure
	 */
	public function getStructure() {
		if (!is_array($this->structure) || count($this->structure) < 1) {
			$this->structure = $this->getTree();
		}
		return $this->structure;
	}
}

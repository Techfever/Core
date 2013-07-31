<?php
namespace Kernel\User;

use Kernel\ServiceLocator;
use Kernel\Database\Database;
use DateTime;

class Log {

	/**
	 * @var Location
	 **/
	private $_location = array();

	/**
	 * @var Valid
	 **/
	private $_valid = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$date = new DateTime('NOW');
		$date = $date->format('Y-m-d H:i:s');
		$id = $this->getUserID();
		$name = $this->getUserName();
		$ipaddress = $this->getUserIP();
		$session = $this->getSessionID();
		$currenturi = $this->getUri();
		$referuri = $this->getReferer();
		$currenturicheck = explode('/', $currenturi);
		$currenturistatus = true;
		if (is_array($currenturicheck) && count($currenturicheck) > 1 && $currenturicheck[2] === 'Theme') {
			$currenturistatus = false;
		}
		$location = array(
				'id' => '',
				'name' => '',
				'ip' => '',
				'session' => '',
				'uri' => '',
				'refer' => '',
				'date' => ''
		);
		if ($currenturistatus) {
			$location = array(
					'id' => $id,
					'name' => $name,
					'ip' => $ipaddress,
					'session' => $session,
					'uri' => $currenturi,
					'refer' => $referuri,
					'date' => $date
			);
			$this->_valid = true;
		}
		$this->_location = $location;
	}

	public function getUserID() {
		$id = 0;
		return $id;
	}

	public function getUserName() {
		$name = 'Unknown';
		return $name;
	}

	public function getUserIP() {
		$ip = '1.1.1.1';
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP');
			} else {
				$ip = getenv('REMOTE_ADDR');
			}
		}
		return $ip;
	}

	public function getSessionID() {
		$session = ServiceLocator::getServiceManager('Session');
		if ($session->isValid()) {
			return $session->getId();
		}
		return null;
	}

	public function getUri() {
		$uri = (array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null);
		return $uri;
	}

	public function getReferer() {
		$referer = (array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : null);
		return $referer;
	}

	public function insert() {
		if ($this->_valid && CACHE_ENABLE) {
			$DbInsert = new Database('insert');
			$DbInsert->into('user_access_log');
			$DbInsert->columns(array(
							'user_access_id',
							'user_access_name',
							'user_access_log_session',
							'user_access_log_ip',
							'user_access_log_uri',
							'user_access_log_referrel',
							'user_access_log_created_date'
					));
			$DbInsert
					->values(
							array(
									'user_access_id' => $this->_location['id'],
									'user_access_name' => $this->_location['name'],
									'user_access_log_session' => $this->_location['session'],
									'user_access_log_ip' => $this->_location['ip'],
									'user_access_log_uri' => $this->_location['uri'],
									'user_access_log_referrel' => $this->_location['refer'],
									'user_access_log_created_date' => $this->_location['date']
							));
			$DbInsert->execute();
			if ($DbInsert->affectedRows()) {
				return true;
			}
		}
		return false;
	}
}

<?php

namespace Techfever\Functions\Crypt;

use Techfever\Functions\General as GeneralBase;

class Encode extends GeneralBase {
	private $data = null;
	public function __construct($data, $add_key = true) {
		if ($add_key) {
			$key = (strlen ( SYSTEM_CRYPT_KEY ) > 0 ? SYSTEM_CRYPT_KEY : 111216);
			$data = ($key + $data);
			$data = base64_encode ( $data );
		} else {
			$data = rtrim ( strtr ( base64_encode ( $data ), '+/', '-_' ), '=' );
		}
		$this->data = $data;
	}
	public function __toString() {
		return ( string ) $this->data;
	}
}

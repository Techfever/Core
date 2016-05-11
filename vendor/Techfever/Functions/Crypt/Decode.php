<?php

namespace Techfever\Functions\Crypt;

use Techfever\Functions\General as GeneralBase;

class Decode extends GeneralBase {
	private $data = null;
	public function __construct($data, $add_key = true) {
		if ($add_key) {
			$data = base64_decode ( $data );
			$key = (strlen ( SYSTEM_CRYPT_KEY ) > 0 ? SYSTEM_CRYPT_KEY : 111216);
			$data = ($data - $key);
		} else {
			$data = base64_decode ( str_pad ( strtr ( $data, '-_', '+/' ), strlen ( $data ) % 4, '=', STR_PAD_RIGHT ) );
		}
		$this->data = $data;
	}
	public function __toString() {
		return ( string ) $this->data;
	}
}

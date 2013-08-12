<?php
namespace Techfever\Functions;

class DirConvert {
	private $path = null;

	public function __construct($path) {
		$path = str_replace(DSOTHER, DS, $path);
		$this->path = str_replace(DSWIN, DS, $path);
	}

	public function __toString() {
		return (string) $this->path;
	}
}

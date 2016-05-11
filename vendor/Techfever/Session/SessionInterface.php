<?php

namespace Techfever\Session;

interface SessionInterface {
	public function getContainer($key);
	public function getManager();
	public function initialize();
}

<?php
/**
 * Autoloader
 */

define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/kernel');

// use library generated autoloader
require_once 'vendor/autoload.php';
?>

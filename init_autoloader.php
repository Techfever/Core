<?php
/**
 * Autoloader
 */

define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/kernel');

// use library generated autoloader

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
	$loader = include 'vendor/autoload.php';
}
// Get application stack configuration
$configuration = include KERNEL_PATH . '/Config/KernelNamespace.php';
if (isset($loader)) {
	foreach ($configuration['Namespace'] as $name => $path) {
		$loader->add($name, dirname($path));
	}
	$loader->register();
}
?>

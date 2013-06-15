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

$vendorPath = false;
if (is_dir('vendor')) {
	$vendorPath = 'vendor';
}
$kernelPath = false;
if (is_dir('kernel')) {
	$kernelPath = 'kernel';
	$configuration = include CORE_PATH . '/config/Kernel.Config.php';
} else {
	$configuration = array(
		"autoloader" => array(
			"autoregister_zf" => true,
		)
	);
}
if ($vendorPath) {
	if (isset($loader)) {
		$loader->add('Techfever', $vendorPath);
		foreach ($configuration['autoloader']['namespaces'] as $name => $path) {
			$loader->add($name, dirname($path));
		}
		$loader->register();
	} else {
		include $vendorPath . '/Techfever/Loader/AutoloaderFactory.php';
		Techfever\Loader\AutoloaderFactory::factory(array(
			'Techfever\Loader\StandardAutoloader' => $configuration['autoloader'],
		));
	}
}

if (!class_exists('Techfever\Loader\AutoloaderFactory')) {
	throw new RuntimeException('Unable to load TF1. Run `php composer.phar install` or define a TF1_PATH environment variable.');
}
if (!class_exists('Kernel\Startup')) {
	throw new RuntimeException('Unable to load Kernel.');
}
?>

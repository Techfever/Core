<?php
/**
 * Autoloader
 */

define('CORE_PATH', getcwd());
define('KERNEL_PATH', CORE_PATH . '/Kernel');
define('CONFIG_PATH', CORE_PATH . '/Config');

// use library generated autoloader

// Composer autoloading
if (file_exists('vendor/autoload.php')) {
	$loader = include 'vendor/autoload.php';
}

$vendorPath = false;
if (is_dir('vendor')) {
	$vendorPath = 'vendor';
}
$configuration = include CONFIG_PATH . '/kernel.config.php';

if ($vendorPath) {
	if (isset($loader)) {
		foreach ($configuration['autoloader']['namespaces'] as $name => $path) {
			$loader->add($name, dirname($path));
		}
		$loader->register();
	} else {
		include $vendorPath . '/Zend/Loader/AutoloaderFactory.php';
		Zend\Loader\AutoloaderFactory::factory(array(
			'Zend\Loader\StandardAutoloader' => $configuration['autoloader']
		));
		require $vendorPath . '/Zend/Stdlib/compatibility/autoload.php';
		require $vendorPath . '/Zend/Session/compatibility/autoload.php';
	}
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
	throw new RuntimeException('Unable to load TF1. Run `php composer.phar install` or define a TF1_PATH environment variable.');
}
if (!class_exists('Kernel\Startup')) {
	throw new RuntimeException('Unable to load Kernel.');
}
?>

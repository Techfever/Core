<?php
return array(
		'service_manager' => array(
				'factories' => array(
						'log' => 'Kernel\Service\Factories\LoggerServiceFactory',
						'cache\filesystem' => 'Kernel\Service\Factories\Cache\FilesystemServiceFactory',
						'cache\output' => 'Kernel\Service\Factories\Cache\OutputCacheServiceFactory',
						'cache\capture' => 'Kernel\Service\Factories\Cache\CaptureCacheServiceFactory',
						'db' => 'Kernel\Service\Factories\DbServiceFactory',
						'Navigator' => 'Kernel\Service\Initializers\NavigatorServiceFactory'
				)
		)
);

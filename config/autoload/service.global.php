<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
		'service_manager' => array(
				'factories' => array(
						'UrlRewrite' => 'Techfever\UrlRewrite\UrlRewriteServiceFactory',
						'MobileDetect' => 'Techfever\Mobile\MobileDetectServiceFactory',
						'php' => 'Techfever\Php\PhpServiceFactory',
						'log' => 'Techfever\Log\LoggerServiceFactory',
						'cachestorage' => 'Techfever\Cache\FilesystemServiceFactory',
						'cachepattern' => 'Techfever\Cache\OutputCacheServiceFactory',
						'dbadapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
						'db' => 'Techfever\Database\DatabaseServiceFactory',
						'session' => 'Techfever\Session\SessionServiceFactory',
						'template' => 'Techfever\Template\TemplateServiceFactory',
						'snapshot' => 'Techfever\Snapshot\SnapshotServiceFactory',
						'MvcTranslator' => 'Techfever\Translator\TranslatorServiceFactory',
						'UserLog' => 'Techfever\User\LogServiceFactory',
						'UserAccess' => 'Techfever\User\AccessServiceFactory',
						'UserPermission' => 'Techfever\User\PermissionServiceFactory',
						'navigator' => 'Techfever\Navigator\NavigatorServiceFactory',
						'SystemService' => 'Techfever\Service\SystemServiceFactory',
				),
				'abstract_factories' => array(
						'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
						'Zend\Log\LoggerAbstractServiceFactory',
				),
				'aliases' => array(
						'translator' => 'MvcTranslator',
				),
		),
);

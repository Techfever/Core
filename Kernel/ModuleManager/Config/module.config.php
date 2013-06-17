<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
		'controllers' => array(
			'invokables' => array(
				'ModuleManager\Controller\Action' => 'ModuleManager\Controller\ActionController'
			)
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'template_map' => array(
						'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
						'site/layout' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/main.phtml',
						'error/404' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/Error/404.phtml',
						'error/index' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/Error/index.phtml'
				),
				'template_path_stack' => array(
					__DIR__ . '/../view'
				),
				'layout' => 'site/layout',
		)
);

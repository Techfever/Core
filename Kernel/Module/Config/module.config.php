<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
		'router' => array(
				'routes' => array(
						'home' => array(
							'type' => 'Zend\Mvc\Router\Http\Literal', 'options' => array(
								'route' => '/', 'defaults' => array(
									'controller' => 'Module\Controller\Action', 'action' => 'index'
								)
							)
						),
						'Module' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/Module', 'defaults' => array(
										'Module' => 'Module\Controller', 'controller' => 'Action', 'action' => 'index'
									)
								),
								'may_terminate' => true,
								'child_routes' => array(
										'default' => array(
												'type' => 'Segment',
												'options' => array(
													'route' => '/[:controller[/:action]]', 'constraints' => array(
														'controller' => '[a-zA-Z][a-zA-Z0-9_-]*', 'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
													), 'defaults' => array()
												)
										)
								)
						)
				)
		),
		'controllers' => array(
			'invokables' => array(
				'Module\Controller\Action' => 'Module\Controller\ActionController'
			)
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'not_found_template' => 'error/404',
				'display_not_found_reason' => true,
				'exception_template' => 'error/index',
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'layout' => 'frontend/layout',
				'template_map' => array(
					'frontend/layout' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/layout.phtml', 'error/404' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/Error/404.phtml', 'error/index' => __DIR__ . '/../../../Vendor/Techfever/Theme/Default/Error/index.phtml'
				),
				'template_path_stack' => array(
					__DIR__ . '/../View'
				)
		)
);

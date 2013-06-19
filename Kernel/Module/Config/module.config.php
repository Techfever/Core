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
							'type' => 'Zend\Mvc\Router\Http\Literal', 
							'options' => array(
								'route' => '/', 
								'defaults' => array(
									'controller' => 'Module\Controller\Action', 
									'action' => 'index'
								)
							)
						),
						'Module' => array(
							'type' => 'Literal',
							'options' => array(
								'route' => '/Module',
								'defaults' => array(
									'Module' => 'Module\Controller', 
									'controller' => 'Action', 
									'action' => 'index'
								)
							),
							'may_terminate' => true,
							'child_routes' => array(
								'default' => array(
									'type' => 'Segment',
										'options' => array(
											'route' => '/[:controller[/:action]]', 
											'constraints' => array(
												'controller' => '[a-zA-Z][a-zA-Z0-9_-]*', 
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
										), 
										'defaults' => array()
									)
								)
							)
						)
				)
		)
);

<?php
return array(
		'router' => array(
				'routes' => array(
						'home' => array(
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array(
										'route' => '/',
										'defaults' => array(
												'controller' => 'Index\Controller\Action',
												'action' => 'Index',
										),
								),
						),
				),
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'layout' => 'layout/layout',
		),
);

<?php
return array(
		'session' => array(
				'config' => array(
						'class' => 'Zend\Session\Config\SessionConfig',
						'options' => array(
								'name' => 'TnM'
						)
				),
				'storage' => 'Zend\Session\Storage\SessionArrayStorage',
				'validators' => array(
						array(
								'Zend\Session\Validator\RemoteAddr',
								'Zend\Session\Validator\HttpUserAgent'
						)
				),
				'save_handler' => array(
						'name' => 'db',
						'adapter' => 'db'
				)
		)
);

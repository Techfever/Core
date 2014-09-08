<?php
return array(
		'log' => array(
				'writers' => array(
						array(
								'name' => 'stream',
								'options' => array(
										'stream' => 'data/log/error-%s.log',
										'formatter' => array(
												'name' => 'simple',
												'options' => array(
														'dateTimeFormat' => 'Y-m-d H:i:s'
												)
										)
								)
						)
				),
				'exceptionhandler' => true,
				'errorhandler' => true
		)
);

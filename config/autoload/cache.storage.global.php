<?php
return array (
		'cachestorage' => array (
				'filesystem' => array (
						'options' => array (
								'namespace' => 'tnm',
								'cache_dir' => 'data/cache',
								'readable' => CACHE_ENABLE,
								'writable' => CACHE_ENABLE 
						),
						'plugins' => array (
								'serializer' => array (
										'throw_exceptions' => true 
								) 
						) 
				) 
		) 
);

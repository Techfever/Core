<?php
return array (
		'cachepattern' => array (
				'output' => array (
						'options' => array (
								'storage' => array (
										'adapter' => array (
												'name' => 'filesystem',
												'options' => array (
														'namespace' => 'tnm',
														'namespace_separator' => '_',
														'cache_dir' => 'data/cache',
														'readable' => CACHE_ENABLE,
														'writable' => CACHE_ENABLE,
												) 
										) 
								),
								'cache_output' => true 
						) 
				),
				'capture' => array (
						'options' => array (
								'public_dir' => __DIR__ 
						) 
				) 
		) 
);

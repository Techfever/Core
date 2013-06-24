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
		'cachepattern' => array(
				'output' => array(
						'options' => array(
								'storage' => array(
									'adapter' => array(
										'name' => 'filesystem', 'options' => array(
											'namespace' => 'output', 'namespace_separator' => '_', 'cache_dir' => 'Data/Cache', 'readable' => CACHE_ENABLE, 'writable' => CACHE_ENABLE,
										)
									)
								),
								'cache_output' => true,
						)
				),
				'capture' => array(
					'options' => array(
						'public_dir' => __DIR__
					)
				)
		)
);

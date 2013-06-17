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

return array (
		'translator' => array (
				'translation_file_patterns' => array (
						array (
								'type' => 'phparray',
								'text_domain' => 'default',
								'base_dir' => __DIR__ . '/../../module/Application/language',
								'pattern' => '%s.php' 
						) 
				),
				'default' => array (
						'type' => 'phparray',
						'text_domain' => 'default',
						'base_dir' => __DIR__ . '/../../module/%s/language',
						'pattern' => '%s.php' 
				) 
		) 
);

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
		'db' => array (
				'driver' => 'Mysqli',
				'driver_options' => array (),
				'username' => 'root',
				'password' => 't3chn4t10n',
				'hostname' => 'localhost',
				'port' => '3306',
				'database' => 'cc_techfever',
				'options' => array (
						'buffer_results' => true 
				) 
		) 
);
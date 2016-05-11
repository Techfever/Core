<?php

namespace Techfever\Template\Plugin\Filters;

use Zend\Filter\File\RenameUpload as BaseRenameUpload;

class RenameUpload extends BaseRenameUpload {
	/**
	 *
	 * @var array
	 */
	protected $options = array (
			'user_id' => null,
			'session_id' => null,
			'timestamp' => null,
			'target' => null,
			'use_upload_name' => false,
			'use_upload_extension' => false,
			'overwrite' => false,
			'randomize' => false 
	);
	
	/**
	 *
	 * @param string $source        	
	 * @param string $filename        	
	 * @return string
	 */
	protected function applyRandomToFilename($source, $filename) {
		$info = pathinfo ( $filename );
		
		$options = $this->getOptions ();
		$filename = $options ['timestamp'];
		$filename .= '-' . $options ['session_id'];
		$filename .= '-tnm';
		
		$sourceinfo = pathinfo ( $source );
		
		$extension = '';
		if ($this->getUseUploadExtension () === true && isset ( $sourceinfo ['extension'] )) {
			$extension .= '.' . $sourceinfo ['extension'];
		} elseif (isset ( $info ['extension'] )) {
			$extension .= '.' . $info ['extension'];
		}
		
		return $filename . $extension;
	}
}

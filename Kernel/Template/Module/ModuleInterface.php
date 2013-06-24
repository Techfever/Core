<?php
namespace Kernel\Template\Module;

interface ModuleInterface {
	/**
	 * Get Template Type Config
	 * 
	 * @return array Template\Config
	 */
	public function getConfig();

	/**
	 * Get Template Type Default Config
	 *
	 * @return array Template\DefaultConfig
	 */
	public function getDefaultConfig();

	/**
	 * Get Template Type Structure
	 * 
	 * @return array Template\Structure
	 */
	public function getStructure();

}

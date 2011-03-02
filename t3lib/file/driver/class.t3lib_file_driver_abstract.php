<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Ingo Renner <ingo@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * File system driver.
 *
 * @author	Ingo Renner <ingo@typo3.org>
 * @package	TYPO3
 * @subpackage	t3lib
 */
class t3lib_file_driver_Abstract {

	protected $configuration = array();

	/**
	 * Constructor for t3lib_file_driver_Abstract.
	 *
	 * @param	array	$configuration Configuration parameters for the driver, such as user name and password for remote drivers for example.
	 */
	public function __construct($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Gets the complete configuration or a specific configuration option for
	 * the driver.
	 *
	 * @param	string	$option (optional) specific option name to get.
	 * @return	mixed	The complete configuration array or a specific (string) configuration option.
	 */
	public function getConfiguration($option = '') {
		$configuration = $this->configuration;

		if (!empty($option) && array_key_exists($option, $configuration)) {
			$configuration = $configuration[$option];
		}

		return $configuration;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_file.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_file.php']);
}

?>
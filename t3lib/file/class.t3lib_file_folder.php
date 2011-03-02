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
 * Folder representation in the file abstraction layer.
 *
 * @author	Ingo Renner <ingo@typo3.org>
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package	TYPO3
 * @subpackage	t3lib
 */
class t3lib_file_Folder {

	/**
	 * The unique id of this folder
	 *
	 * @var integer
	 */
	protected $uid;

	/**
	 * The folder name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The driver this folder comes from
	 *
	 * @var t3lib_file_driver_Abstract
	 */
	protected $driver;

	/**
	 * The parent folder of this item
	 *
	 * @var t3lib_file_Folder
	 */
	protected $parent;

	/**
	 * The configuration for the driver
	 *
	 * @var string/array
	 */
	protected $driverConfiguration;

	/**
	 * Constructor for a folder object.
	 *
	 * @param array $folder The folder row from the database
	 */
	public function __construct(array $folder) {
		// TODO: check who creates folder objects (= where the config could come from)
	}

	/**
	 * Creates a new folder below this folder
	 *
	 * @param  $name
	 * @param null|t3lib_file_driver_Abstract $driver The driver to use. Is inherited from this folder if NULL
	 * @return void
	 */
	public function createSubfolder($name, t3lib_file_driver_Abstract $driver = NULL) {
		// TODO check if creating folder is supported by driver
	}

	/**
	 * Returns a list of all subfolders; if it is given, the list is filtered by pattern.
	 *
	 * @return void
	 */
	public function getSubfolders($pattern = '') {
		// TODO fetch folders
	}

	/**
	 * Returns an array of file objects from this folder; if it is given, the list is filtered by pattern.
	 *
	 * @param string $pattern The pattern to search for. Optional.
	 * @return array
	 */
	public function getFiles($pattern = '') {
		// TODO fetch files
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_folder.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_folder.php']);
}

?>
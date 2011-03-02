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
 * File representation in the file abstraction layer.
 *
 * @author	Ingo Renner <ingo@typo3.org>
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package	TYPO3
 * @subpackage	t3lib
 */
class t3lib_file_File {

	/**
	 * The filename
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The extension of the file
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * The mimetype of the file
	 *
	 * @var string
	 */
	protected $mimetype;

	/**
	 * The SHA1 hash of the file
	 *
	 * @var string
	 */
	protected $sha1;

	/**
	 * The file size
	 *
	 * @var integer
	 */
	protected $size;

	/**
	 * Pointer to the folder this file resides in
	 *
	 * @var t3lib_file_Folder
	 */
	protected $parent;

	public function __construct() {
		//
	}

	public function getName() {
		return $this->name;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_file.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_file_file.php']);
}

?>
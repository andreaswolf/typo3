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
class t3lib_vfs_File {

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
	 * @var t3lib_vfs_Folder
	 */
	protected $parent;

	/**
	 * The driver used to store this file
	 *
	 * @var t3lib_vfs_driver_Abstract
	 */
	protected $storageDriver;

	public function __construct($name, $parentFolder) {
		$this->parent = $parentFolder;
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * Returns a publicly accessible URL for this file.
	 *
	 * WARNING: Access to the file may be restricted by further means, e.g. some web-based authentication. You have to take care of this
	 * yourself.
	 *
	 * @return void
	 */
	public function getPublicUrl() {
		return $this->storageDriver->getPublicUrl($this);
	}

	public function getParent() {
		return $this->parent;
	}

	public function getContents() {
		return $this->storageDriver->getContents($this);
	}

	/**
	 * Opens the file in the specified mode. The mode is specified the same way as with the PHP function fopen()
	 *
	 * @param  $mode
	 * @return void
	 *
	 * @see http://de3.php.net/manual/de/function.fopen.php
	 */
	public function open($mode = 'r') {
		return $this->storageDriver->getFileHandle($this, $mode);
	}

	public function close() {
		//
	}

	public function read($numBytes) {
		//
	}

	public function write($contents) {
		//
	}

	/**
	 * Sets the file pointer to the specified position or, if none is given, returns the current position.
	 *
	 * @param integer $position
	 * @return void
	 */
	public function seek($position = NULL) {
		//
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_file.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_file.php']);
}

?>
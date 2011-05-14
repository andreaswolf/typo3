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
class t3lib_vfs_File extends t3lib_vfs_Node {

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
	 * The driver used to store this file
	 *
	 * @var t3lib_vfs_driver_Abstract
	 */
	protected $storageDriver;

	/**
	 * The handle used when this file is open
	 *
	 * @var t3lib_vfs_FileHandle
	 */
	protected $fileHandle;

	/**
	 * The names of all properties this record has.
	 *
	 * @var array
	 */
	protected $availableProperties = array('pid', 'crdate', 'cruser_id', 'tstamp', 'name', 'sha1', 'mimetype', 'size');


	public function __construct($properties) {
			// TODO change $properties to be an array in all calls (esp. unit tests), remove this typecast afterwards
		parent::__construct((array)$properties);
	}

	public function setStorageDriver(t3lib_vfs_driver_Abstract $driver) {
		$this->storageDriver = $driver;
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

	public function getContents() {
		return $this->storageDriver->getFileContents($this);
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
		$this->fileHandle = $this->storageDriver->getFileHandle($this, $mode);
	}

	/**
	 * Returns TRUE if the file is open.
	 *
	 * @return boolean
	 */
	public function isOpen() {
		return ($this->fileHandle !== NULL && $this->fileHandle->isOpen());
	}

	public function close() {
		$this->fileHandle->close();
		$this->fileHandle = NULL;
	}

	public function read($numBytes) {
		if (!$this->isOpen()) {
			throw new RuntimeException('File is closed.', 1299863431);
		}

		return $this->storageDriver->readFromFile($this->fileHandle, $numBytes);
	}

	/**
	 * @param  $contents
	 * @return boolean
	 */
	public function write($contents) {
		if (!$this->isOpen()) {
			throw new RuntimeException('File is closed.', 1299863432);
		}

		return $this->storageDriver->writeToFile($this->fileHandle, $contents);
	}

	/**
	 * Sets the file pointer to the specified position or, if none is given, returns the current position.
	 *
	 * @param integer $position
	 * @param integer $seekMode  A seek mode as defined in the constants in t3lib_VFS; defaults to SEEK_MODE_SET
	 * @return FIXME
	 */
	public function seek($position = NULL, $seekMode = t3lib_VFS::SEEK_MODE_SET) {
		// TODO write unit tests
		return $this->storageDriver->seek($this->fileHandle, $position, $seekMode);
	}

	/**
	 * Moves this file to the specified path.
	 *
	 * WARNING: This operation might involve costly path calculations - use it only if you don't have the folder object
	 * of the target folder at hand! Otherwise, use moveToFolder().
	 *
	 * @param  $path The new path. If a filename is given, the file is renamed; if not, the old name is kept
	 * @return boolean TRUE if moving the file succeeded.
	 */
	public function moveToPath($path) {
		//
	}

	/**
	 * Moves this file to the specified folder. If $name is given, the file is also renamed.
	 *
	 * @param t3lib_vfs_Folder $folder
	 * @param string $name
	 * @return boolean TRUE if moving the file succeeded
	 */
	public function moveToFolder(t3lib_vfs_Folder $folder, $name = NULL) {
		//
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_file.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_file.php']);
}

?>
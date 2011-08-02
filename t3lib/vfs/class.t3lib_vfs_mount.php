<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Andreas Wolf <andreas.wolf@ikt-werk.de>
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
 * A mount point inside the TYPO3 virtual file system (VFS). 
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_vfs_Mount extends t3lib_vfs_Folder {

	/**
	 * The driver this folder comes from
	 *
	 * @var t3lib_vfs_driver_Abstract
	 */
	protected $storageDriver;

	/**
	 * Constructor for a mount object.
	 *
	 * @param array $folder The folder row from the database
	 */
	public function __construct(array $folder) {
		// TODO: check who creates folder objects (= where the config could come from)
		parent::__construct($folder);

		$this->mountpoint = $this;
	}

	/**
	 * Sets the driver that belongs to this mountpoint.
	 *
	 * @param t3lib_vfs_driver_Abstract $driver
	 * @return t3lib_vfs_Mount
	 */
	public function setStorageDriver(t3lib_vfs_driver_Abstract $driver) {
		$this->storageDriver = $driver;
		return $this;
	}

	/**
	 * Returns the storage driver for this mountpoint.
	 *
	 * @return t3lib_vfs_driver_Abstract
	 */
	public function getStorageDriver() {
		return $this->storageDriver;
	}

	/**
	 * Returns TRUE if this folder is the start of a new subtree
	 *
	 * @return boolean
	 */
	public function isMountpoint() {
		return TRUE;
	}

	public function setMountpoint(t3lib_vfs_Mount $mountpoint) {
		throw new LogicException('Setting the mountpoint for a mount object is not supported.', 1300101066);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_mount.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_mount.php']);
}

?>
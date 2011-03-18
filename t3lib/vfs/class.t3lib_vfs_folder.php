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
class t3lib_vfs_Folder extends t3lib_vfs_Node {

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
	 * The parent folder of this item
	 *
	 * @var t3lib_vfs_Folder
	 */
	protected $parent;

	/**
	 * The mount point this folder resides in. This is the basis of the subtree inside TYPO3s virtual file system
	 *
	 * @var t3lib_vfs_Mount
	 */
	protected $mountpoint;

	/**
	 * Constructor for a folder object.
	 *
	 * @param array $folder The folder row from the database
	 */
	public function __construct(array $folder) {
		// TODO: check who creates folder objects (= where the config could come from)
		$this->properties = $folder;
		$this->uid = $folder['uid'];
		$this->name = $folder['name'];
	}

	/**
	 * Returns the name of this folder
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the parent folder of this folder
	 *
	 * @param t3lib_vfs_Folder $parent
	 * @return void
	 */
	public function setParent(t3lib_vfs_Folder $parent) {
		// TODO check if id of parent is different from the pid given with the record -> behaviour in this case has to be defined
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Returns the parent of this folder.
	 *
	 * TODO define if this will return NULL if this is a mountpoint
	 *
	 * @return t3lib_vfs_Folder
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Creates a new folder below this folder
	 *
	 * @param  $name
	 * @param null|t3lib_vfs_driver_Abstract $driver
	 *        The driver to use. If this is given, the folder constitutes a new mountpoint inside the TYPO3 virtual file
	 *        system (VFS)
	 * @return void
	 */
	public function createSubfolder($name, t3lib_vfs_driver_Abstract $driver = NULL) {
		$storageDriver = $this->mountpoint->getStorageDriver();
		if (!$storageDriver->hasCapability(t3lib_vfs_driver_Abstract::CAPABILITY_SUPPORTS_FOLDERS)) {
			throw new RuntimeException("Driver for folder $this->uid does not support folder creation.", 1300287831);
		}
		// TODO get relative path of this folder, create folder

		$relativePath = $this->getPathInMountpoint() . '/' . $name;

		$storageDriver->createFolder($relativePath);
	}

	/**
	 * Returns the object for a subfolder of the current folder, if it exists.
	 *
	 * @param  $name
	 * @return t3lib_vfs_Folder
	 */
	public function getSubfolder($name) {
		/** @var $statement t3lib_db_PreparedStatement */
		static $statement;

		if (!$statement) {
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 't3lib_vfs_Folder', 'pid = :pid AND name = :name');
		}
		$statement->execute(array('pid' => $this->uid, 'name' => $name));

		if ($statement->rowCount() == 0) {
			throw new RuntimeException("Folder $this->uid has no subfolder '$name'.", 1300481287);
		}

		$folderRow = $statement->fetch();
		$statement->free();

		/** @var t3lib_vfs_Factory $factory */
		$factory = t3lib_div::makeInstance('t3lib_vfs_Factory');

		return $factory->getFolderObjectFromData($folderRow);
	}

	/**
	 * Returns a list of all subfolders; if it is given, the list is filtered by pattern.
	 *
	 * @return t3lib_vfs_Folder[]
	 */
	public function getSubfolders($pattern = '') {
		// TODO implement pattern matching
		/** @var $statement t3lib_db_PreparedStatement */
		static $statement;

		if (!$statement) {
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 't3lib_vfs_Folder', 'pid = :pid');
		}
		$statement->execute(array('pid' => $this->uid));

		if ($statement->rowCount() == 0) {
			throw new RuntimeException("Folder $this->uid has no subfolders.", 1300481288);
		}

		/** @var t3lib_vfs_Factory $factory */
		$factory = t3lib_div::makeInstance('t3lib_vfs_Factory');

		while ($row = $statement->fetch()) {
			$folders[] = $factory->getFolderObjectFromData($row);
		}
		$statement->free();

		return $folders;
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

	/**
	 * Returns TRUE if this folder is the start of a new subtree
	 *
	 * @return boolean
	 */
	public function isMountpoint() {
		return FALSE;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_folder.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_folder.php']);
}

?>
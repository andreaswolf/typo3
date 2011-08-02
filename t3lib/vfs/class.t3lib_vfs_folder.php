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
	 * The names of all properties this record has.
	 *
	 * @var array
	 */
	protected $availableProperties = array('pid', 'crdate', 'cruser_id', 'tstamp', 'name', 'driver', 'config');

	/**
	 * Constructor for a folder object.
	 *
	 * @param array $folder The folder row from the database
	 */
	public function __construct(array $folder) {
		// TODO: check who creates folder objects (= where the config could come from)
		parent::__construct($folder);
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
		return parent::setParent($parent);
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
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'sys_folder', 'pid = :pid AND name = :name');
		}
		$statement->execute(array(':pid' => $this->uid, ':name' => $name));

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
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'sys_folder', 'pid = :pid');
		}
		$statement->execute(array(':pid' => $this->uid));

		if ($statement->rowCount() == 0) {
			return array();
		}

		/** @var t3lib_vfs_Factory $factory */
		$factory = t3lib_div::makeInstance('t3lib_vfs_Factory');

		$folders = array();
		while ($row = $statement->fetch()) {
			$folders[] = $factory->getFolderObjectFromData($row);
		}
		$statement->free();

		return $folders;
	}

	/**
	 * Returns the file object for a given file name
	 *
	 * @param string $name The file name
	 * @return t3lib_vfs_File
	 *
	 * @throws RuntimeException If the file was not found
	 */
	public function getFile($name) {
		/** @var $statement t3lib_db_PreparedStatement */
		static $statement;

		if (!$statement) {
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'sys_file', 'pid = :pid AND name = :name');
		}
		$statement->execute(array(':pid' => $this->uid, ':name' => $name));

		if ($statement->rowCount() == 0) {
			throw new RuntimeException("Folder $this->uid contains no file '$name'.", 1300481287);
		}

		$fileRow = $statement->fetch();
		$statement->free();

		/** @var t3lib_vfs_Factory $factory */
		$factory = t3lib_div::makeInstance('t3lib_vfs_Factory');

		return $factory->getFileObjectFromData($fileRow);
	}

	/**
	 * Returns an array of file objects from this folder; if it is given, the list is filtered by pattern.
	 *
	 * @param string $pattern The pattern to search for. Optional.
	 * @return array
	 */
	public function getFiles($pattern = '') {
		/** @var $statement t3lib_db_PreparedStatement */
		static $statement;

		if (!$statement) {
			$statement = $GLOBALS['TYPO3_DB']->prepare_SELECTquery('*', 'sys_file', 'pid = :pid');
		}
		$statement->execute(array(':pid' => $this->uid));

		if ($statement->rowCount() == 0) {
			return array();
		}

		/** @var $factory t3lib_vfs_Factory */
		$factory = t3lib_div::makeInstance('t3lib_vfs_Factory');

		while ($row = $statement->fetch()) {
			$fileObjects[] = $factory->getFileObjectFromData($row);
		}

		return $fileObjects;
	}

	/**
	 * Returns TRUE if this folder is the start of a new subtree
	 *
	 * @return boolean
	 */
	public function isMountpoint() {
		return FALSE;
	}

	/**
	 * Returns the path to this folder, the folder's name NOT included by default
	 *
	 * @param bool $includeCurrent If this node should be included in the path
	 * @return string The node path separated by slashes
	 */
	public function getPath($includeCurrent = FALSE) {
		return parent::getPath($includeCurrent) . ($includeCurrent ? '/' : '');
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_folder.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_folder.php']);
}

?>
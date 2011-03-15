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
 * Factory class for VFS objects.
 *
 * NOTE: This class is part of the lowlevel VFS api and should not be used from outside the VFS package.
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package  TYPO3
 * @subpackage  t3lib
 */
class t3lib_vfs_Factory implements t3lib_Singleton {
	/**
	 * Returns a folder object for a given folder uid. The result
	 *
	 * @param  integer  $folderUid  The uid of the folder to return
	 * @return t3lib_vfs_Folder
	 *
	 * @throws InvalidArgumentException
	 */
	public function getFolderObject($folderUid) {
		static $instances;

		if (!is_int($folderUid)) {
			throw new InvalidArgumentException('uid of folder has to be numeric.', 1299957013);
		}

		if ($folderUid == 0) {
			return t3lib_div::makeInstance('t3lib_vfs_RootNode');
		}

		if (!$instances[$folderUid]) {
			$folderData = array(); // TODO really fetch folder data

			$instances[$folderUid] = $this->createFolderObject($folderData);
		}

		return $instances[$folderUid];
	}

	/**
	 * Creates a file object and injects all required dependencies.
	 *
	 * WARNING: This function does not cache created objects, unlike getFolderObject() does.
	 *
	 * @param  $folderData
	 * @return t3lib_vfs_File
	 */
	public function createFolderObject($folderData) {
		if ($folderData['driver']) {
			// folder is mountpoint
			$isMountpoint = TRUE;
			$class = 't3lib_vfs_Mount';
		} else {
			// regular folder
			$isMountpoint = FALSE;
			$class = 't3lib_vfs_Folder';
		}

		/** @var t3lib_vfs_Mount $folderObject */
		$folderObject = t3lib_div::makeInstance($class, $folderData);
		$parentFolder = $this->getFolderObject($folderData['pid']);

		if ($isMountpoint) {
			$driverObject = $this->getDriverInstance($folderData['driver'], $folderData['driverConfiguration']);
			$folderObject->setStorageDriver($driverObject);
		} else {
			$mountpoint = $parentFolder->getMountpoint();
			$folderObject->setMountpoint($mountpoint);
			$folderObject->setParent($parentFolder);
		}

		return $folderObject;
	}

	public function getFileObject($uid) {
		static $instances;

		if (!is_numeric($uid)) {
			throw new InvalidArgumentException('uid of file has to be numeric.', 1300096564);
		}

		if (!$instances[$uid]) {
			$fileData = array(); // TODO fetch file data

			$instances[$uid] = $this->createFileObject($fileData);
		}

		return $instances[$uid];
	}

	public function createFileObject($fileData) {
		// TODO
		/** @var t3lib_vfs_File $fileObject */
		$fileObject = t3lib_div::makeInstance('t3lib_vfs_File', $fileData);
	}

	protected function getDriverInstance($driver, $configuration) {
		$driverObject = t3lib_div::makeInstance($driver, $configuration);

		return $driverObject;
	}
}

?>
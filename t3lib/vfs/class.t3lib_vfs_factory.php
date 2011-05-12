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
	 * @var t3lib_vfs_Folder[]
	 */
	protected $folderInstances = array();

	/**
	 * @var t3lib_vfs_File[]
	 */
	protected $fileInstances = array();

	/**
	 * Returns a folder object for a given folder uid. The resulting object is cached.
	 *
	 * @param  integer  $folderUid  The uid of the folder to return
	 * @return t3lib_vfs_Folder
	 *
	 * @throws InvalidArgumentException
	 */
	public function getFolderObject($folderUid) {
		if (!is_numeric($folderUid)) {
			throw new InvalidArgumentException('uid of folder has to be numeric.', 1299957013);
		}

		if ($folderUid == 0) {
			return t3lib_div::makeInstance('t3lib_vfs_RootNode');
		}

		if (!$this->folderInstances[$folderUid]) {
			$folderData = array(); // TODO really fetch folder data

			$this->folderInstances[$folderUid] = $this->createFolderObject($folderData);
		}

		return $this->folderInstances[$folderUid];
	}

	/**
	 * Returns a folder object from given folder record data. The resulting object is cached, so it is also available
	 * via getFolderObject().
	 *
	 * WARNING: Only call this method with a complete folder record, otherwise the object will be missing information!
	 *
	 * @param array $folderData The data to construct the object from
	 * @return t3lib_vfs_Folder
	 *
	 * @throws InvalidArgumentException
	 */
	public function getFolderObjectFromData(array $folderData) {
		if (!is_numeric($folderData['uid'])) {
			throw new InvalidArgumentException('uid of folder has to be numeric.', 1299957014);
		}

		$folderUid = $folderData['uid'];
		if (!$this->folderInstances[$folderUid]) {
			$this->folderInstances[$folderUid] = $this->createFolderObject($folderData);
		}

		return $this->folderInstances[$folderUid];
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
			$class = 't3lib_vfs_Mount';
		} else {
			// regular folder
			$class = 't3lib_vfs_Folder';
		}

		/** @var t3lib_vfs_Mount $folderObject */
		$folderObject = t3lib_div::makeInstance($class, $folderData);
		$this->injectDependenciesForFolderObject($folderObject);

		return $folderObject;
	}

	protected function injectDependenciesForFolderObject(t3lib_vfs_Folder $folderObject) {
		$pid = $folderObject->getValue('pid');
		$parentFolder = $this->getFolderObject($pid);

		if ($folderObject->isMountpoint()) {
			$driverObject = $this->getDriverInstance($folderObject->getValue('driver'), $folderObject->getValue('driverConfiguration'));
			$folderObject->setStorageDriver($driverObject);
		} else {
			$mountpoint = $parentFolder->getMountpoint();
			$folderObject->setMountpoint($mountpoint);
			$folderObject->setParent($parentFolder);
		}
	}

	public function getFileObject($uid) {
		if (!is_numeric($uid)) {
			throw new InvalidArgumentException('uid of file has to be numeric.', 1300096564);
		}

		if (!$this->fileInstances[$uid]) {
			$fileData = array(); // TODO fetch file data

			$this->fileInstances[$uid] = $this->createFileObject($fileData);
		}

		return $this->fileInstances[$uid];
	}

	public function getFileObjectFromData($fileData) {
		if (!is_numeric($fileData['uid'])) {
			throw new InvalidArgumentException('uid of file has to be numeric.', 1300096565);
		}

		if (!$this->fileInstances[$fileData['uid']]) {
			$this->fileInstances[$fileData['uid']] = $this->createFileObject($fileData);
		}

		return $this->fileInstances[$fileData['uid']];
	}

	public function createFileObject($fileData) {
		/** @var t3lib_vfs_File $fileObject */
		$fileObject = t3lib_div::makeInstance('t3lib_vfs_File', $fileData);
		$this->injectDependenciesForFileObject($fileObject);

		return $fileObject;
	}

	protected function injectDependenciesForFileObject(t3lib_vfs_File $fileObject) {
		$pid = $fileObject->getValue('pid');
		$fileObject->setParent($this->getFolderObject($pid));
	}

	protected function getDriverInstance($driver, $configuration) {
		$driverObject = t3lib_div::makeInstance($driver, $configuration);

		return $driverObject;
	}
}

?>
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
 *  the Freef Software Foundation; either version 2 of the License, or
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
 * Indexer for the virtual file system
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package TYPO3
 * @subpackage t3lib
 *
 * TODO add mass/recursive indexing methods
 */
class t3lib_vfs_Indexer implements t3lib_Singleton {

	/**
	 * @var t3lib_vfs_Repository
	 */
	protected $repository;

	/**
	 * @var t3lib_vfs_Factory
	 */
	protected $factory;

	public function __construct() {
		$this->repository = t3lib_div::makeInstance('t3lib_vfs_Repository');
	}

	public function setRepository(t3lib_vfs_Repository $repository) {
		$this->repository = $repository;
		return $this;
	}

	public function setFactory(t3lib_vfs_Factory $factory) {
		$this->factory = $factory;
		return $this;
	}

	/**
	 * Indexes a node inside the VFS, and all nodes in the path if they are not indexed.
	 *
	 * @param string $path
	 * @return t3lib_vfs_Node The last node that has been indexed
	 */
	public function indexNodeAtPath($path) {
		/** @var $indexedNode t3lib_vfs_Node */
		list($indexedNode, $missingParts) = $this->repository->getNearestIndexedNode($path);

			// traverse all non-indexed path parts and index these items
		$currentNode = NULL;
		if (count($missingParts) > 0) {
				// use the current storage engine for all calculations, as we can be pretty sure that there is no other
				// mount down in the path
			$storage = $indexedNode->getMountpoint()->getStorageDriver();

			/** @var $currentNode t3lib_vfs_Folder */
			$currentNode = $indexedNode;
			foreach ($missingParts as $part) {
				$path = $currentNode->getPathInMountpoint();
				switch ($storage->getNodeType($path)) {
					case 'file':
						$currentNode = $this->indexFile($currentNode, $part);

						break;

					case 'dir':
						$currentNode = $this->indexFolder($currentNode, $part);

						break;
				}
			}
		} else {
			$currentNode = $indexedNode;
		}

		return $currentNode;
	}

	/**
	 * Indexes a file in a given folder. This is part of the "plumbing" API and should not be used unless really
	 * necessary. For normal usage, use indexNodeAtPath(), which just takes a path and cares about the rest (index
	 * parents etc.)
	 *
	 * @param string $relativePath The path of the file inside the VFS.
	 * @return string The indexed file
	 *
	 * @see indexNodeAtPath()
	 */
	public function indexFile(t3lib_vfs_Folder $parentFolder, $name) {
		/** @var $fileObject t3lib_vfs_File */
		// TODO use factory for this
		// $this->factory->createFileObject($filename);
		$fileObject = t3lib_div::makeInstance('t3lib_vfs_File', $name);
		$fileObject->setParent($parentFolder);

		$fileInfo = $this->gatherFileInformation($fileObject);

		foreach ($fileInfo as $key => $value) {
			$fileObject->setValue($key, $value);
		}

		$this->repository->persistNodeToDatabase($fileObject);

		return $fileObject;
	}

	/**
	 * Indexes a subfolder of a given folder. The subfolder has to physically exist.
	 *
	 * @param t3lib_vfs_Folder $folder The folder in which the subfolder resides
	 * @param string $subfolder The name of the folder to index
	 * @return t3lib_vfs_Folder The folder object
	 */
	public function indexFolder(t3lib_vfs_Folder $parentFolder, $subfolder) {
		$folderObject = t3lib_div::makeInstance('t3lib_vfs_Folder', array('name' => $subfolder));
		$folderObject->setParent($parentFolder);

		$this->repository->persistNodeToDatabase($folderObject);

		return $folderObject;
	}

	/**
	 * @return array
	 */
	protected function gatherFileInformation(t3lib_vfs_File $file) {
		$mountpoint = $file->getParent()->getMountpoint();
		$storageDriver =  $mountpoint->getStorageDriver();

		$fileStat = $storageDriver->stat($file);

		$fileInfo = array(
			'crdate' => $fileStat['ctime'],
			'timestamp' => $fileStat['mtime'],
			'size' => $fileStat['size'],
			'sha1' => $storageDriver->hash('sha1', $file)
		);

		return $fileInfo;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_indexer.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_indexer.php']);
}

?>
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
 * Repository for accessing files and folders.
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package	TYPO3
 * @subpackage	t3lib
 */
class t3lib_vfs_Repository implements t3lib_Singleton {

	/**
	 * Returns the root folder node of the file repository.
	 *
	 * @return t3lib_vfs_Folder The root folder
	 */
	public function getRootNode() {
		return t3lib_div::makeInstance('t3lib_vfs_RootNode');
	}

	public function putFileToPath($file, $path) {
		//
	}

	/**
	 * Traverses the virtual file system to get a folder node
	 *
	 * @param  $path
	 * @return void
	 */
	public function getFolderNode($path) {
		$pathParts = explode('/', $path);

		$node = $this->getRootNode();
		foreach ($pathParts as $pathPart) {
			$node = $node->getSubfolder($pathPart);
		}
	}

	public function updateNodeInDatabase(t3lib_vfs_Node $node) {
		$immutableProperties = array(
			'crdate',
			'cruser_id',
			'uid'
		);

		if (is_a($node, 't3lib_vfs_Folder')) {
			$table = 'sys_folder';
		} elseif (is_a($node, 't3lib_vfs_File')) {
			$table = 'sys_file';
		}
		$uid = (int)$node->getValue('uid');

		$changedProperties = $node->getChangedProperties();
		foreach ($immutableProperties as $property) {
			unset($changedProperties[$property]);
		}

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, "uid = $uid", $changedProperties);
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_folder.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_folder.php']);
}

?>
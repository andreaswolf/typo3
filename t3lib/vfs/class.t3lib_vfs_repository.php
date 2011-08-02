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
	 * @param string $path
	 * @return t3lib_vfs_Folder
	 */
	public function getFolderNode($path) {
		$path = trim($path, '/');
		$pathParts = explode('/', $path);

		$node = $this->getRootNode();
		foreach ($pathParts as $pathPart) {
			if ($pathPart === '') continue;

			$node = $node->getSubfolder($pathPart);
		}

		return $node;
	}

	/**
	 * Checks if all parts from a path are indexed and if not, returns the deepest node that is indexed and additionally
	 * all missing parts.
	 *
	 * NOTE: If the last element in the path is a folder, the path has to end with a slash
	 *
	 * @param string $path
	 * @return array The found node as first element, an array with all missing parts as second element
	 */
	public function getNearestIndexedNode($path) {
		$pathParts = explode('/', $path);

		$node = $this->getRootNode();
		while (count($pathParts) > 0) {
			$pathPart = array_shift($pathParts);
			if ($pathPart === '') continue;

			try {
				if (count($pathParts) == 0) {
					$node = $node->getFile($pathPart);
				} else {
					$node = $node->getSubfolder($pathPart);
				}
			} catch (RuntimeException $e) {
				array_unshift($pathParts, $pathPart);
				if ($node instanceof t3lib_vfs_RootNode) {
					$node = NULL;
				}
				return array($node, $pathParts);
			}
		}

		return array($node, array());
	}

	/**
	 * Writes the contents of a node to the database. Existing nodes are updated, for new nodes a new database row
	 * is created.
	 *
	 * @param t3lib_vfs_Node $node
	 * @return void
	 */
	public function persistNodeToDatabase(t3lib_vfs_Node $node) {
		$table = '';
		if (is_a($node, 't3lib_vfs_Folder')) {
			$table = 'sys_folder';
		} elseif (is_a($node, 't3lib_vfs_File')) {
			$table = 'sys_file';
		}

		if (!$node->isNew()) {
			$uid = (int)$node->getValue('uid');

			$this->updateRowInDatabase($table, $uid, $node->getChangedProperties());
		} else {
			$nodeStack = array();
			while ($node->isNew()) {
				$nodeStack[] = $node;
				$node = $node->getParent();
			}

			foreach (array_reverse($nodeStack) as $node) {
				$uid = $this->insertRowToDatabase($table, $node->getProperties());

				$node->setUid($uid);
			}
		}
	}

	/**
	 * Inserts a row into the database
	 *
	 * @param string $table
	 * @param array $properties
	 * @return
	 */
	protected function insertRowToDatabase($table, array $properties) {
		$immutableProperties = array(
			'cruser_id',
			'crdate'
		);

		foreach ($immutableProperties as $property) {
			unset($properties[$property]);
		}

		$properties['tstamp'] = time();

		/** @var $GLOBALS['TYPO3_DB'] t3lib_DB */
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $properties);
		if ($res === FALSE) {
			throw new RuntimeException("Inserting node failed: " . $GLOBALS['TYPO3_DB']->sql_error(), 1305389166);
		}
		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * Updates an existing row in database
	 *
	 * @param string $table
	 * @param int $uid
	 * @param array $properties
	 * @return
	 */
	protected function updateRowInDatabase($table, $uid, array $properties) {
		$immutableProperties = array(
			'crdate',
			'cruser_id',
			'uid'
		);

		foreach ($immutableProperties as $property) {
			unset($properties[$property]);
		}

		$properties['tstamp'] = time();

		return $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, "uid = $uid", $properties);
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_repository.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_repository.php']);
}

?>
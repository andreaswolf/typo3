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
 * Common base class for all nodes inside the VFS tree.
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package TYPO3
 * @subpackage t3lib
 */
abstract class t3lib_vfs_Node {

	protected $name;

	/**
	 * @var t3lib_vfs_Node
	 */
	protected $parent;

	/**
	 * The mountpoint this file is located in.
	 *
	 * @var t3lib_vfs_Mount
	 */
	protected $mountpoint;

	/**
	 * The names of all properties this record has. Set this in inherited classes.
	 *
	 * @var array
	 */
	protected $availableProperties = array();

	/**
	 * All properties of this record.
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Holds all properties that have been modified since the last update. The key is the name of the property, the
	 * value is the old property value.
	 *
	 * @var array
	 */
	protected $changedProperties = array();

	/**
	 * The uid of this node. Is -1 if this node has never been persisted to the database (i.e. it is freshly created)
	 *
	 * @var int
	 */
	protected $uid = -1;

	public function __construct(array $properties) {
		if (count($this->availableProperties) > 0) {
			$this->properties = t3lib_div::array_merge(
				array_combine($this->availableProperties, array_pad(array(), count($this->availableProperties), '')),
				$properties
			);
		} else {
			$this->properties = $properties;
		}
		$this->name = $this->properties['name'];
		if (isset($this->properties['uid'])) {
			$this->uid = $this->properties['uid'];
		}
	}

	public function setParent(t3lib_vfs_Node $parent) {
		$this->parent = $parent;
		$this->setValue('pid', $parent->getUid());

		if ($parent instanceof t3lib_vfs_Mount) {
			$this->setMountpoint($parent);
		}

		return $this;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * Returns TRUE if this node has the given property
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasProperty($name) {
		return array_key_exists($name, $this->properties);
	}

	/**
	 * Returns all properties of this object.
	 *
	 * @return array
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Returns the names of all changed properties.
	 *
	 * @return array
	 */
	public function getChangedPropertyNames() {
		return array_keys($this->changedProperties);
	}

	/**
	 * Returns an array containing the names (as keys) and original values of all changed properties.
	 *
	 * @return array
	 */
	public function getChangedProperties() {
		return $this->changedProperties;
	}

	/**
	 * Resets the internal array keeping the names (and original values) of changed properties.
	 *
	 * @return void
	 */
	public function resetChangedProperties() {
		$this->changedProperties = array();
	}

	/**
	 * Sets the given property to the specified value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return t3lib_vfs_Node This object
	 */
	public function setValue($name, $value) {
		if (!$this->hasProperty($name)) {
			throw new InvalidArgumentException("Property $name does not exist.", 1300127094);
		}

			// keep original value if property has been changed before
		if (!isset($this->changedProperties[$name])) {
			$this->changedProperties[$name] = $this->properties[$name];
		}

		$this->properties[$name] = $value;
		return $this;
	}

	/**
	 * Returns the value of the given property
	 *
	 * @param string $name The property to return
	 * @return mixed
	 */
	public function getValue($name) {
		return $this->properties[$name];
	}

	/**
	 * Returns the uid of this node; a value of -1 means it is new
	 *
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * Sets the uid of this record. This is only possible as long as the record has no uid, and should only be used
	 * by the database layer to inject the uid after creating a database record for it.
	 *
	 * @param int $uid
	 * @return t3lib_vfs_Node
	 */
	public function setUid($uid) {
		if (!$this->isNew()) {
			throw new LogicException("Can't change uid for existing records.", 1304785700);
		}
		$this->uid = $uid;
		$this->properties['uid'] = $uid;
		return $this;
	}

	/**
	 * Returns TRUE if this record has never been persisted to the database
	 *
	 * @return bool
	 */
	public function isNew() {
		return $this->uid == -1;
	}

	/**
	 * Sets the mountpoint this folder resides in. This might also be this folder itself (in case it is a mountpoint).
	 *
	 * @param t3lib_vfs_Mount $mountpoint
	 * @return t3lib_vfs_Folder This object
	 */
	public function setMountpoint(t3lib_vfs_Mount $mountpoint) {
		$this->mountpoint = $mountpoint;
		return $this;
	}

	/**
	 * Returns the mountpoint this folder resides in. The mountpoint is the root of a subtree inside the virtual file system.
	 * Usually, mountpoints are used to mount a different storage at a certain location.
	 *
	 * @return t3lib_vfs_Mount
	 */
	public function getMountpoint() {
		return $this->mountpoint;
	}

	public function isMountpoint() {
		return FALSE;
	}

	public function isRootNode() {
		return FALSE;
	}

	/**
	 * Returns the path to this node, the node's name NOT included by default
	 *
	 * @param bool $includeCurrent If this node should be included in the path
	 * @return string The node path separated by slashes
	 */
	public function getPath($includeCurrent = FALSE) {
		$pathParts = array();

		$pathInMountpoint = $this->getPathInMountpoint($includeCurrent);
		$node = $this->getMountpoint();
		while (!$node->isRootNode()) {
			$pathParts[] = $node->getName();
			$node = $node->getParent();
		}
		$pathParts = array_reverse($pathParts);

		return implode('/', $pathParts) . '/' . $pathInMountpoint;
	}

	/**
	 * Returns the path of this node inside its mountpoint, with the name of the mountpoint NOT included by default.
	 *
	 * @param bool $includeCurrent If this node should be included in the path 
	 * @return string The node path separated by slashes; if the current node is not included, it ends with a slash
	 */
	public function getPathInMountpoint($includeCurrent = FALSE) {
		$pathParts = array();

		if ($this->isMountpoint()) {
			return '';
		}

		if ($includeCurrent) {
			$pathParts[] = $this->getName();
		}
		$node = $this->getParent();
		while(!$node->isMountpoint()) {
			$pathParts[] = $node->getName();
			$node = $node->getParent();
		}
		$pathParts = array_reverse($pathParts);

		return implode('/', $pathParts) . ($includeCurrent ? '' : '/');
	}
}

?>
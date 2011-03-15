<?php

class t3lib_vfs_Node {

	protected $name;

	protected $parent;

	/**
	 * The mountpoint this file is located in.
	 *
	 * @var t3lib_vfs_Mount
	 */
	protected $mountpoint;

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

	public function __construct(array $properties) {
		$this->properties = $properties;
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
		if (!isset($this->properties[$name])) {
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
	 * @return t3lib_vfs_Folder
	 */
	public function getMountpoint() {
		return $this->mountpoint;
	}
}

?>
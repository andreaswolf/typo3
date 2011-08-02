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
 * File system driver.
 *
 * @author	Ingo Renner <ingo@typo3.org>
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package	TYPO3
 * @subpackage	t3lib
 */
abstract class t3lib_vfs_driver_Abstract {

	protected $configuration = array();

	/**
	 * A list of all supported hash algorithms, written all lower case and without any dashes etc. (e.g. sha1 instead of SHA-1)
	 *
	 * Be sure to set this in inherited classes!
	 *
	 * @var array
	 */
	protected $supportedHashAlgorithms = array();

	/**
	 * The capabilities of this driver. See CAPABILITY_* constants for possible values
	 *
	 * @var integer
	 */
	protected $capabilities;

	const CAPABILITY_WRITABLE = 1;
	const CAPABILITY_SUPPORTS_FOLDERS = 2;
	const CAPABILITY_RANDOM_ACCESS = 4;

	/**
	 * Constructor for t3lib_vfs_driver_Abstract.
	 *
	 * @param	array	$configuration Configuration parameters for the driver, such as user name and password for remote drivers for example.
	 */
	public function __construct($configuration) {
		$this->configuration = $configuration;
		$this->verifyConfiguration();
	}

	abstract protected function verifyConfiguration();

	/**
	 * Gets the complete configuration or a specific configuration option for
	 * the driver.
	 *
	 * @param	string	$option (optional) specific option name to get.
	 * @return	mixed	The complete configuration array or a specific (string) configuration option.
	 */
	public function getConfiguration($option = '') {
		$configuration = $this->configuration;

		if (!empty($option) && array_key_exists($option, $configuration)) {
			$configuration = $configuration[$option];
		}

		return $configuration;
	}

	public function getCapabilities() {
		return $this->capabilities;
	}

	public function hasCapability($capability) {
		return ($this->capabilities & $capability) == $capability;
	}

	/**
	 * Creates a new file and returns the matching file object for it.
	 *
	 * @abstract
	 * @param  $path
	 * @return void
	 */
	abstract public function createFile($path);

	/**
	 * @abstract
	 * @param t3lib_vfs_File $file
	 * @param string $mode
	 * @return mixed A file handle used for accessing the file. The type of this depends on the underlying storage.
	 */
	abstract public function getFileHandle(t3lib_vfs_File $file, $mode = 'r');

	/**
	 * Reads a given amount of bytes from a file handle, at max until EOF.
	 *
	 * @abstract
	 * @param  $handle
	 * @param  $numBytes
	 * @return mixed The contents read from the file
	 */
	abstract public function readFromFile(t3lib_vfs_FileHandle $handle, $numBytes);

	/**
	 * Writes given data to a file opened with file handle.
	 *
	 * @abstract
	 * @param  $handle
	 * @param  $contents
	 * @return void
	 */
	abstract public function writeToFile(t3lib_vfs_FileHandle $handle, $contents);

	abstract public function closeFileHandle(t3lib_vfs_FileHandle $handle);

	/**
	 * Returns the contents of a file. Beware that this requires to load the complete file into memory and also may
	 * require fetching the file from an external location. So this might be an expensive operation (both in terms of
	 * processing resources and money) for large files.
	 *
	 * @param t3lib_vfs_File $file
	 * @return string The file contents
	 */
	abstract public function getFileContents(t3lib_vfs_File $file);

	/**
	 * Moves the cursor to the specified position; if no position is given, the current position of the cursor is returned
	 *
	 * @param  $fileHandle
	 * @param  $position
	 * @param  $seekMode  The mode for setting the cursor position; one of the t3lib_VFS::SEEK_MODE_* constants
	 * @return void
	 */
	abstract public function seek(t3lib_vfs_FileHandle $fileHandle, $position = NULL, $seekMode = t3lib_VFS::SEEK_MODE_SET);

	/**
	 * Returns various information about a file. This heavily depends on the information provided by the underlying
	 * storage layer, so don't expect it to return much useful information.
	 *
	 * @abstract
	 * @param t3lib_vfs_File $file
	 * @return array
	 *
	 * @see http://de3.php.net/manual/de/function.stat.php
	 */
	abstract public function stat(t3lib_vfs_File $file);

	/**
	 * Returns a list of all hashing algorithms this driver supports.
	 *
	 * @return array
	 */
	public function getSupportedHashAlgorithms() {
		return $this->supportedHashAlgorithms;
	}

	/**
	 * Creates a (cryptographic) hash for a file.
	 *
	 * @abstract
	 * @param string $hashAlgorithm The hash algorithm to use
	 * @param t3lib_vfs_File $file
	 * @return string
	 */
	abstract public function hash($hashAlgorithm, t3lib_vfs_File $file);

	/**
	 * Returns the URL for publicly accessing a file.
	 *
	 * WARNING: There might be additional access checks outside of TYPO3 that prevent access to this file.
	 *
	 * @abstract
	 * @param t3lib_vfs_File $file
	 * @return void
	 */
	abstract public function getPublicUrl(t3lib_vfs_File $file);

	/**
	 * Creates a folder at the specified (relative) path.
	 *
	 * @abstract
	 * @param  $path The relative path
	 * @return t3lib_vfs_Folder
	 */
	abstract public function createFolder($path);

	/**
	 * Checks for existence of a given file or folder (with relative path inside this drivers base path)
	 *
	 * @abstract
	 * @param string $path The path to check
	 * @return bool
	 */
	abstract public function nodeExists($path);

	/**
	 * Returns the type of a node (one of directory, file)
	 *
	 * @abstract
	 * @param string $path The path to return the type for
	 * @return string
	 */
	abstract public function getNodeType($path);
}

?>
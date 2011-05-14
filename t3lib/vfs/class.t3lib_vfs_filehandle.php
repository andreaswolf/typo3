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
 * A file handle. This encapsulates a handle that can be used by a driver to directly access a file
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package  TYPO3
 * @subpackage  t3lib
 */
class t3lib_vfs_FileHandle {

	/**
	 * Capability constant: file handle is writable
	 */
	const CAP_WRITABLE = 1;

	/**
	 * Capability constant: file handle is seekable
	 */
	const CAP_SEEKABLE = 2;

	/**
	 * @var \t3lib_vfs_File
	 */
	protected $file;

	/**
	 * @var resource
	 */
	protected $handle;

	protected $open;

	/**
	 * The capabilites of this file handle
	 *
	 * @var integer
	 */
	protected $capabilities;

	/**
	 * @param t3lib_vfs_File $file
	 * @param resource $handle
	 * @param int $capabilities
	 *
	 * @throws InvalidArgumentException If the given handle is no resource
	 */
	public function __construct(t3lib_vfs_File $file, $handle, $capabilities = self::CAP_WRITABLE) {
		$this->handle = $handle;
		$this->file = $file;
		$this->capabilities = $capabilities;

		if (!is_resource($handle)) {
			throw new InvalidArgumentException('Given handle is no resource.', 1299841578);
		}
	}

	public function isOpen() {
		return $this->open;
	}

	public function getResource() {
		return $this->handle;
	}

	public function close() {
		fclose($this->handle);
	}

	public function hasCapability($capability) {
		return ($this->capabilities & $capability) === $capability;
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_filehandle.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/vfs/class.t3lib_vfs_filehandle.php']);
}

?>
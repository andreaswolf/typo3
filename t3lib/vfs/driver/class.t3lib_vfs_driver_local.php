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
 * Driver for the local file system
 *
 * @author  Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package	TYPO3
 * @subpackage	t3lib
 */
class t3lib_vfs_driver_Local extends t3lib_vfs_driver_Abstract {

	/**
	 * The absolute base path.
	 *
	 * @var string
	 */
	protected $absoluteBasePath;

	/**
	 * The base url to this drive
	 *
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * A list of all supported hash algorithms, written all lower case.
	 *
	 * @var array
	 */
	protected $supportedHashAlgorithms = array('sha1');

	protected function verifyConfiguration() {
		$this->absoluteBasePath = $this->configuration['basePath'];
		$this->absoluteBasePath = rtrim($this->absoluteBasePath, '/') . '/';

		if (!file_exists($this->absoluteBasePath) || !is_dir($this->absoluteBasePath)) {
			throw new RuntimeException("Base path $this->absoluteBasePath does not exist.", 1299233097);
		}
	}

	/**
	 * Converts a relative path inside this driver's root into an absolute path (with correct directory separators).
	 *
	 * @param  $relativePath
	 * @return string
	 */
	protected function makePathAbsolute($relativePath) {
		$path = $this->absoluteBasePath . $relativePath;
		//$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		// TODO implement directory separator conversion if it is not a slash

		return $path;
	}

	public function getAbsoluteBasePath() {
		return $this->absoluteBasePath;
	}

	public function getAbsolutePath(t3lib_vfs_File $file) {
		$path = $this->absoluteBasePath;

		$path .= $file->getPathInMountpoint(TRUE);
		return $path;
	}

	public function getPublicUrl(t3lib_vfs_File $file) {
		// TODO: Implement getPublicUrl() method.
	}

	public function stat(t3lib_vfs_File $file) {
		// TODO define which data should be returned
		// TODO write unit test
		$fileStat = stat($this->getAbsolutePath($file));

		$stat = array(
			'size' => $fileStat['size'],
			'atime' => $fileStat['atime'],
			'mtime' => $fileStat['mtime'],
			'ctime' => $fileStat['ctime'],
			'nlink' => $fileStat['nlink']
		);
		return $stat;
	}

	/**
	 * Creates a (cryptographic) hash for a file.
	 *
	 * @param string $hashAlgorithm The hash algorithm to use
	 * @param t3lib_vfs_File $file
	 * @return string
	 */
	public function hash($hashAlgorithm, t3lib_vfs_File $file) {
		if (!in_array($hashAlgorithm, $this->getSupportedHashAlgorithms())) {
			throw new InvalidArgumentException("Hash algorithm $hashAlgorithm is not supported.", 1304964032);
		}

		switch ($hashAlgorithm) {
			case 'sha1':
				$hash = sha1_file($this->getAbsolutePath($file));

				break;
		}

		return $hash;
	}

	public function getFileHandle(t3lib_vfs_File $file, $mode = 'r') {
		$filePath = $this->getAbsolutePath($file);

		$capabilities = NULL;
			// the file is opened writable for all modes except 'r' -- see http://de.php.net/manual/en/function.fopen.php
		if ($mode != 'r') {
			$capabilities = $capabilities | t3lib_vfs_FileHandle::CAP_WRITABLE;
		}

		$resourcePointer = fopen($filePath, $mode);
		if ($resourcePointer === FALSE) {
			throw new RuntimeException("Opening file \"$filePath\" in mode $mode failed.", 1299784211);
		}

		return new t3lib_vfs_FileHandle($file, $resourcePointer, $capabilities);
	}

	public function closeFileHandle(t3lib_vfs_FileHandle $handle) {
		// TODO check if additional things should be done in this method
		$handle->close();
	}

	/**
	 * Creates a folder at the specified (relative) path.
	 *
	 * @param  $path The relative path incl. the name of the new folder
	 * @return t3lib_vfs_Folder
	 */
	public function createFolder($path) {
		$path = $this->absoluteBasePath . $path;
		$name = basename($path);

		if (file_exists($path)) {
			throw new RuntimeException("Folder \"$path\" already exists.", 1299761890);
		}

		mkdir($path);
		return new t3lib_vfs_Folder(array('name' => $name));
	}

	/**
	 * Returns the contents of a file.
	 *
	 * @param t3lib_vfs_File $file
	 * @return void
	 */
	public function getFileContents(t3lib_vfs_File $file) {
		// TODO: Implement getFileContents() method.
	}

	/**
	 * Reads a given amount of bytes from a file handle, at max until EOF.
	 *
	 * @param  $handle
	 * @param  $numBytes
	 * @return mixed The contents read from the file
	 */
	public function readFromFile(t3lib_vfs_FileHandle $handle, $numBytes) {
		$resource = $handle->getResource();

		$fcontents = fread($resource, $numBytes);
		return $fcontents;
	}

	/**
	 * Writes given data to a file opened with file handle.
	 *
	 * @param  $handle
	 * @param  $contents
	 * @return void
	 */
	public function writeToFile(t3lib_vfs_FileHandle $handle, $contents) {
		$resource = $handle->getResource();

		if (!$handle->hasCapability(t3lib_vfs_FileHandle::CAP_WRITABLE)) {
			// TODO add file path
			throw new RuntimeException('File is not writable!', 1299851832);
		}

		fwrite($resource, $contents);
	}

	/**
	 * Moves the cursor to the specified position; if no position is given, the current position of the cursor is returned
	 *
	 * @param  $fileHandle
	 * @param  $position
	 * @return void
	 */
	public function seek(t3lib_vfs_FileHandle $fileHandle, $position = NULL, $seekMode = t3lib_VFS::SEEK_MODE_SET) {
		if ($position === NULL) {
			return ftell($fileHandle->getResource());
		} else {
			fseek($fileHandle->getResource(), $position, $seekMode);
		}
	}

	/**
	 * Creates a new file and returns the matching file object for it.
	 *
	 * @param  $path
	 * @return void
	 */
	public function createFile($path) {
		$path = $this->absoluteBasePath . $path;

		if (!file_exists(dirname($path))) {
			throw new RuntimeException("Folder \"" . dirname($path) . "\" does not exist.", 1299761888);
		}
		if (!is_dir(dirname($path))) {
			throw new RuntimeException("\"" . dirname($path) . "\" is not a folder.", 1299761889);
		}
		if (file_exists($path)) {
			throw new RuntimeException("File \"$path\" already exists.", 1299761887);
		}

			// using file_put_contents() because touch() doesn't work together with stream wrappers (see http://bugs.php.net/bug.php?id=38025)
		file_put_contents($path, '');
		return new t3lib_vfs_File(basename($path), NULL);
	}

	/**
	 * Checks for existence of a given file or folder (with relative path inside this drivers base path)
	 *
	 * @param string $path The path to check
	 * @return bool
	 */
	public function nodeExists($path) {
		$path = $this->makePathAbsolute($path);

		return file_exists($path);
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_file.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/file/class.t3lib_vfs_file.php']);
}

?>
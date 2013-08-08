<?php
namespace TYPO3\CMS\Core\Resource;

/**
 * A basic interface for files. It exposes all intrinsic properties of the file,
 * like its size, creation/modification date and contents.
 *
 * Implement this to get a basically usable file that can be used almost
 * everywhere a file is required.
 */
interface BasicFileInterface extends ResourceInterface {

	/**
	 * Returns the filename without its extension.
	 *
	 * WARNING: This might not strip the complete extension, e.g. for .tar.gz (because this is a tarball
	 * which is gzipped, thus the extension of the file itself is only .gz)
	 *
	 * @return string
	 */
	public function getNameWithoutExtension();

	public function getExtension();

	public function getCreationDate();

	public function getModificationDate();

	public function getContentHash($algorithm = NULL);

	public function getAvailableContentHashAlgorithms();

	public function getContent();

	public function getSize();

	public function getMimeType();
}

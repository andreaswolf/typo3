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

	public function getCreationTime();

	public function getModificationTime();

	public function getSha1();

	public function getContents();

	/**
	 * Replace the current file contents with the given string.
	 *
	 * @TODO : Consider to remove this function from the interface, as its
	 * @TODO : At the same time, it could be considered whether to make the whole
	 * @param string $contents The contents to write to the file.
	 * @return File The file object (allows chaining).
	 */
	public function setContents($contents);

	// TODO add setContents()?

	public function getSize();

	public function getMimeType();

	public function isPubliclyAvailable();

	public function getPublicUrl();

	public function delete();

	public function rename($newName);

	public function moveToFolder(FolderInterface $folder, $newName = NULL);

	/**
	 * Returns a path to a local version of this file to process it locally (e.g. with some system tool).
	 * If the file is normally located on a remote storage, this creates a local copy.
	 * If the file is already on the local system, this only makes a new copy if $writable is set to TRUE.
	 *
	 * @param bool $writable Set this to FALSE if you only want to do read operations on the file.
	 * @return string
	 */
	public function getForLocalProcessing($writable = TRUE);

	/**
	 * Returns an array representation of this object.
	 *
	 * @return array Array of main data of the file. Don't rely on all data to be present here, it's just a selection of the most relevant information.
	 *
	 * TODO decide if this should return all properties of the object, including intrinsic file properties (i.e. size etc.)
	 */
	public function toArray();

	/**
	 * Returns a modified version of the file.
	 *
	 * @param string $taskType The task type of this processing
	 * @param array $configuration the processing configuration, see manual for that
	 * @return ProcessedFile The processed file
	 */
	public function process($taskType, array $configuration);
}

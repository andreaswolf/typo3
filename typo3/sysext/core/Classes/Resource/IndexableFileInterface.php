<?php
namespace TYPO3\CMS\Core\Resource;

/**
 * Interface for files which can be indexed in the database.
 */
interface IndexableFileInterface {
	/**
	 * Returns the uid of this file record. Will return NULL if the file is not
	 * indexed.
	 *
	 * @return integer
	 */
	public function getUid();

	/**
	 * Returns the index status of this file (FALSE if it has been indexed)
	 *
	 * @return boolean
	 */
	public function _isNew();

	/**
	 * Returns all properties that have been changed in this instance of the
	 * file. Note that this only gives a useful result for files that have
	 * already been indexed.
	 *
	 * @return array
	 */
	public function getChangedProperties();

	/**
	 * Lifecycle method to show the object that it is "clean", e.g. after being
	 * reconstituted from the database.
	 *
	 * @return void
	 */
	public function _memorizeCleanState();

	/**
	 * Returns the clean, unchanged properties of this file (= the clean state
	 * memorized earlier).
	 *
	 * @return array
	 */
	public function _getCleanProperties();

	/**
	 * Returns TRUE if properties of this file have been changed. Use getProperties()
	 * and _getCleanProperties() to get the changed properties.
	 *
	 * @see TYPO3\CMS\Extbase\Persistence\ObjectMonitoringInterface
	 * @return boolean
	 */
	public function _isDirty();
}

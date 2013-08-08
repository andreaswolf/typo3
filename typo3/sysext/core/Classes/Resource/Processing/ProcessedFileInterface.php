<?php
namespace TYPO3\CMS\Core\Resource\Processing;

use TYPO3\CMS\Core\Resource\BasicFileInterface;

// TODO check if it is good to extend BasicFileInterface here
interface ProcessedFileInterface extends BasicFileInterface {
	public function getTask();

	public function getProcessingConfiguration(); // ?

	// alias for exists()?
	public function isProcessed();

	public function needsReprocessing();

	public function usesOriginalFile();

	public function getTaskIdentifier();
}

interface ProcessedStaticFile {
	// not stored in the database -> can only be found via the identifier
}

interface ProcessedEnrichedFile {
	// stored in the database -> has a uid
	// TODO check if this should inherit from IndexableInterface
}
<?php
namespace TYPO3\CMS\Core\Resource;

/**
 * A file that is localizable
 */
// TODO check if EnrichedFI should be the parent class of this one
// (or if there should be no parent at all -> it would be a stand-alone aspect of files)
interface LocalizableFileInterface extends EnrichedFileInterface {

	public function isLocalized(); // TODO does this method make sense?

	public function getAvailableLocales();

	public function loadLocalizedProperties($locale);
}
<?php
namespace TYPO3\CMS\Core\Resource;

/**
 * A file that is localizable
 */
// TODO check if RichFI should be the parent interface of this one
// (or if there should be no parent at all -> it would be a stand-alone aspect of files)
interface LocalizableFileInterface {

	public function isLocalized(); // TODO does this method make sense?

	public function getAvailableLocales(); // returns the available locales

	public function loadLocalizedProperties($locale);

	public function getCurrentLocale();

	/**
	 * Switches the locale the file uses for returning properties.
	 * If an array is given, it is treated as a fallback chain and the best
	 * matching locale is used.
	 *
	 * @param string|array $locale
	 */
	public function switchLocale($locale);

	/**
	 * Adds a set of localized properties to this object.
	 *
	 * @param string $locale
	 * @param array $properties
	 */
	public function addPropertiesForLocale($locale, $properties);
}

?>
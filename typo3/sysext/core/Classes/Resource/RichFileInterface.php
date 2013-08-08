<?php
namespace TYPO3\CMS\Core\Resource;

/**
 * A file with (optional) additional meta information.
 */
interface RichFileInterface extends BasicFileInterface {

	// TODO add property bag implementation here -> all default properties are in a "default" bag
	// TODO define if locale overlays should be done automatically (=> we also need a way to get the original property value then)

	public function hasProperty($key);

	public function getProperty($key);

	public function setProperty($key, $value);

	public function getProperties();

	public function getAvailableProperties();
}

?>
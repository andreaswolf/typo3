<?php
namespace TYPO3\CMS\Backend\Form\Suggest;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Interface for a FormEngine suggest result renderer
 *
 * @author Andreas Wolf <andreas.wolf@typo3.org>
 */
interface ResultRenderer {

	/**
	 * @param SearchResult $result
	 * @return ResultRenderer
	 */
	public function __construct(SearchResult $result);

	/**
	 * Renders the results and returns them as a string
	 *
	 * @return string
	 */
	public function render();

	/**
	 * @param $suffix
	 * @return void
	 */
	public function setRowIdSuffix($suffix);

}

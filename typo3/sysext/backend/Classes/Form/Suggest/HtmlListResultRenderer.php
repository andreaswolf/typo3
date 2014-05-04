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
 * HTML list result renderer for the FormEngine suggest wizard
 *
 * @author Andreas Wolf <andreas.wolf@typo3.org>
 */
class HtmlListResultRenderer implements ResultRenderer {

	/**
	 * The tag to wrap the list in. One of "ul", "li"; "div" is also possible, but requires a change to the rest of
	 * the generated code.
	 *
	 * @var string
	 */
	protected $listTag = 'ul';

	/**
	 * The CSS class for the list tag.
	 *
	 * @var string
	 */
	protected $cssClass = '';

	/**
	 * The suffix for the row identifiers.
	 *
	 * @var string
	 */
	protected $rowIdSuffix = '';

	/**
	 * The search result to render.
	 *
	 * @var SearchResult
	 */
	protected $result;

	public function __construct(SearchResult $result) {
		$this->result = $result;
	}

	/**
	 * Renders the given results and returns them as a string
	 *
	 * @return string
	 */
	public function render() {
		$listItems = $this->createListItemsFromResultRow($this->result->getRecords());

		if (count($listItems) > 0) {
			$list = implode('', $listItems);
		} else {
			$list = '<li class="suggest-noresults"><i>'
				. $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.noRecordFound') . '</i></li>';
		}
		$list = '<' . $this->listTag . ' class="' . $this->cssClass . '-resultlist">' . $list . '</' . $this->listTag . '>';

		return $list;
	}

	/**
	 * Sets the list's css class
	 *
	 * @param $class
	 */
	public function setListCssClass($class) {
		$this->cssClass = $class;
	}

	/**
	 * @param string $rowIdSuffix
	 */
	public function setRowIdSuffix($rowIdSuffix) {
		$this->rowIdSuffix = $rowIdSuffix;
	}

	/**
	 * Creates a list of <li> elements from a list of results returned by the receiver. The results have to be ordered
	 * as they should be displayed.
	 *
	 * @param array $resultRows
	 * @return array
	 */
	protected function createListItemsFromResultRow($resultRows) {
		if (count($resultRows) === 0) {
			return array();
		}
		$listItems = array();

		// put together the selector entries
		$items = count($resultRows);
		for ($i = 0; $i < $items; ++$i) {
			$row = $resultRows[$i];
			$rowId = $row['table'] . '-' . $row['uid'] . $this->rowIdSuffix;
			$listItems[] = '<li' . ($row['class'] != '' ? ' class="' . $row['class'] . '"' : '') . ' id="' . $rowId
				. '"' . ($row['style'] != '' ? ' style="' . $row['style'] . '"' : '') . '>'
				. $row['text']
				. '</li>';
		}

		return $listItems;
	}

}

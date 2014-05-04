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
 * Search results for a suggest request
 *
 * @author Andreas Wolf <andreas.wolf@typo3.org>
 */
class SearchResult {

	/**
	 * @var string
	 */
	protected $query;

	/**
	 * The rows that were found.
	 *
	 * @var array
	 */
	protected $records;

	/**
	 * @param string $query
	 */
	public function __construct($query) {
		$this->query = $query;
	}

	/**
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @param array $results
	 */
	public function setRecords($results) {
		$this->records = $this->sortRows($results);
	}

	/**
	 * @return array
	 */
	public function getRecords() {
		return $this->records;
	}

	/**
	 * Traverses the given records and sort them by their "text" key
	 *
	 * @param $resultRows
	 * @return array
	 */
	protected function sortRows($resultRows) {
		$recordsWithSortingKey = array();
		foreach ($resultRows as $key => $row) {
			$recordsWithSortingKey[$key] = $row['text'];
		}
		asort($recordsWithSortingKey);
		$sortedIndices = array_keys($recordsWithSortingKey);
		unset($recordsWithSortingKey);

		$sortedRecords = array();
		for ($i = 0; $i < count($resultRows); ++$i) {
			$sortedRecords[$i] = $resultRows[$sortedIndices[$i]];
		}
		return $sortedRecords;
	}
}
 
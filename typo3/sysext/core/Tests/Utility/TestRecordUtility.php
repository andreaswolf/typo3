<?php
namespace TYPO3\CMS\Core\Tests\Utility;

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Andreas Wolf <andreas.wolf@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Utility to prepare records for tests.
 */
class TestRecordUtility {
	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	public function __construct() {
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Returns a data map as expected by DataHandler (TCEmain) for inserting a record into $table.
	 *
	 * @param string $table
	 * @param array $recordData
	 * @return array
	 */
	protected function createDatamapForInsertingRecord($table, array $recordData) {
		return array(
			$table => array(
				'NEW' . uniqid() => $recordData
			)
		);
	}

	/**
	 * Returns a data map for updating the given record in DataHandler/TCEmain
	 *
	 * @param string $table
	 * @param integer $uid
	 * @param array $updatedData
	 * @return array
	 */
	protected function createDatamapForUpdatingRecord($table, $uid, array $updatedData) {
		unset($updatedData['uid']);

		$recordData = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordRaw($table, 'uid=' . (int)$uid);
		$updatedData = array_merge($recordData, $updatedData);

		return array(
			$table => array(
				$uid => $updatedData
			)
		);
	}

	/**
	 * Returns a command map for updating the given record in DataHandler/TCEmain
	 *
	 * @param string $table
	 * @param integer $uid
	 * @return array
	 */
	protected function createCommandmapForUpdatingRecord($table, $uid) {
		return array(
			$table => array(
				$uid => array(
					'edit' => 1
				)
			)
		);
	}

	/**
	 * Makes sure that the backend user with the given name and password exists. Additional properties may also be specified.
	 * If the user already exists, the password and other properties will just be updated
	 *
	 * @param string $username
	 * @param string $password
	 * @param bool $isAdministrator
	 * @param array $additionalInformation
	 */
	public function ensureBackendUserExists($username, $password, $isAdministrator = FALSE, $additionalInformation = array()) {
		$matchFields = array('username' => $username);
		$additionalInformation = array_merge(
			array( // These values are "defaults" for the record
				'disable' => 0
			),
			$additionalInformation,
			array(
				'password' => md5($password),
				'admin' => ($isAdministrator ? 1 : 0),
				'pid' => 0
			)
		);

		$this->ensureRecordPresent('be_users', $matchFields, $additionalInformation);
	}

	/**
	 * @param string $table
	 * @param array $matchFields
	 * @param array $additionalFields
	 * @throws \RuntimeException When there is more than one record found and we're not sure which to update
	 */
	public function ensureRecordPresent($table, array $matchFields, array $additionalFields) {

		// Create an array with values "field=value" for the WHERE clause
		$whereStringFields = array();
		array_walk($matchFields, function($value, $key) use ($table, &$whereStringFields) {
			if (is_string($value)) {
				$value = $this->databaseConnection->fullQuoteStr($value, $table);
			}

			$whereStringFields[$key] = $key . ' = ' . $value;
		});

		$whereString = implode(' AND ', $whereStringFields);
		$rows = $this->databaseConnection->exec_SELECTgetRows('*', $table, $whereString);
		$count = count($rows);

		if ($count == 0) {
			// we're merging the match fields into the additional fields so the match fields always win
			$datamap = $this->createDatamapForInsertingRecord($table, array_merge($additionalFields, $matchFields));
			$cmdmap = array(); // no command map required for inserting a record
		} elseif ($count == 1) {
			// The match fields should not be updated in an existing record
			$datamap = $this->createDatamapForUpdatingRecord($table, $rows[0]['uid'], $additionalFields);
			$cmdmap = $this->createCommandmapForUpdatingRecord($table, $rows[0]['uid']);
		} else {
			throw new \RuntimeException(
				sprintf('More than one record found in table %s for match criteria %s', $table, $whereString)
			);
		}

		if ($datamap) {
			/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
			$dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');

			$dataHandler->start($datamap, $cmdmap);
			$dataHandler->process_datamap();
			$dataHandler->process_cmdmap();

			// TODO record uids of created/updated records
		}
	}
}
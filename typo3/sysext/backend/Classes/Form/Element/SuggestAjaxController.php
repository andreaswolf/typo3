<?php
namespace TYPO3\CMS\Backend\Form\Element;

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

use TYPO3\CMS\Backend\Form\Suggest;
use TYPO3\CMS\Backend\Form\Wizard\SuggestWizardDefaultReceiver;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class SuggestAjaxController {

	/**
	 * The string that was entered by the user
	 *
	 * @var string
	 */
	protected $search;

	/**
	 * The table the record this query came from resides in.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The (suggest-enabled) field they query came from.
	 *
	 * @var string
	 */
	protected $field;

	/**
	 * The UID of the parent record.
	 *
	 * @var integer
	 */
	protected $parentRecordUid;

	/**
	 * The page the record this query came from resides on; if the record is a page itself, this is identical to
	 * $this->parentRecordUid.
	 *
	 * @var integer
	 */
	protected $parentPageId;

	/**
	 * The TSconfig of the parent page of the current record (or the current page, if the query came from a page field)
	 *
	 * @var array
	 */
	protected $parentPageTsConfig;

	/**
	 * The configuration of the suggest wizard
	 *
	 * @var array
	 */
	protected $wizardConfiguration;

	/**
	 * The configuration of the current field.
	 *
	 * @var array
	 */
	protected $fieldConfiguration = array();

	/**
	 * The default where clause based on the field configuration.
	 *
	 * @var string
	 */
	protected $defaultWhereClause;

	/**
	 * @var string
	 */
	protected $cssClass = 'typo3-TCEforms-suggest';

	/**
	 * Ajax handler for the "suggest" feature in TCEforms.
	 *
	 * @param array $params The parameters from the AJAX call
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj The AJAX object representing the AJAX call
	 * @return void
	 */
	public function processAjaxRequest($params, &$ajaxObj) {
		// Get parameters from $_GET/$_POST
		$this->search = GeneralUtility::_GP('value');
		$this->table = GeneralUtility::_GP('table');
		$this->parentRecordUid = GeneralUtility::_GP('uid');
		$this->field = GeneralUtility::_GP('field');
		$this->parentPageId = GeneralUtility::_GP('pid');
		$newRecordRow = GeneralUtility::_GP('newRecordRow');

		$searchResults = new Suggest\SearchResult($this->search);

		// If the current record's uid is numeric, we have an already existing element, so get the
		// TSconfig of the page itself or the element container (for non-page elements)
		// otherwise it's a new element, so use given id of parent page (i.e., don't modify it here)
		$row = NULL;
		if (is_numeric($this->parentRecordUid)) {
			$row = BackendUtility::getRecord($this->table, $this->parentRecordUid);
			if ($this->table == 'pages') {
				$this->parentPageId = $this->parentRecordUid;
			} else {
				$this->parentPageId = $row['pid'];
			}
		} else {
			$row = unserialize($newRecordRow);
		}
		$this->parentPageTsConfig = BackendUtility::getPagesTSconfig($this->parentPageId);

		$this->overrideFieldNameAndConfigurationForFlexform($this->table, $this->field, $row, $this->fieldConfiguration);
		$this->wizardConfiguration = $this->fieldConfiguration['wizards']['suggest'];
		$this->defaultWhereClause = $this->setWhereClause($this->fieldConfiguration);

		$queryTables = $this->getTablesToQueryFromFieldConfiguration($this->fieldConfiguration);

		$resultRows = array();

		// fetch the records for each query table. A query table is a table from which records are allowed to
		// be added to the TCEForm selector, originally fetched from the "allowed" config option in the TCA
		foreach ($queryTables as $queryTable) {
			// if the table does not exist, skip it
			if (!is_array($GLOBALS['TCA'][$queryTable]) || !count($GLOBALS['TCA'][$queryTable])) {
				continue;
			}

			$rows = $this->queryTable($queryTable, $params);

			if (empty($rows)) {
				continue;
			}
			$resultRows = $rows + $resultRows;
			unset($rows);
		}

		// Limit the number of items in the result list
		$maxItems = $this->fieldConfiguration['maxItemsInResultList'] ? : 10;
		$maxItems = min(count($resultRows), $maxItems);

		$resultRows = array_slice($resultRows, 0, $maxItems);
		$searchResults->setRecords($resultRows);

		$rowIdSuffix = '-' . $this->table . '-' . $this->parentRecordUid . '-' . $this->field;

		$resultRenderer = new Suggest\HtmlListResultRenderer($searchResults);
		$resultRenderer->setListCssClass($this->cssClass);
		$resultRenderer->setRowIdSuffix($rowIdSuffix);

		$renderedResults = $resultRenderer->render($resultRows);
		$ajaxObj->addContent(0, $renderedResults);
	}

	/**
	 * Queries the given tables.
	 *
	 * @param string $table
	 * @return array
	 */
	protected function queryTable($table) {
		$tableConfiguration = $this->getConfigurationForTable($table);

		// instantiate the class that should fetch the records for this $queryTable
		$receiverClassName = $tableConfiguration['receiverClass'];
		if ($receiverClassName == '' || !class_exists($receiverClassName)) {
			$receiverClassName = SuggestWizardDefaultReceiver::class;
		}
		$receiverObj = GeneralUtility::makeInstance($receiverClassName, $table, $tableConfiguration);
		$queryParameters = array('value' => $this->search);
		$rows = $receiverObj->queryTable($queryParameters);

		return $rows;
	}

	/**
	 * Returns the configuration for the suggest wizard for the given table. This does multiple overlays from the
	 * TSconfig.
	 *
	 * @param string $queryTable The table to query
	 * @return mixed
	 */
	protected function getConfigurationForTable($queryTable) {
		$tableConfiguration = (array)$this->wizardConfiguration['default'];

		if (is_array($this->wizardConfiguration[$queryTable])) {
			ArrayUtility::mergeRecursiveWithOverrule($tableConfiguration, $this->wizardConfiguration[$queryTable]);
		}
		$globalSuggestTsConfig = $this->parentPageTsConfig['TCEFORM.']['suggest.'];
		$currentFieldSuggestTsConfig = $this->parentPageTsConfig['TCEFORM.'][$this->table . '.'][$this->field . '.']['suggest.'];

		// merge the configurations of different "levels" to get the working configuration for this table and
		// field (i.e., go from the most general to the most special configuration)
		if (is_array($globalSuggestTsConfig['default.'])) {
			ArrayUtility::mergeRecursiveWithOverrule($tableConfiguration, $globalSuggestTsConfig['default.']);
		}
		if (is_array($globalSuggestTsConfig[$queryTable . '.'])) {
			ArrayUtility::mergeRecursiveWithOverrule($tableConfiguration, $globalSuggestTsConfig[$queryTable . '.']);
		}

		// use $table instead of $queryTable here because we overlay a config
		// for the input-field here, not for the queried table
		if (is_array($currentFieldSuggestTsConfig['default.'])) {
			ArrayUtility::mergeRecursiveWithOverrule($tableConfiguration, $currentFieldSuggestTsConfig['default.']);
		}

		if (is_array($currentFieldSuggestTsConfig[$queryTable . '.'])) {
			ArrayUtility::mergeRecursiveWithOverrule($tableConfiguration, $currentFieldSuggestTsConfig[$queryTable . '.']);
		}

		// process addWhere
		if (!isset($tableConfiguration['addWhere']) && $this->defaultWhereClause) {
			$tableConfiguration['addWhere'] = $this->defaultWhereClause;
		}
		if (isset($tableConfiguration['addWhere'])) {
			$replacement = array(
				'###THIS_UID###' => (int)$this->parentRecordUid,
				'###CURRENT_PID###' => (int)$this->parentPageId
			);
			if (isset($this->parentPageTsConfig['TCEFORM.'][$this->table . '.'][$this->field . '.'])) {
				$fieldTSconfig = $this->parentPageTsConfig['TCEFORM.'][$this->table . '.'][$this->field . '.'];
				if (isset($fieldTSconfig['PAGE_TSCONFIG_ID'])) {
					$replacement['###PAGE_TSCONFIG_ID###'] = (int)$fieldTSconfig['PAGE_TSCONFIG_ID'];
				}
				if (isset($fieldTSconfig['PAGE_TSCONFIG_IDLIST'])) {
					$replacement['###PAGE_TSCONFIG_IDLIST###'] = $GLOBALS['TYPO3_DB']->cleanIntList($fieldTSconfig['PAGE_TSCONFIG_IDLIST']);
				}
				if (isset($fieldTSconfig['PAGE_TSCONFIG_STR'])) {
					$replacement['###PAGE_TSCONFIG_STR###'] = $GLOBALS['TYPO3_DB']->quoteStr($fieldTSconfig['PAGE_TSCONFIG_STR'], $this->fieldConfiguration['foreign_table']);
				}
			}
			$tableConfiguration['addWhere'] = strtr(' ' . $tableConfiguration['addWhere'], $replacement);
		}

		return $tableConfiguration;
	}

	/**
	 * Checks the given field configuration for the tables that should be used for querying and returns them as an
	 * array.
	 *
	 * @param array $fieldConfig
	 * @return array
	 */
	protected function getTablesToQueryFromFieldConfiguration($fieldConfig) {
		$queryTables = array();

		if (isset($fieldConfig['allowed'])) {
			if ($fieldConfig['allowed'] !== '*') {
				// list of allowed tables
				$queryTables = GeneralUtility::trimExplode(',', $fieldConfig['allowed']);
			} else {
				// all tables are allowed, if the user can access them
				foreach ($GLOBALS['TCA'] as $tableName => $tableConfig) {
					if (!$this->isTableHidden($tableConfig) && $this->currentBackendUserMayAccessTable($tableConfig)) {
						$queryTables[] = $tableName;
					}
				}
				unset($tableName, $tableConfig);
			}
		} elseif (isset($fieldConfig['foreign_table'])) {
			// use the foreign table
			$queryTables = array($fieldConfig['foreign_table']);
		}

		return $queryTables;
	}

	/**
	 * Returns the SQL WHERE clause to use for querying records. This is currently only relevant if a foreign_table
	 * is configured and should be used; it could e.g. be used to limit to a certain subset of records from the
	 * foreign table
	 *
	 * @param array $fieldConfig
	 * @return string
	 */
	protected function setWhereClause($fieldConfig) {
		if (!isset($fieldConfig['foreign_table'])) {
			return '';
		}

		$foreign_table_where = $fieldConfig['foreign_table_where'];
		// strip ORDER BY clause
		$foreign_table_where = trim(preg_replace('/ORDER[[:space:]]+BY.*/i', '', $foreign_table_where));

		return $foreign_table_where;
	}

	/**
	 * Returns TRUE if a table has been marked as hidden in the configuration
	 *
	 * @param array $tableConfig
	 * @return bool
	 */
	protected function isTableHidden($tableConfig) {
		return !(bool)$tableConfig['ctrl']['hideTable'];
	}

	/**
	 * Checks if the current backend user is allowed to access the given table, based on the ctrl-section of the
	 * table's configuration array (TCA) entry.
	 *
	 * @param array $tableConfig
	 * @return bool
	 */
	protected function currentBackendUserMayAccessTable($tableConfig) {
		if ($GLOBALS['BE_USER']->isAdmin()) {
			return TRUE;
		}

		// If the user is no admin, they may not access admin-only tables
		if ((bool)$tableConfig['ctrl']['adminOnly'] === TRUE) {
			return FALSE;
		}

		// allow access to root level pages if security restrictions should be bypassed
		if ((bool)$tableConfig['ctrl']['rootLevel'] === FALSE
		    || (bool)$tableConfig['ctrl']['security']['ignoreRootLevelRestriction'] === TRUE) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Checks if the query comes from a Flexform element and if yes, resolves the field configuration from the Flexform
	 * data structure.
	 *
	 * @param string $table
	 * @param string &$field The field identifier, either a simple table field or a Flexform field path separated with |
	 * @param array $row The row we're dealing with; optional (only required for Flexform records)
	 * @param array &$fieldConfig
	 */
	protected function overrideFieldNameAndConfigurationForFlexform($table, &$field, $row, &$fieldConfig) {
		// check if field is a flexform reference
		if (strpos($field, '|') === FALSE) {
			$fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
		} else {
			$parts = explode('|', $field);

			if ($GLOBALS['TCA'][$table]['columns'][$parts[0]]['config']['type'] !== 'flex') {
				return;
			}

			if (!is_array($row) || count($row) === 0) {
				return;
			}

			$flexfieldTCAConfig = $GLOBALS['TCA'][$table]['columns'][$parts[0]]['config'];
			$flexformDSArray = BackendUtility::getFlexFormDS($flexfieldTCAConfig, $row, $table);
			$flexformDSArray = GeneralUtility::resolveAllSheetsInDS($flexformDSArray);
			$flexformElement = $parts[count($parts) - 2];
			$continue = TRUE;
			foreach ($flexformDSArray as $sheet) {
				foreach ($sheet as $_ => $dataStructure) {
					$fieldConfig = $this->getNestedDsFieldConfig($dataStructure, $flexformElement);
					if (count($fieldConfig) > 0) {
						$continue = FALSE;
						break;
					}
				}
				if (!$continue) {
					break;
				}
			}
			// Flexform field name levels are separated with | instead of encapsulation in [];
			// reverse this here to be compatible with regular field names.
			$field = str_replace('|', '][', $field);
		}
	}

	/**
	 * Search a data structure array recursively -- including within nested
	 * (repeating) elements -- for a particular field config.
	 *
	 * @param array $dataStructure The data structure
	 * @param string $fieldName The field name
	 * @return array
	 */
	protected function getNestedDsFieldConfig(array $dataStructure, $fieldName) {
		$fieldConfig = array();
		$elements = $dataStructure['ROOT']['el'] ? $dataStructure['ROOT']['el'] : $dataStructure['el'];
		if (is_array($elements)) {
			foreach ($elements as $k => $ds) {
				if ($k === $fieldName) {
					$fieldConfig = $ds['TCEforms']['config'];
					break;
				} elseif (isset($ds['el'][$fieldName]['TCEforms']['config'])) {
					$fieldConfig = $ds['el'][$fieldName]['TCEforms']['config'];
					break;
				} else {
					$fieldConfig = $this->getNestedDsFieldConfig($ds, $fieldName);
				}
			}
		}
		return $fieldConfig;
	}

}

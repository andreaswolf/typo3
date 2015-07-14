<?php
namespace TYPO3\CMS\Backend\Form\Wizard;

/*
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

use TYPO3\CMS\Backend\Form\Suggest\HtmlListResultRenderer;
use TYPO3\CMS\Backend\Form\Suggest\SearchResult;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Backend\Form\Wizard\SuggestWizardDefaultReceiver;

/**
 * Wizard for rendering an AJAX selector for records
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @author Benjamin Mack <benni@typo3.org>
 */
class SuggestWizard {

	/**
	 * @var string
	 */
	protected $cssClass = 'typo3-TCEforms-suggest';

	/**
	 * Renders an ajax-enabled text field. Also adds required JS
	 *
	 * @param string $fieldname The fieldname in the form
	 * @param string $table The table we render this selector for
	 * @param string $field The field we render this selector for
	 * @param array $row The row which is currently edited
	 * @param array $config The TSconfig of the field
	 * @return string The HTML code for the selector
	 */
	public function renderSuggestSelector($fieldname, $table, $field, array $row, array $config) {
		$languageService = $this->getLanguageService();
		$containerCssClass = $this->cssClass . ' ' . $this->cssClass . '-position-right';
		$suggestId = 'suggest-' . $table . '-' . $field . '-' . $row['uid'];
		$jsRow = '';

		$isFlexFormField = $GLOBALS['TCA'][$table]['columns'][$field]['config']['type'] === 'flex';
		if ($isFlexFormField) {
			$fieldPattern = 'data[' . $table . '][' . $row['uid'] . '][';
			$flexformField = str_replace($fieldPattern, '', $fieldname);
			$flexformField = substr($flexformField, 0, -1);
			$field = str_replace(array(']['), '|', $flexformField);

			if (!MathUtility::canBeInterpretedAsInteger($row['uid'])) {
				// Ff we have a new record, we hand that row over to JS.
				// This way we can properly retrieve the configuration of our wizard
				// if it is shown in a flexform
				$jsRow = serialize($row);
			}
		}

		$selector = '
		<div class="' . $containerCssClass . '" id="' . $suggestId . '">
			<div class="input-group">
				<span class="input-group-addon"><i class="fa fa-search"></i></span>
				<input type="search" id="' . htmlspecialchars($fieldname) . 'Suggest" placeholder="' . $languageService->sL('LLL:EXT:lang/locallang_core.xlf:labels.findRecord') . '" class="form-control ' . $this->cssClass . '-search" />
				<div class="' . $this->cssClass . '-indicator" style="display: none;" id="' . htmlspecialchars($fieldname) . 'SuggestIndicator">
					<span class="fa fa-spin fa-spinner" title="' . $languageService->sL('LLL:EXT:lang/locallang_core.xlf:alttext.suggestSearching', TRUE) . '"></span>
				</div>
				<div class="' . $this->cssClass . '-choices" style="display: none;" id="' . htmlspecialchars($fieldname) . 'SuggestChoices"></div>
			</div>
		</div>';
		// Get minimumCharacters from TCA
		$minChars = 0;
		if (isset($config['fieldConf']['config']['wizards']['suggest']['default']['minimumCharacters'])) {
			$minChars = (int)$config['fieldConf']['config']['wizards']['suggest']['default']['minimumCharacters'];
		}
		// Overwrite it with minimumCharacters from TSConfig (TCEFORM) if given
		if (isset($config['fieldTSConfig']['suggest.']['default.']['minimumCharacters'])) {
			$minChars = (int)$config['fieldTSConfig']['suggest.']['default.']['minimumCharacters'];
		}
		$minChars = $minChars > 0 ? $minChars : 2;

		// fetch the TCA field type to hand it over to the JS class
		$type = '';
		if (isset($config['fieldConf']['config']['type'])) {
			$type = $config['fieldConf']['config']['type'];
		}

		/** @var PageRenderer $pageRenderer */
		$pageRenderer = $GLOBALS['SOBE']->doc->getPageRenderer();
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/FormEngine/Suggest');

		return $selector;
	}

	/**
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}


}

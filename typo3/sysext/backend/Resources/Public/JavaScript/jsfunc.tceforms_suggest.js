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

/**
 * Class for JS handling of suggest fields in FormEngine.
 *
 * TODO loading indicator, styling
 *
 * @author  Andreas Wolf <andreas.wolf@typo3.org>
 * @author  Benni Mack <benni@typo3.org>
 */
define('TYPO3/CMS/Backend/FormEngine/Suggest', ['jquery', 'jquery/jquery.autocomplete'], function($) {
	var defaultSettings = {
		minimumCharacters: 2
	};

	var PATH_typo3 = top.TS.PATH_typo3 || window.opener.top.TS.PATH_typo3;

	var suggestElementClass = 'typo3-TCEforms-suggest-search';
	/**
	 * Constructor for the suggest elements. Invoke with a jQuery'fied field <div> as the parameter and it will
	 * setup the suggest wizard for the field.
	 *
	 * @param $element
	 * @param string newRecordRow JSON encoded new content element. Only set when new record is inside flexform
	 * @constructor
	 */
	var FormEngineSuggest = function($element) {
		this.$element = $element;
		console.debug("create new suggest for ", this.$element);

		var ajaxEndpointUrl = TYPO3.settings.ajaxUrls['t3lib_TCEforms_suggest::searchRecord'];

		// TODO test this with nested records (IRRE, Flexform)
		this.$recordElement = this.$element.parents('.typo3-TCEforms').first();
		this.$fieldContainer = this.$element.parents('.t3js-formengine-field-item').first();
		console.debug('Parent record ', this.$recordElement);
		console.debug('Field wrapper ', this.$element.parents('.t3-form-field'));

		this.fieldType = this.$fieldContainer.data('type');
		this.elementName = this.$fieldContainer.data('element');
		this.field = this.$fieldContainer.data('field');
		this.table = this.$recordElement.data('table');
		this.uid = this.$recordElement.data('id');
		this.newRecord = this.$recordElement.data('newRecord');

		var autocompleteOptions = {
			serviceUrl: ajaxEndpointUrl,
			paramName: 'value',
			formatResult: this.formatResultValue,
			onSelect: $.proxy(this.addElementToList, this),
			deferRequestBy: 200,
			triggerSelectOnValidInput: false,
			containerClass: 'typo3-TCEforms-suggest-choices',
			params: {
				format: 'json',
				table: this.table,
				uid: this.uid,
				field: this.field // TODO Add newRecordRow
			}
		};

		this.$suggestElement = this.$element.find('.' + suggestElementClass);
		console.debug("Suggest element: ", this.$suggestElement);
		this.$suggestElement.autocomplete(autocompleteOptions);

	};

	FormEngineSuggest.prototype = {
		formatResultValue: function(suggestion, currentValue) {
			return suggestion.data.label;
		},

		addElementToList: function(suggestion) {
			console.debug('selected element', suggestion);
			var selectedRecordTable = suggestion.data.table, selectedRecordUid = suggestion.data.uid;
			var label = suggestion.data.label;

			var uidStringToInsert = (this.fieldType == 'select') ? selectedRecordUid : (selectedRecordTable + '_' + selectedRecordUid);

			console.debug('element name', this.elementName);
			setFormValueFromBrowseWin(this.elementName, uidStringToInsert, label, '');
			TBE_EDITOR.fieldChanged(this.table, this.uid, this.field, this.elementName);

			this.$suggestElement.val('');
			console.debug('element', this.$suggestElement);
			console.debug('element value', this.$element.val());
		}
	};

	$(function() {
		console.debug('Initialize TCEforms suggest.');
		$('.typo3-TCEforms-suggest').each(function() {
			new FormEngineSuggest($(this));
		});
	});

	return FormEngineSuggest;
});

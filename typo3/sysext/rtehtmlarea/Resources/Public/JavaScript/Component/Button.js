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

define('TYPO3/CMS/Rtehtmlarea/Component/Button', ['TYPO3/CMS/Rtehtmlarea/HtmlArea'], function(HTMLArea) {

/*
 * Ext.ux.HTMLAreaButton extends Ext.Button
 */
Ext.ux.HTMLAreaButton = Ext.extend(Ext.Button, {
	/*
	 * Component initialization
	 */
	initComponent: function () {
		Ext.ux.HTMLAreaButton.superclass.initComponent.call(this);
		this.addEvents(
			/*
			 * @event HTMLAreaEventHotkey
			 * Fires when the button hotkey is pressed
			 */
			'HTMLAreaEventHotkey',
			/*
			 * @event HTMLAreaEventContextMenu
			 * Fires when the button is triggered from the context menu
			 */
			'HTMLAreaEventContextMenu'
		);
		this.addListener({
			afterrender: {
				fn: this.initEventListeners,
				single: true
			}
		});
	},
	/*
	 * Initialize listeners
	 */
	initEventListeners: function () {
		this.addListener({
			HTMLAreaEventHotkey: {
				fn: this.onHotKey
			},
			HTMLAreaEventContextMenu: {
				fn: this.onButtonClick
			}
		});
		this.setHandler(this.onButtonClick, this);
			// Monitor toolbar updates in order to refresh the state of the button
		this.mon(this.getToolbar(), 'HTMLAreaEventToolbarUpdate', this.onUpdateToolbar, this);
	},
	/*
	 * Get a reference to the editor
	 */
	getEditor: function() {
		return RTEarea[this.ownerCt.editorId].editor;
	},
	/*
	 * Get a reference to the toolbar
	 */
	getToolbar: function() {
		return this.ownerCt;
	},
	/*
	 * Add properties and function to set button active or not depending on current selection
	 */
	inactive: true,
	activeClass: 'buttonActive',
	setInactive: function (inactive) {
		this.inactive = inactive;
		return inactive ? this.removeClass(this.activeClass) : this.addClass(this.activeClass);
	},
	/*
	 * Determine if the button should be enabled based on the current selection and context configuration property
	 */
	isInContext: function (mode, selectionEmpty, ancestors) {
		var editor = this.getEditor();
		var inContext = true;
		if (mode === 'wysiwyg' && this.context) {
			var attributes = [],
				contexts = [];
			if (/(.*)\[(.*?)\]/.test(this.context)) {
				contexts = RegExp.$1.split(',');
				attributes = RegExp.$2.split(',');
			} else {
				contexts = this.context.split(',');
			}
			contexts = new RegExp( '^(' + contexts.join('|') + ')$', 'i');
			var matchAny = contexts.test('*');
			Ext.each(ancestors, function (ancestor) {
				inContext = matchAny || contexts.test(ancestor.nodeName);
				if (inContext) {
					Ext.each(attributes, function (attribute) {
						inContext = eval("ancestor." + attribute);
						return inContext;
					});
				}
				return !inContext;
			});
		}
		return inContext && (!this.selection || !selectionEmpty);
	},
	/*
	 * Handler invoked when the button is clicked
	 */
	onButtonClick: function (button, event, key) {
		if (!this.disabled) {
			if (!this.plugins[this.action](this.getEditor(), key || this.itemId) && event) {
				event.stopEvent();
			}
			if (Ext.isOpera) {
				this.getEditor().focus();
			}
			if (this.dialog) {
				this.setDisabled(true);
			} else {
				this.getToolbar().update();
			}
		}
		return false;
	},
	/*
	 * Handler invoked when the hotkey configured for this button is pressed
	 */
	onHotKey: function (key, event) {
		return this.onButtonClick(this, event, key);
	},
	/*
	 * Handler invoked when the toolbar is updated
	 */
	onUpdateToolbar: function (mode, selectionEmpty, ancestors, endPointsInSameBlock) {
		this.setDisabled(mode === 'textmode' && !this.textMode);
		if (!this.disabled) {
			if (!this.noAutoUpdate) {
				this.setDisabled(!this.isInContext(mode, selectionEmpty, ancestors));
			}
			this.plugins['onUpdateToolbar'](this, mode, selectionEmpty, ancestors, endPointsInSameBlock);
		}
	}
});

Ext.reg('htmlareabutton', Ext.ux.HTMLAreaButton);

});
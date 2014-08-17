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
/*
 * Main script of TYPO3 htmlArea RTE
 */
	// Avoid re-initialization on AJax call when HTMLArea object was already initialized
define('TYPO3/CMS/Rtehtmlarea/HtmlArea', [
	'TYPO3/CMS/Rtehtmlarea/Utility/TYPO3'
], function(TYPO3Utility) {

	// Establish HTMLArea name space
Ext.namespace('HTMLArea.CSS', 'HTMLArea.util.TYPO3', 'HTMLArea.util.Tips', 'HTMLArea.util.Color', 'Ext.ux.form', 'Ext.ux.menu', 'Ext.ux.Toolbar');
Ext.apply(HTMLArea, {
	util: {
		TYPO3: TYPO3Utility
	},
	/***************************************************
	 * COMPILED REGULAR EXPRESSIONS                    *
	 ***************************************************/
	RE_htmlTag		: /<.[^<>]*?>/g,
	RE_tagName		: /(<\/|<)\s*([^ \t\n>]+)/ig,
	RE_head			: /<head>((.|\n)*?)<\/head>/i,
	RE_body			: /<body>((.|\n)*?)<\/body>/i,
		// This expression is deprecated as of TYPO3 4.7
	Reg_body		: new RegExp('<\/?(body)[^>]*>', 'gi'),
	reservedClassNames	: /htmlarea/,
	RE_email		: /([0-9a-z]+([a-z0-9_-]*[0-9a-z])*){1}(\.[0-9a-z]+([a-z0-9_-]*[0-9a-z])*)*@([0-9a-z]+([a-z0-9_-]*[0-9a-z])*\.)+[a-z]{2,9}/i,
	RE_url			: /(([^:/?#]+):\/\/)?(([a-z0-9_]+:[a-z0-9_]+@)?[a-z0-9_-]{2,}(\.[a-z0-9_-]{2,})+\.[a-z]{2,5}(:[0-9]+)?(\/\S+)*\/?)/i,
	RE_numberOrPunctuation	: /[0-9.(),;:!¡?¿%#$'"_+=\\\/-]*/g,
	/***************************************************
	 * BROWSER IDENTIFICATION                          *
	 ***************************************************/
	isIEBeforeIE9: Ext.isIE6 || Ext.isIE7 || Ext.isIE8 || (Ext.isIE && typeof(document.documentMode) !== 'undefined' && document.documentMode < 9),
	/***************************************************
	 * LOCALIZATION                                    *
	 ***************************************************/
	localize: function (label, plural) {
		var i = plural || 0;
		var localized = HTMLArea.I18N.dialogs[label] || HTMLArea.I18N.tooltips[label] || HTMLArea.I18N.msg[label] || '';
		if (typeof localized === 'object' && typeof localized[i] !== 'undefined') {
			localized = localized[i]['target'];
		}
		return localized;
	},
	/***************************************************
	 * INITIALIZATION                                  *
	 ***************************************************/
	init: function () {
		if (!HTMLArea.isReady) {
				// Apply global configuration settings
			Ext.apply(HTMLArea, RTEarea[0]);
			Ext.applyIf(HTMLArea, {
				editorSkin	: HTMLArea.editorUrl + 'skins/default/',
				editorCSS	: HTMLArea.editorUrl + 'skins/default/htmlarea.css'
			});
			if (!Ext.isString(HTMLArea.editedContentCSS)) {
				HTMLArea.editedContentCSS = HTMLArea.editorSkin + 'htmlarea-edited-content.css';
			}
			HTMLArea.isReady = true;
			HTMLArea.appendToLog('', 'HTMLArea', 'init', 'Editor url set to: ' + HTMLArea.editorUrl, 'info');
			HTMLArea.appendToLog('', 'HTMLArea', 'init', 'Editor skin CSS set to: ' + HTMLArea.editorCSS, 'info');
			HTMLArea.appendToLog('', 'HTMLArea', 'init', 'Editor content skin CSS set to: ' + HTMLArea.editedContentCSS, 'info');
		}
	},
	/*
	 * Create an editor when HTMLArea is loaded and when Ext is ready
	 *
	 * @param	string		editorId: the id of the editor
	 *
	 * @return 	boolean		false if successful
	 */
	initEditor: function (editorId) {
		if (document.getElementById('pleasewait' + editorId)) {
			if (HTMLArea.checkSupportedBrowser()) {
				document.getElementById('pleasewait' + editorId).style.display = 'block';
				document.getElementById('editorWrap' + editorId).style.visibility = 'hidden';
				if (!HTMLArea.isReady) {
					HTMLArea.initEditor.defer(150, null, [editorId]);
				} else {
						// Create an editor for the textarea
					var editor = new HTMLArea.Editor(Ext.apply(new HTMLArea.Config(editorId), RTEarea[editorId]));
					editor.generate();
					return false;
				}
			} else {
				document.getElementById('pleasewait' + editorId).style.display = 'none';
				document.getElementById('editorWrap' + editorId).style.visibility = 'visible';
			}
		}
		return true;
	},
	/*
	 * Check if the client agent is supported
	 *
	 * @return	boolean		true if the client is supported
	 */
	checkSupportedBrowser: function () {
		return Ext.isGecko || Ext.isWebKit || Ext.isOpera || Ext.isIE;
	},
	/*
	 * Write message to JavaScript console
	 *
	 * @param	string		editorId: the id of the editor issuing the message
	 * @param	string		objectName: the name of the object issuing the message
	 * @param	string		functionName: the name of the function issuing the message
	 * @param	string		text: the text of the message
	 * @param	string		type: the type of message: 'log', 'info', 'warn' or 'error'
	 *
	 * @return	void
	 */
	appendToLog: function (editorId, objectName, functionName, text, type) {
		var str = 'RTE[' + editorId + '][' + objectName + '::' + functionName + ']: ' + text;
		if (typeof(type) === 'undefined') {
			var type = 'info';
		}
		if (typeof(console) !== 'undefined' && typeof(console) === 'object') {
			// If console is TYPO3.Backend.DebugConsole, write only error messages
			if (Ext.isFunction(console.addTab)) {
				if (type === 'error') {
					console[type](str);
				}
			// IE may not have any console
			} else if (typeof(console[type]) !== 'undefined') {
				console[type](str);
			}
		}
	}
});
/***************************************************
 *  EDITOR CONFIGURATION
 ***************************************************/
HTMLArea.Config = function (editorId) {
	this.editorId = editorId;
		// if the site is secure, create a secure iframe
	this.useHTTPS = false;
		// for Mozilla
	this.useCSS = false;
	this.enableMozillaExtension = true;
	this.disableEnterParagraphs = false;
	this.disableObjectResizing = false;
	this.removeTrailingBR = true;
		// style included in the iframe document
	this.editedContentStyle = HTMLArea.editedContentCSS;
		// content style
	this.pageStyle = "";
		// Maximum attempts at accessing the stylesheets
	this.styleSheetsMaximumAttempts = 20;
		// Remove tags (must be a regular expression)
	this.htmlRemoveTags = /none/i;
		// Remove tags and their contents (must be a regular expression)
	this.htmlRemoveTagsAndContents = /none/i;
		// Remove comments
	this.htmlRemoveComments = false;
		// Array of custom tags
	this.customTags = [];
		// BaseURL to be included in the iframe document
	this.baseURL = document.baseURI;
		// IE does not support document.baseURI
		// Since document.URL is incorrect when using realurl, get first base tag and extract href
	if (!this.baseURL) {
		var baseTags = document.getElementsByTagName ('base');
		if (baseTags.length > 0) {
			this.baseURL = baseTags[0].href;
		} else {
			this.baseURL = document.URL;
		}
	}
	if (this.baseURL && this.baseURL.match(/(.*\:\/\/.*\/)[^\/]*/)) {
		this.baseURL = RegExp.$1;
	}
		// URL-s
	this.popupURL = "popups/";
		// DocumentType
	this.documentType = '<!DOCTYPE html\r'
			+ '    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"\r'
			+ '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\r';
	this.blankDocument = '<html><head></head><body></body></html>';
		// Hold the configuration of buttons and hot keys registered by plugins
	this.buttonsConfig = {};
	this.hotKeyList = {};
		// Default configurations for toolbar items
	this.configDefaults = {
		all: {
			xtype: 'htmlareabutton',
			disabledClass: 'buttonDisabled',
			textMode: false,
			selection: false,
			dialog: false,
			hidden: false,
			hideMode: 'display'
		},
		htmlareabutton: {
			cls: 'button',
			overCls: 'buttonHover',
				// Erratic behaviour of click event in WebKit and IE browsers
			clickEvent: (Ext.isWebKit || Ext.isIE) ? 'mousedown' : 'click'
		},
		htmlareacombo: {
			cls: 'select',
			typeAhead: true,
			lastQuery: '',
			triggerAction: 'all',
			editable: !Ext.isIE,
			selectOnFocus: !Ext.isIE,
			validationEvent: false,
			validateOnBlur: false,
			submitValue: false,
			forceSelection: true,
			mode: 'local',
			storeRoot: 'options',
			storeFields: [ { name: 'text'}, { name: 'value'}],
			valueField: 'value',
			displayField: 'text',
			labelSeparator: '',
			hideLabel: true,
			tpl: '<tpl for="."><div ext:qtip="{value}" style="text-align:left;font-size:11px;" class="x-combo-list-item">{text}</div></tpl>'
		}
	};
};
HTMLArea.Config = Ext.extend(HTMLArea.Config, {
	/**
	 * Registers a button for inclusion in the toolbar, adding some standard configuration properties for the ExtJS widgets
	 *
	 * @param	object		buttonConfiguration: the configuration object of the button:
	 *					id		: unique id for the button
	 *					tooltip		: tooltip for the button
	 *					textMode	: enable in text mode
	 *					context		: disable if not inside one of listed elements
	 *					hidden		: hide in menu and show only in context menu
	 *					selection	: disable if there is no selection
	 *					hotkey		: hotkey character
	 *					dialog		: if true, the button opens a dialogue
	 *					dimensions	: the opening dimensions object of the dialogue window: { width: nn, height: mm }
	 *					and potentially other ExtJS config properties (will be forwarded)
	 *
	 * @return	boolean		true if the button was successfully registered
	 */
	registerButton: function (config) {
		config.itemId = config.id;
		if (Ext.type(this.buttonsConfig[config.id])) {
			HTMLArea.appendToLog('', 'HTMLArea.Config', 'registerButton', 'A toolbar item with the same Id: ' + config.id + ' already exists and will be overidden.', 'warn');
		}
			// Apply defaults
		config = Ext.applyIf(config, this.configDefaults['all']);
		config = Ext.applyIf(config, this.configDefaults[config.xtype]);
			// Set some additional properties
		switch (config.xtype) {
			case 'htmlareacombo':
				if (config.options) {
						// Create combo array store
					config.store = new Ext.data.ArrayStore({
						autoDestroy:  true,
						fields: config.storeFields,
						data: config.options
					});
				} else if (config.storeUrl) {
						// Create combo json store
					config.store = new Ext.data.JsonStore({
						autoDestroy:  true,
						autoLoad: true,
						root: config.storeRoot,
						fields: config.storeFields,
						url: config.storeUrl
					});
				}
				config.hideLabel = Ext.isEmpty(config.fieldLabel) || Ext.isIE6;
				config.helpTitle = config.tooltip;
				break;
			default:
				if (!config.iconCls) {
					config.iconCls = config.id;
				}
				break;
		}
		config.cmd = config.id;
		config.tooltip = { title: config.tooltip };
		this.buttonsConfig[config.id] = config;
		return true;
	},
	/*
	 * Register a hotkey with the editor configuration.
	 */
	registerHotKey: function (hotKeyConfiguration) {
		if (Ext.isDefined(this.hotKeyList[hotKeyConfiguration.id])) {
			HTMLArea.appendToLog('', 'HTMLArea.Config', 'registerHotKey', 'A hotkey with the same key ' + hotKeyConfiguration.id + ' already exists and will be overidden.', 'warn');
		}
		if (Ext.isDefined(hotKeyConfiguration.cmd) && !Ext.isEmpty(hotKeyConfiguration.cmd) && Ext.isDefined(this.buttonsConfig[hotKeyConfiguration.cmd])) {
			this.hotKeyList[hotKeyConfiguration.id] = hotKeyConfiguration;
			return true;
		} else {
			HTMLArea.appendToLog('', 'HTMLArea.Config', 'registerHotKey', 'A hotkey with key ' + hotKeyConfiguration.id + ' could not be registered because toolbar item with id ' + hotKeyConfiguration.cmd + ' was not registered.', 'warn');
			return false;
		}
	},
	/*
	 * Get the configured document type for dialogue windows
	 */
	getDocumentType: function () {
		return this.documentType;
	}
});

/***************************************************
 *  UTILITY FUNCTIONS
 ***************************************************/
Ext.apply(HTMLArea.util, {
	/*
	 * Perform HTML encoding of some given string
	 * Borrowed in part from Xinha (is not htmlArea) - http://xinha.gogo.co.nz/
	 */
	htmlDecode: function (str) {
		str = str.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		str = str.replace(/&nbsp;/g, '\xA0'); // Decimal 160, non-breaking-space
		str = str.replace(/&quot;/g, '\x22');
		str = str.replace(/&#39;/g, "'");
		str = str.replace(/&amp;/g, '&');
		return str;
	},
	htmlEncode: function (str) {
		if (typeof(str) != 'string') {
			str = str.toString();
		}
		str = str.replace(/&/g, '&amp;');
		str = str.replace(/</g, '&lt;').replace(/>/g, '&gt;');
		str = str.replace(/\xA0/g, '&nbsp;'); // Decimal 160, non-breaking-space
		str = str.replace(/\x22/g, '&quot;'); // \x22 means '"'
		return str;
	}
});
/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.htmlDecode = HTMLArea.util.htmlDecode;
/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.htmlEncode = HTMLArea.util.htmlEncode;

/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.getInnerText = HTMLArea.DOM.getInnerText;
/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.removeFromParent = HTMLArea.DOM.removeFromParent;

/*
 * This function verifies if the element has any allowed attributes
 *
 * @param	object	element: the DOM element
 * @param	array	allowedAttributes: array of allowed attribute names
 *
 * @return	boolean	true if the element has one of the allowed attributes
 *
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.hasAllowedAttributes = HTMLArea.DOM.hasAllowedAttributes;
/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.isBlockElement = HTMLArea.DOM.isBlockElement;
/*
 ***********************************************
 * THIS FUNCTION IS DEPRECATED AS OF TYPO3 4.7 *
 ***********************************************
 */
HTMLArea.needsClosingTag = HTMLArea.DOM.needsClosingTag;


/***************************************************
 *  COLOR WIDGETS AND UTILITIES
 ***************************************************/
HTMLArea.util.Color = function () {
	return {
		/*
		 * Returns a rgb-style color from a number
		 */
		colorToRgb: function(v) {
			if (typeof(v) != 'number') {
				return v;
			}
			var r = v & 0xFF;
			var g = (v >> 8) & 0xFF;
			var b = (v >> 16) & 0xFF;
			return 'rgb(' + r + ',' + g + ',' + b + ')';
		},
		/*
		 * Returns hexadecimal color representation from a number or a rgb-style color.
		 */
		colorToHex: function(v) {
			if (typeof(v) === 'undefined' || v == null) {
				return '';
			}
			function hex(d) {
				return (d < 16) ? ('0' + d.toString(16)) : d.toString(16);
			};
			if (typeof(v) == 'number') {
				var b = v & 0xFF;
				var g = (v >> 8) & 0xFF;
				var r = (v >> 16) & 0xFF;
				return '#' + hex(r) + hex(g) + hex(b);
			}
			if (v.substr(0, 3) === 'rgb') {
				var re = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/;
				if (v.match(re)) {
					var r = parseInt(RegExp.$1);
					var g = parseInt(RegExp.$2);
					var b = parseInt(RegExp.$3);
					return ('#' + hex(r) + hex(g) + hex(b)).toUpperCase();
				}
				return '';
			}
			if (v.substr(0, 1) === '#') {
				return v;
			}
			return '';
		},
		/*
		 * Select interceptor to ensure that the color exists in the palette before trying to select
		 */
		checkIfColorInPalette: function (color) {
				// Do not continue if the new color is not in the palette
			if (this.el && !this.el.child('a.color-' + color)) {
					// Remove any previous selection
				this.deSelect();
				return false;
			}
		}
	}
}();
/*
 * Intercept Ext.ColorPalette.prototype.select
 */
Ext.ColorPalette.prototype.select = Ext.ColorPalette.prototype.select.createInterceptor(HTMLArea.util.Color.checkIfColorInPalette);
/*
 * Add deSelect method to Ext.ColorPalette
 */
Ext.override(Ext.ColorPalette, {
	deSelect: function () {
		if (this.el && this.value){
			this.el.child('a.color-' + this.value).removeClass('x-color-palette-sel');
			this.value = null;
		}
	}
});
Ext.ux.menu.HTMLAreaColorMenu = Ext.extend(Ext.menu.Menu, {
	enableScrolling: false,
	hideOnClick: true,
	cls: 'x-color-menu',
	colorPaletteValue: '',
	customColorsValue: '',
	plain: true,
	showSeparator: false,
	initComponent: function () {
		var paletteItems = [];
		var width = 'auto';
		if (this.colorsConfiguration) {
			paletteItems.push({
				xtype: 'container',
				layout: 'anchor',
				width: 160,
				style: { float: 'right' },
				items: {
					xtype: 'colorpalette',
					itemId: 'custom-colors',
					cls: 'htmlarea-custom-colors',
					colors: this.colorsConfiguration,
					value: this.value,
					allowReselect: true,
					tpl: new Ext.XTemplate(
						'<tpl for="."><a href="#" class="color-{1}" hidefocus="on"><em><span style="background:#{1}" unselectable="on">&#160;</span></em><span unselectable="on">{0}</span></a></tpl>'
					)
				}
			});
		}
		if (this.colors.length) {
			paletteItems.push({
				xtype: 'container',
				layout: 'anchor',
				items: {
					xtype: 'colorpalette',
					itemId: 'color-palette',
					cls: 'color-palette',
					colors: this.colors,
					value: this.value,
					allowReselect: true
				}
			});
		}
		if (this.colorsConfiguration && this.colors.length) {
			width = 350;
		}
		Ext.apply(this, {
			layout: 'menu',
			width: width,
			items: paletteItems
		});
		Ext.ux.menu.HTMLAreaColorMenu.superclass.initComponent.call(this);
		this.standardPalette = this.find('itemId', 'color-palette')[0];
		this.customPalette = this.find('itemId', 'custom-colors')[0];
		if (this.standardPalette) {
			this.standardPalette.purgeListeners();
			this.relayEvents(this.standardPalette, ['select']);
		}
		if (this.customPalette) {
			this.customPalette.purgeListeners();
			this.relayEvents(this.customPalette, ['select']);
		}
		this.on('select', this.menuHide, this);
		if (this.handler){
			this.on('select', this.handler, this.scope || this);
		}
	},
	menuHide: function() {
		if (this.hideOnClick){
			this.hide(true);
		}
	}
});
Ext.reg('htmlareacolormenu', Ext.ux.menu.HTMLAreaColorMenu);
/*
 * Color palette trigger field
 * Based on http://www.extjs.com/forum/showthread.php?t=89312
 */
Ext.ux.form.ColorPaletteField = Ext.extend(Ext.form.TriggerField, {
	triggerClass: 'x-form-color-trigger',
	defaultColors: [
		'000000', '222222', '444444', '666666', '999999', 'BBBBBB', 'DDDDDD', 'FFFFFF',
		'660000', '663300', '996633', '003300', '003399', '000066', '330066', '660066',
		'990000', '993300', 'CC9900', '006600', '0033FF', '000099', '660099', '990066',
		'CC0000', 'CC3300', 'FFCC00', '009900', '0066FF', '0000CC', '663399', 'CC0099',
		'FF0000', 'FF3300', 'FFFF00', '00CC00', '0099FF', '0000FF', '9900CC', 'FF0099',
		'CC3333', 'FF6600', 'FFFF33', '00FF00', '00CCFF', '3366FF', '9933FF', 'FF00FF',
		'FF6666', 'FF6633', 'FFFF66', '66FF66', '00FFFF', '3399FF', '9966FF', 'FF66FF',
		'FF9999', 'FF9966', 'FFFF99', '99FF99', '99FFFF', '66CCFF', '9999FF', 'FF99FF',
		'FFCCCC', 'FFCC99', 'FFFFCC', 'CCFFCC', 'CCFFFF', '99CCFF', 'CCCCFF', 'FFCCFF'
	],
		// Whether or not the field background, text, or triggerbackgroud are set to the selected color
	colorizeFieldBackgroud: true,
	colorizeFieldText: true,
	colorizeTrigger: false,
	editable: true,
	initComponent: function () {
		Ext.ux.form.ColorPaletteField.superclass.initComponent.call(this);
		if (!this.colors) {
			this.colors = this.defaultColors;
		}
		this.addEvents(
			'select'
		);
	},
		// private
	validateBlur: function () {
		return !this.menu || !this.menu.isVisible();
	},
	setValue: function (color) {
		if (color) {
			if (this.colorizeFieldBackgroud) {
				this.el.applyStyles('background: #' + color  + ';');
			}
			if (this.colorizeFieldText) {
				this.el.applyStyles('color: #' + this.rgbToHex(this.invert(this.hexToRgb(color)))  + ';');
			}
			if (this.colorizeTrigger) {
				this.trigger.applyStyles('background-color: #' + color  + ';');
			}
		}
		return Ext.ux.form.ColorPaletteField.superclass.setValue.call(this, color);
	},
		// private
	onDestroy: function () {
		Ext.destroy(this.menu);
		Ext.ux.form.ColorPaletteField.superclass.onDestroy.call(this);
	},
		// private
	onTriggerClick: function () {
		if (this.disabled) {
			return;
		}
		if (this.menu == null) {
			this.menu = new Ext.ux.menu.HTMLAreaColorMenu({
				cls: 'htmlarea-color-menu',
				hideOnClick: false,
				colors: this.colors,
				colorsConfiguration: this.colorsConfiguration,
				value: this.getValue()
			});
		}
		this.onFocus();
		this.menu.show(this.el, "tl-bl?");
		this.menuEvents('on');
	},
		//private
	menuEvents: function (method) {
		this.menu[method]('select', this.onSelect, this);
		this.menu[method]('hide', this.onMenuHide, this);
		this.menu[method]('show', this.onFocus, this);
	},
	onSelect: function (m, d) {
		this.setValue(d);
		this.fireEvent('select', this, d);
		this.menu.hide();
	},
	onMenuHide: function () {
		this.focus(false, 60);
		this.menuEvents('un');
	},
	invert: function ( r, g, b ) {
		if( r instanceof Array ) { return this.invert.call( this, r[0], r[1], r[2] ); }
		return [255-r,255-g,255-b];
	},
	hexToRgb: function ( hex ) {
		return [ this.hexToDec( hex.substr(0, 2) ), this.hexToDec( hex.substr(2, 2) ), this.hexToDec( hex.substr(4, 2) ) ];
	},
	hexToDec: function( hex ) {
		var s = hex.split('');
		return ( ( this.getHCharPos( s[0] ) * 16 ) + this.getHCharPos( s[1] ) );
	},
	getHCharPos: function( c ) {
		var HCHARS = '0123456789ABCDEF';
		return HCHARS.indexOf( c.toUpperCase() );
	},
	rgbToHex: function( r, g, b ) {
		if( r instanceof Array ) { return this.rgbToHex.call( this, r[0], r[1], r[2] ); }
		return this.decToHex( r ) + this.decToHex( g ) + this.decToHex( b );
	},
	decToHex: function( n ) {
		var HCHARS = '0123456789ABCDEF';
		n = parseInt(n, 10);
		n = ( !isNaN( n )) ? n : 0;
		n = (n > 255 || n < 0) ? 0 : n;
		return HCHARS.charAt( ( n - n % 16 ) / 16 ) + HCHARS.charAt( n % 16 );
	}
});
Ext.reg('colorpalettefield', Ext.ux.form.ColorPaletteField);
/***************************************************
 * TYPO3-SPECIFIC FUNCTIONS
 ***************************************************/
/*
 * Extending the TYPO3 Lorem Ipsum extension
 */
var lorem_ipsum = function (element, text) {
	if (/^textarea$/i.test(element.nodeName) && element.id && element.id.substr(0,7) === 'RTEarea') {
		var editor = RTEarea[element.id.substr(7, element.id.length)]['editor'];
		editor.getSelection().insertHtml(text);
		editor.updateToolbar();
	}
};


	return HTMLArea;

});

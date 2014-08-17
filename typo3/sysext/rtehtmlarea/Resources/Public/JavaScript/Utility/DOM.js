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

define('TYPO3/CMS/Rtehtmlarea/Utility/DOM', ['TYPO3/CMS/Rtehtmlarea/HtmlArea'], function(HTMLArea) {

/*****************************************************************
 * HTMLArea.DOM: Utility functions for dealing with the DOM tree *
 *****************************************************************/
HTMLArea.DOM = function () {
	return {
		/***************************************************
		*  DOM-RELATED CONSTANTS
		***************************************************/
			// DOM node types
		ELEMENT_NODE: 1,
		ATTRIBUTE_NODE: 2,
		TEXT_NODE: 3,
		CDATA_SECTION_NODE: 4,
		ENTITY_REFERENCE_NODE: 5,
		ENTITY_NODE: 6,
		PROCESSING_INSTRUCTION_NODE: 7,
		COMMENT_NODE: 8,
		DOCUMENT_NODE: 9,
		DOCUMENT_TYPE_NODE: 10,
		DOCUMENT_FRAGMENT_NODE: 11,
		NOTATION_NODE: 12,
		/***************************************************
		*  DOM-RELATED REGULAR EXPRESSIONS
		***************************************************/
		RE_blockTags: /^(address|article|aside|body|blockquote|caption|dd|div|dl|dt|fieldset|footer|form|header|hr|h1|h2|h3|h4|h5|h6|iframe|li|ol|p|pre|nav|noscript|section|table|tbody|td|tfoot|th|thead|tr|ul)$/i,
		RE_noClosingTag: /^(area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)$/i,
		RE_bodyTag: new RegExp('<\/?(body)[^>]*>', 'gi'),
		/***************************************************
		*  STATIC METHODS ON DOM NODE
		***************************************************/
		/*
		 * Determine whether an element node is a block element
		 *
		 * @param	object		element: the element node
		 *
		 * @return	boolean		true, if the element node is a block element
		 */
		isBlockElement: function (element) {
			return element && element.nodeType === HTMLArea.DOM.ELEMENT_NODE && HTMLArea.DOM.RE_blockTags.test(element.nodeName);
		},
		/*
		 * Determine whether an element node needs a closing tag
		 *
		 * @param	object		element: the element node
		 *
		 * @return	boolean		true, if the element node needs a closing tag
		 */
		needsClosingTag: function (element) {
			return element && element.nodeType === HTMLArea.DOM.ELEMENT_NODE && !HTMLArea.DOM.RE_noClosingTag.test(element.nodeName);
		},
		/*
		 * Gets the class names assigned to a node, reserved classes removed
		 *
		 * @param	object		node: the node
		 * @return	array		array of class names on the node, reserved classes removed
		 */
		getClassNames: function (node) {
			var classNames = [];
			if (node) {
				if (node.className && /\S/.test(node.className)) {
					classNames = node.className.trim().split(' ');
				}
				if (HTMLArea.reservedClassNames.test(node.className)) {
					var cleanClassNames = [];
					var j = -1;
					for (var i = 0; i < classNames.length; ++i) {
						if (!HTMLArea.reservedClassNames.test(classNames[i])) {
							cleanClassNames[++j] = classNames[i];
						}
					}
					classNames = cleanClassNames;
				}
			}
			return classNames;
		},
		/*
		 * Check if a class name is in the class attribute of a node
		 *
		 * @param	object		node: the node
		 * @param	string		className: the class name to look for
		 * @param	boolean		substring: if true, look for a class name starting with the given string
		 * @return	boolean		true if the class name was found, false otherwise
		 */
		hasClass: function (node, className, substring) {
			var found = false;
			if (node && node.className) {
				var classes = node.className.trim().split(' ');
				for (var i = classes.length; --i >= 0;) {
					found = ((classes[i] == className) || (substring && classes[i].indexOf(className) == 0));
					if (found) {
						break;
					}
				}
			}
			return found;
		},
		/*
		 * Add a class name to the class attribute of a node
		 *
		 * @param	object		node: the node
		 * @param	string		className: the name of the class to be added
		 * @return	void
		 */
		addClass: function (node, className) {
			if (node) {
				HTMLArea.DOM.removeClass(node, className);
					// Remove classes configured to be incompatible with the class to be added
				if (node.className && HTMLArea.classesXOR && HTMLArea.classesXOR[className] && Ext.isFunction(HTMLArea.classesXOR[className].test)) {
					var classNames = node.className.trim().split(' ');
					for (var i = classNames.length; --i >= 0;) {
						if (HTMLArea.classesXOR[className].test(classNames[i])) {
							HTMLArea.DOM.removeClass(node, classNames[i]);
						}
					}
				}
				if (node.className) {
					node.className += ' ' + className;
				} else {
					node.className = className;
				}
			}
		},
		/*
		 * Remove a class name from the class attribute of a node
		 *
		 * @param	object		node: the node
		 * @param	string		className: the class name to removed
		 * @param	boolean		substring: if true, remove the class names starting with the given string
		 * @return	void
		 */
		removeClass: function (node, className, substring) {
			if (node && node.className) {
				var classes = node.className.trim().split(' ');
				var newClasses = [];
				for (var i = classes.length; --i >= 0;) {
					if ((!substring && classes[i] != className) || (substring && classes[i].indexOf(className) != 0)) {
						newClasses[newClasses.length] = classes[i];
					}
				}
				if (newClasses.length) {
					node.className = newClasses.join(' ');
				} else {
					if (!Ext.isOpera) {
						node.removeAttribute('class');
						if (HTMLArea.isIEBeforeIE9) {
							node.removeAttribute('className');
						}
					} else {
						node.className = '';
					}
				}
			}
		},
		/*
		 * Get the innerText of a given node
		 *
		 * @param	object		node: the node
		 *
		 * @return	string		the text inside the node
		 */
		getInnerText: function (node) {
			return HTMLArea.isIEBeforeIE9 ? node.innerText : node.textContent;;
		},
		/*
		 * Get the block ancestors of a node within a given block
		 *
		 * @param	object		node: the given node
		 * @param	object		withinBlock: the containing node
		 *
		 * @return	array		array of block ancestors
		 */
		getBlockAncestors: function (node, withinBlock) {
			var ancestors = [];
			var ancestor = node;
			while (ancestor && (ancestor.nodeType === HTMLArea.DOM.ELEMENT_NODE) && !/^(body)$/i.test(ancestor.nodeName) && ancestor != withinBlock) {
				if (HTMLArea.DOM.isBlockElement(ancestor)) {
					ancestors.unshift(ancestor);
				}
				ancestor = ancestor.parentNode;
			}
			ancestors.unshift(ancestor);
			return ancestors;
		},
		/*
		 * Get the deepest element ancestor of a given node that is of one of the specified types
		 *
		 * @param	object		node: the given node
		 * @param	array		types: an array of nodeNames
		 *
		 * @return	object		the found ancestor of one of the given types or null
		 */
		getFirstAncestorOfType: function (node, types) {
			var ancestor = null,
				parent = node;
			if (!Ext.isEmpty(types)) {
				if (Ext.isString(types)) {
					var types = [types];
				}
				types = new RegExp( '^(' + types.join('|') + ')$', 'i');
				while (parent && parent.nodeType === HTMLArea.DOM.ELEMENT_NODE && !/^(body)$/i.test(parent.nodeName)) {
					if (types.test(parent.nodeName)) {
						ancestor = parent;
						break;
					}
					parent = parent.parentNode;
				}
			}
			return ancestor;
		},
		/*
		 * Get the position of the node within the children of its parent
		 * Adapted from FCKeditor
		 *
		 * @param	object		node: the DOM node
		 * @param	boolean		normalized: if true, a normalized position is calculated
		 *
		 * @return	integer		the position of the node
		 */
		getPositionWithinParent: function (node, normalized) {
			var current = node,
				position = 0;
			while (current = current.previousSibling) {
				// For a normalized position, do not count any empty text node or any text node following another one
				if (
					normalized
					&& current.nodeType == HTMLArea.DOM.TEXT_NODE
					&& (!current.nodeValue.length || (current.previousSibling && current.previousSibling.nodeType == HTMLArea.DOM.TEXT_NODE))
				) {
					continue;
				}
				position++;
			}
			return position;
		},
		/*
		 * Determine whether a given node has any allowed attributes
		 *
		 * @param	object		node: the DOM node
		 * @param	array		allowedAttributes: array of allowed attribute names
		 *
		 * @return	boolean		true if the node has one of the allowed attributes
		 */
		 hasAllowedAttributes: function (node, allowedAttributes) {
			var value,
				hasAllowedAttributes = false;
			if (Ext.isString(allowedAttributes)) {
				allowedAttributes = [allowedAttributes];
			}
			allowedAttributes = allowedAttributes || [];
			for (var i = allowedAttributes.length; --i >= 0;) {
				value = node.getAttribute(allowedAttributes[i]);
				if (value) {
					if (allowedAttributes[i] === 'style') {
						if (node.style.cssText) {
							hasAllowedAttributes = true;
							break;
						}
					} else {
						hasAllowedAttributes = true;
						break;
					}
				}
			}
			return hasAllowedAttributes;
		},
		/*
		 * Remove the given node from its parent
		 *
		 * @param	object		node: the DOM node
		 *
		 * @return	void
		 */
		removeFromParent: function (node) {
			var parent = node.parentNode;
			if (parent) {
				parent.removeChild(node);
			}
		},
		/*
		 * Change the nodeName of an element node
		 *
		 * @param	object		node: the node to convert (must belong to a document)
		 * @param	string		nodeName: the nodeName of the converted node
		 *
		 * @retrun	object		the converted node or the input node
		 */
		convertNode: function (node, nodeName) {
			var convertedNode = node,
				ownerDocument = node.ownerDocument;
			if (ownerDocument && node.nodeType === HTMLArea.DOM.ELEMENT_NODE) {
				var convertedNode = ownerDocument.createElement(nodeName),
					parent = node.parentNode;
				while (node.firstChild) {
					convertedNode.appendChild(node.firstChild);
				}
				parent.insertBefore(convertedNode, node);
				parent.removeChild(node);
			}
			return convertedNode;
		},
		/*
		 * Determine whether a given range intersects a given node
		 *
		 * @param	object		range: the range
		 * @param	object		node: the DOM node (must belong to a document)
		 *
		 * @return	boolean		true if the range intersects the node
		 */
		rangeIntersectsNode: function (range, node) {
			var rangeIntersectsNode = false,
				ownerDocument = node.ownerDocument;
			if (ownerDocument) {
				if (HTMLArea.isIEBeforeIE9) {
					var nodeRange = ownerDocument.body.createTextRange();
					nodeRange.moveToElementText(node);
					rangeIntersectsNode = (range.compareEndPoints('EndToStart', nodeRange) == -1 && range.compareEndPoints('StartToEnd', nodeRange) == 1) ||
						(range.compareEndPoints('EndToStart', nodeRange) == 1 && range.compareEndPoints('StartToEnd', nodeRange) == -1);
				} else {
					var nodeRange = ownerDocument.createRange();
					try {
						nodeRange.selectNode(node);
					} catch (e) {
						if (Ext.isWebKit) {
							nodeRange.setStart(node, 0);
							if (node.nodeType === HTMLArea.DOM.TEXT_NODE || node.nodeType === HTMLArea.DOM.COMMENT_NODE || node.nodeType === HTMLArea.DOM.CDATA_SECTION_NODE) {
								nodeRange.setEnd(node, node.textContent.length);
							} else {
								nodeRange.setEnd(node, node.childNodes.length);
							}
						} else {
							nodeRange.selectNodeContents(node);
						}
					}
						// Note: sometimes WebKit inverts the end points
					rangeIntersectsNode = (range.compareBoundaryPoints(range.END_TO_START, nodeRange) == -1 && range.compareBoundaryPoints(range.START_TO_END, nodeRange) == 1) ||
						(range.compareBoundaryPoints(range.END_TO_START, nodeRange) == 1 && range.compareBoundaryPoints(range.START_TO_END, nodeRange) == -1);
				}
			}
			return rangeIntersectsNode;
		},
		/*
		 * Make url's absolute in the DOM tree under the root node
		 *
		 * @param	object		root: the root node
		 * @param	string		baseUrl: base url to use
		 * @param	string		walker: a HLMLArea.DOM.Walker object
		 * @return	void
		 */
		makeUrlsAbsolute: function (node, baseUrl, walker) {
			walker.walk(node, true, 'HTMLArea.DOM.makeImageSourceAbsolute(node, args[0]) || HTMLArea.DOM.makeLinkHrefAbsolute(node, args[0])', 'Ext.emptyFn', [baseUrl]);
		},
		/*
		 * Make the src attribute of an image node absolute
		 *
		 * @param	object		node: the image node
		 * @param	string		baseUrl: base url to use
		 * @return	void
		 */
		makeImageSourceAbsolute: function (node, baseUrl) {
			if (/^img$/i.test(node.nodeName)) {
				var src = node.getAttribute('src');
				if (src) {
					node.setAttribute('src', HTMLArea.DOM.addBaseUrl(src, baseUrl));
				}
				return true;
			}
			return false;
		},
		/*
		 * Make the href attribute of an a node absolute
		 *
		 * @param	object		node: the image node
		 * @param	string		baseUrl: base url to use
		 * @return	void
		 */
		makeLinkHrefAbsolute: function (node, baseUrl) {
			if (/^a$/i.test(node.nodeName)) {
				var href = node.getAttribute('href');
				if (href) {
					node.setAttribute('href', HTMLArea.DOM.addBaseUrl(href, baseUrl));
				}
				return true;
			}
			return false;
		},
		/*
		 * Add base url
		 *
		 * @param	string		url: value of a href or src attribute
		 * @param	string		baseUrl: base url to add
		 * @return	string		absolute url
		 */
		addBaseUrl: function (url, baseUrl) {
			var absoluteUrl = url;
				// If the url has no scheme...
			if (!/^[a-z0-9_]{2,}\:/i.test(absoluteUrl)) {
				var base = baseUrl;
				while (absoluteUrl.match(/^\.\.\/(.*)/)) {
						// Remove leading ../ from url
					absoluteUrl = RegExp.$1;
					base.match(/(.*\:\/\/.*\/)[^\/]+\/$/);
						// Remove lowest directory level from base
					base = RegExp.$1;
					absoluteUrl = base + absoluteUrl;
				}
					// If the url is still not absolute...
				if (!/^.*\:\/\//.test(absoluteUrl)) {
					absoluteUrl = baseUrl + absoluteUrl;
				}
			}
			return absoluteUrl;
		}
	};
}();
/***************************************************
 *  HTMLArea.DOM.Walker: DOM tree walk
 ***************************************************/
HTMLArea.DOM.Walker = function (config) {
	var configDefaults = {
		keepComments: false,
		keepCDATASections: false,
		removeTags: /none/i,
		removeTagsAndContents: /none/i,
		keepTags: /.*/i,
		removeAttributes: /none/i,
		removeTrailingBR: true,
		baseUrl: ''
	};
	Ext.apply(this, config, configDefaults);
};
HTMLArea.DOM.Walker = Ext.extend(HTMLArea.DOM.Walker, {
	/*
	 * Walk the DOM tree
	 *
	 * @param	object		node: the root node of the tree
	 * @param	boolean		includeNode: if set, apply callback to the node
	 * @param	string		startCallback: a function call to be evaluated on each node, before walking the children
	 * @param	string		endCallback: a function call to be evaluated on each node, after walking the children
	 * @param	array		args: array of arguments
	 * @return	void
	 */
	walk: function (node, includeNode, startCallback, endCallback, args) {
		if (!this.removeTagsAndContents.test(node.nodeName)) {
			if (includeNode) {
				eval(startCallback);
			}
				// Walk the children
			var child = node.firstChild;
			while (child) {
				this.walk(child, true, startCallback, endCallback, args);
				child = child.nextSibling;
			}
			if (includeNode) {
				eval(endCallback);
			}
		}
	},
	/*
	 * Generate html string from DOM tree
	 *
	 * @param	object		node: the root node of the tree
	 * @param	boolean		includeNode: if set, apply callback to root element
	 * @return	string		rendered html code
	 */
	render: function (node, includeNode) {
		this.html = '';
		this.walk(node, includeNode, 'args[0].renderNodeStart(node)', 'args[0].renderNodeEnd(node)', [this]);
		return this.html;
	},
	/*
	 * Generate html string for the start of a node
	 *
	 * @param	object		node: the root node of the tree
	 * @return	string		rendered html code (accumulated in this.html)
	 */
	renderNodeStart: function (node) {
		var html = '';
		switch (node.nodeType) {
			case HTMLArea.DOM.ELEMENT_NODE:
				if (this.keepTags.test(node.nodeName) && !this.removeTags.test(node.nodeName)) {
					html += this.setOpeningTag(node);
				}
				break;
			case HTMLArea.DOM.TEXT_NODE:
				html += /^(script|style)$/i.test(node.parentNode.nodeName) ? node.data : HTMLArea.util.htmlEncode(node.data);
				break;
			case HTMLArea.DOM.ENTITY_NODE:
				html += node.nodeValue;
				break;
			case HTMLArea.DOM.ENTITY_REFERENCE_NODE:
				html += '&' + node.nodeValue + ';';
				break;
			case HTMLArea.DOM.COMMENT_NODE:
				if (this.keepComments) {
					html += '<!--' + node.data + '-->';
				}
				break;
			case HTMLArea.DOM.CDATA_SECTION_NODE:
				if (this.keepCDATASections) {
					html += '<![CDATA[' + node.data + ']]>';
				}
				break;
			default:
					// Ignore all other node types
				break;
		}
		this.html += html;
	},
	/*
	 * Generate html string for the end of a node
	 *
	 * @param	object		node: the root node of the tree
	 * @return	string		rendered html code (accumulated in this.html)
	 */
	renderNodeEnd: function (node) {
		var html = '';
		if (node.nodeType === HTMLArea.DOM.ELEMENT_NODE) {
			if (this.keepTags.test(node.nodeName) && !this.removeTags.test(node.nodeName)) {
				html += this.setClosingTag(node);
			}
		}
		this.html += html;
	},
	/*
	 * Get the attributes of the node, filtered and cleaned-up
	 *
	 * @param	object		node: the node
	 * @return	object		an object with attribute name as key and attribute value as value
	 */
	getAttributes: function (node) {
		var attributes = node.attributes;
		var filterededAttributes = [];
		var attribute, attributeName, attributeValue;
		for (var i = attributes.length; --i >= 0;) {
			attribute = attributes.item(i);
			attributeName = attribute.nodeName.toLowerCase();
			attributeValue = attribute.nodeValue;
				// Ignore some attributes and those configured to be removed
			if (/_moz|contenteditable|complete/.test(attributeName) || this.removeAttributes.test(attributeName)) {
				continue;
			}
				// Ignore default values except for the value attribute
			if (!attribute.specified && attributeName !== 'value') {
				continue;
			}
			if (Ext.isIE) {
					// IE before I9 fails to put style in attributes list.
				if (attributeName === 'style') {
					if (HTMLArea.isIEBeforeIE9) {
						attributeValue = node.style.cssText;
					}
					// May need to strip the base url
				} else if (attributeName === 'href' || attributeName === 'src') {
					attributeValue = this.stripBaseURL(attributeValue);
					// Ignore value="0" reported by IE on all li elements
				} else if (attributeName === 'value' && /^li$/i.test(node.nodeName) && attributeValue == 0) {
					continue;
				}
			} else if (Ext.isGecko) {
					// Ignore special values reported by Mozilla
				if (/(_moz|^$)/.test(attributeValue)) {
					continue;
					// Pasted internal url's are made relative by Mozilla: https://bugzilla.mozilla.org/show_bug.cgi?id=613517
				} else if (attributeName === 'href' || attributeName === 'src') {
					attributeValue = HTMLArea.DOM.addBaseUrl(attributeValue, this.baseUrl);
				}
			}
				// Ignore id attributes generated by ExtJS
			if (attributeName === 'id' && /^ext-gen/.test(attributeValue)) {
				continue;
			}
			filterededAttributes.push({
				attributeName: attributeName,
				attributeValue: attributeValue
			});
		}
		return (Ext.isWebKit || Ext.isOpera) ? filterededAttributes.reverse() : filterededAttributes;
	},
	/*
	 * Set opening tag for a node
	 *
	 * @param	object		node: the node
	 * @return	object		opening tag
	 */
	setOpeningTag: function (node) {
		var html = '';
			// Handle br oddities
		if (/^br$/i.test(node.nodeName)) {
				// Remove Mozilla special br node
			if (Ext.isGecko && node.hasAttribute('_moz_editor_bogus_node')) {
				return html;
				// In Gecko, whenever some text is entered in an empty block, a trailing br tag is added by the browser.
				// If the br element is a trailing br in a block element with no other content or with content other than a br, it may be configured to be removed
			} else if (this.removeTrailingBR && !node.nextSibling && HTMLArea.DOM.isBlockElement(node.parentNode) && (!node.previousSibling || !/^br$/i.test(node.previousSibling.nodeName))) {
						// If an empty paragraph with a class attribute, insert a non-breaking space so that RTE transform does not clean it away
					if (!node.previousSibling && node.parentNode && /^p$/i.test(node.parentNode.nodeName) && node.parentNode.className) {
						html += "&nbsp;";
					}
				return html;
			}
		}
			// Normal node
		var attributes = this.getAttributes(node);
		for (var i = 0, n = attributes.length; i < n; i++) {
			html +=  ' ' + attributes[i]['attributeName'] + '="' + HTMLArea.util.htmlEncode(attributes[i]['attributeValue']) + '"';
		}
		html = '<' + node.nodeName.toLowerCase() + html + (HTMLArea.DOM.RE_noClosingTag.test(node.nodeName) ? ' />' : '>');
			// Fix orphan list elements
		if (/^li$/i.test(node.nodeName) && !/^[ou]l$/i.test(node.parentNode.nodeName)) {
			html = '<ul>' + html;
		}
		return html;
	},
	/*
	 * Set closing tag for a node
	 *
	 * @param	object		node: the node
	 * @return	object		closing tag, if required
	 */
	setClosingTag: function (node) {
		var html = HTMLArea.DOM.RE_noClosingTag.test(node.nodeName) ? '' : '</' + node.nodeName.toLowerCase() + '>';
			// Fix orphan list elements
		if (/^li$/i.test(node.nodeName) && !/^[ou]l$/i.test(node.parentNode.nodeName)) {
			html += '</ul>';
		}
		return html;
	},
	/*
	 * Strip base url
	 * May be overridden by link handling plugin
	 *
	 * @param	string		value: value of a href or src attribute
	 * @return	tring		stripped value
	 */
	stripBaseURL: function (value) {
		return value;
	}
});
/***************************************************
 *  HTMLArea.DOM.Selection: Selection object
 ***************************************************/
HTMLArea.DOM.Selection = function (config) {
};
HTMLArea.DOM.Selection = Ext.extend(HTMLArea.DOM.Selection, {
	/*
	 * Reference to the editor MUST be set in config
	 */
	editor: null,
	/*
	 * Reference to the editor document
	 */
	document: null,
	/*
	 * Reference to the editor iframe window
	 */
	window: null,
	/*
	 * The current selection
	 */
	selection: null,
	/*
	 * HTMLArea.DOM.Selection constructor
	 */
	constructor: function (config) {
		    // Apply config
		Ext.apply(this, config);
			// Initialize references
		this.document = this.editor.document;
		this.window = this.editor.iframe.getEl().dom.contentWindow;
			// Set current selection
		this.get();
	},
	/*
	 * Get the current selection object
	 *
	 * @return	object		this
	 */
	get: function () {
		this.editor.focus();
	    this.selection = this.window.getSelection ? this.window.getSelection() : this.document.selection;
	    return this;
	},
	/*
	 * Get the type of the current selection
	 *
	 * @return	string		the type of selection ("None", "Text" or "Control")
	 */
	getType: function() {
			// By default set the type to "Text"
		var type = 'Text';
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (Ext.isFunction(this.selection.getRangeAt)) {
					// Check if the current selection is a Control
				if (this.selection && this.selection.rangeCount == 1) {
					var range = this.selection.getRangeAt(0);
					if (range.startContainer.nodeType === HTMLArea.DOM.ELEMENT_NODE) {
						if (
								// Gecko
							(range.startContainer == range.endContainer && (range.endOffset - range.startOffset) == 1) ||
								// Opera and WebKit
							(range.endContainer.nodeType === HTMLArea.DOM.TEXT_NODE && range.endOffset == 0 && range.startContainer.childNodes[range.startOffset].nextSibling == range.endContainer)
						) {
							if (/^(img|hr|li|table|tr|td|embed|object|ol|ul|dl)$/i.test(range.startContainer.childNodes[range.startOffset].nodeName)) {
								type = 'Control';
							}
						}
					}
				}
			} else {
					// IE8 or IE7
				type = this.selection.type;
			}
		}
		return type;
	},
	/*
	 * Empty the current selection
	 *
	 * @return	object		this
	 */
	empty: function () {
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (Ext.isFunction(this.selection.removeAllRanges)) {
				this.selection.removeAllRanges();
			} else {
					// IE8, IE7 or old version of WebKit
				this.selection.empty();
			}
			if (Ext.isOpera) {
				this.editor.focus();
			}
		}
		return this;
	},
	/*
	 * Determine whether the current selection is empty or not
	 *
	 * @return	boolean		true, if the selection is empty
	 */
	isEmpty: function () {
		var isEmpty = true;
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (HTMLArea.isIEBeforeIE9) {
				switch (this.selection.type) {
					case 'None':
						isEmpty = true;
						break;
					case 'Text':
						isEmpty = !this.createRange().text;
						break;
					default:
						isEmpty = !this.createRange().htmlText;
						break;
				}
			} else {
				isEmpty = this.selection.isCollapsed;
			}
		}
		return isEmpty;
	},
	/*
	 * Get a range corresponding to the current selection
	 *
	 * @return	object		the range of the selection
	 */
	createRange: function () {
		var range;
		this.get();
		if (HTMLArea.isIEBeforeIE9) {
			range = this.selection.createRange();
		} else {
			if (Ext.isEmpty(this.selection)) {
				range = this.document.createRange();
			} else {
					// Older versions of WebKit did not support getRangeAt
				if (Ext.isWebKit && !Ext.isFunction(this.selection.getRangeAt)) {
					range = this.document.createRange();
					if (this.selection.baseNode == null) {
						range.setStart(this.document.body, 0);
						range.setEnd(this.document.body, 0);
					} else {
						range.setStart(this.selection.baseNode, this.selection.baseOffset);
						range.setEnd(this.selection.extentNode, this.selection.extentOffset);
						if (range.collapsed != this.selection.isCollapsed) {
							range.setStart(this.selection.extentNode, this.selection.extentOffset);
							range.setEnd(this.selection.baseNode, this.selection.baseOffset);
						}
					}
				} else {
					try {
						range = this.selection.getRangeAt(0);
					} catch (e) {
						range = this.document.createRange();
					}
				}
			}
		}
		return range;
	},
	/*
	 * Return the ranges of the selection
	 *
	 * @return	array		array of ranges
	 */
	getRanges: function () {
		this.get();
		var ranges = [];
			// Older versions of WebKit, IE7 and IE8 did not support getRangeAt
		if (!Ext.isEmpty(this.selection) && Ext.isFunction(this.selection.getRangeAt)) {
			for (var i = this.selection.rangeCount; --i >= 0;) {
				ranges.push(this.selection.getRangeAt(i));
			}
		} else {
			ranges.push(this.createRange());
		}
		return ranges;
	},
	/*
	 * Add a range to the selection
	 *
	 * @param	object		range: the range to be added to the selection
	 *
	 * @return	object		this
	 */
	addRange: function (range) {
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (Ext.isFunction(this.selection.addRange)) {
				this.selection.addRange(range);
			} else if (Ext.isWebKit) {
				this.selection.setBaseAndExtent(range.startContainer, range.startOffset, range.endContainer, range.endOffset);
			}
		}
		return this;
	},
	/*
	 * Set the ranges of the selection
	 *
	 * @param	array		ranges: array of range to be added to the selection
	 *
	 * @return	object		this
	 */
	setRanges: function (ranges) {
		this.get();
		this.empty();
		for (var i = ranges.length; --i >= 0;) {
			this.addRange(ranges[i]);
		}
		return this;
	},
	/*
	 * Set the selection to a given range
	 *
	 * @param	object		range: the range to be selected
	 *
	 * @return	object		this
	 */
	selectRange: function (range) {
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (Ext.isFunction(this.selection.getRangeAt)) {
				this.empty().addRange(range);
			} else {
					// IE8 or IE7
				range.select();
			}
		}
		return this;
	},
	/*
	 * Set the selection to a given node
	 *
	 * @param	object		node: the node to be selected
	 * @param	boolean		endPoint: collapse the selection at the start point (true) or end point (false) of the node
	 *
	 * @return	object		this
	 */
	selectNode: function (node, endPoint) {
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (HTMLArea.isIEBeforeIE9) {
					// IE8/7/6 cannot set this type of selection
				this.selectNodeContents(node, endPoint);
			} else if (Ext.isWebKit && /^(img)$/i.test(node.nodeName)) {
				this.selection.setBaseAndExtent(node, 0, node, 1);
			} else {
				var range = this.document.createRange();
				if (node.nodeType === HTMLArea.DOM.ELEMENT_NODE && /^(body)$/i.test(node.nodeName)) {
					if (Ext.isWebKit) {
						range.setStart(node, 0);
						range.setEnd(node, node.childNodes.length);
					} else {
						range.selectNodeContents(node);
					}
				} else {
					range.selectNode(node);
				}
				if (typeof(endPoint) !== 'undefined') {
					range.collapse(endPoint);
				}
				this.selectRange(range);
			}
		}
		return this;
	},
	/*
	 * Set the selection to the inner contents of a given node
	 *
	 * @param	object		node: the node of which the contents are to be selected
	 * @param	boolean		endPoint: collapse the selection at the start point (true) or end point (false)
	 *
	 * @return	object		this
	 */
	selectNodeContents: function (node, endPoint) {
		var range;
		this.get();
		if (!Ext.isEmpty(this.selection)) {
			if (HTMLArea.isIEBeforeIE9) {
				range = this.document.body.createTextRange();
				range.moveToElementText(node);
			} else {
				range = this.document.createRange();
				if (Ext.isWebKit) {
					range.setStart(node, 0);
					if (node.nodeType === HTMLArea.DOM.TEXT_NODE || node.nodeType === HTMLArea.DOM.COMMENT_NODE || node.nodeType === HTMLArea.DOM.CDATA_SECTION_NODE) {
						range.setEnd(node, node.textContent.length);
					} else {
						range.setEnd(node, node.childNodes.length);
					}
				} else {
					range.selectNodeContents(node);
				}
			}
			if (typeof(endPoint) !== 'undefined') {
				range.collapse(endPoint);
			}
			this.selectRange(range);
		}
		return this;
	},
	/*
	 * Get the deepest node that contains both endpoints of the current selection.
	 *
	 * @return	object		the deepest node that contains both endpoints of the current selection.
	 */
	getParentElement: function () {
		var parentElement,
			range;
		this.get();
		if (HTMLArea.isIEBeforeIE9) {
			range = this.createRange();
			switch (this.selection.type) {
				case 'Text':
				case 'None':
					parentElement = range.parentElement();
					if (/^(form)$/i.test(parentElement.nodeName)) {
						parentElement = this.document.body;
					} else if (/^(li)$/i.test(parentElement.nodeName) && range.htmlText.replace(/\s/g, '') == parentElement.parentNode.outerHTML.replace(/\s/g, '')) {
						parentElement = parentElement.parentNode;
					}
					break;
				case 'Control':
					parentElement = range.item(0);
					break;
				default:
					parentElement = this.document.body;
					break;
			}
		} else {
			if (this.getType() === 'Control') {
				parentElement = this.getElement();
			} else {
				range = this.createRange();
				parentElement = range.commonAncestorContainer;
					// Firefox 3 may report the document as commonAncestorContainer
				if (parentElement.nodeType === HTMLArea.DOM.DOCUMENT_NODE) {
					parentElement = this.document.body;
				} else {
					while (parentElement && parentElement.nodeType === HTMLArea.DOM.TEXT_NODE) {
						parentElement = parentElement.parentNode;
					}
				}
			}
		}
		return parentElement;
	},
	/*
	 * Get the selected element (if any), in the case that a single element (object like and image or a table) is selected
	 * In IE language, we have a range of type 'Control'
	 *
	 * @return	object		the selected node
	 */
	getElement: function () {
		var element = null;
		this.get();
		if (!Ext.isEmpty(this.selection) && this.selection.anchorNode && this.selection.anchorNode.nodeType === HTMLArea.DOM.ELEMENT_NODE && this.getType() == 'Control') {
			element = this.selection.anchorNode.childNodes[this.selection.anchorOffset];
				// For Safari, the anchor node for a control selection is the control itself
			if (!element) {
				element = this.selection.anchorNode;
			} else if (element.nodeType !== HTMLArea.DOM.ELEMENT_NODE) {
				element = null;
			}
		}
		return element;
	},
	/*
	 * Get the deepest element ancestor of the selection that is of one of the specified types
	 *
	 * @param	array		types: an array of nodeNames
	 *
	 * @return	object		the found ancestor of one of the given types or null
	 */
	getFirstAncestorOfType: function (types) {
		var node = this.getParentElement();
		return HTMLArea.DOM.getFirstAncestorOfType(node, types);
	},
	/*
	 * Get an array with all the ancestor nodes of the current selection
	 *
	 * @return	array		the ancestor nodes
	 */
	getAllAncestors: function () {
		var parent = this.getParentElement(),
			ancestors = [];
		while (parent && parent.nodeType === HTMLArea.DOM.ELEMENT_NODE && !/^(body)$/i.test(parent.nodeName)) {
			ancestors.push(parent);
			parent = parent.parentNode;
		}
		ancestors.push(this.document.body);
		return ancestors;
	},
	/*
	 * Get an array with the parent elements of a multiple selection
	 *
	 * @return	array		the selected elements
	 */
	getElements: function () {
		var statusBarSelection = this.editor.statusBar ? this.editor.statusBar.getSelection() : null,
			elements = [];
		if (statusBarSelection) {
			elements.push(statusBarSelection);
		} else {
			var ranges = this.getRanges();
				parent;
			if (ranges.length > 1) {
				for (var i = ranges.length; --i >= 0;) {
					parent = range[i].commonAncestorContainer;
						// Firefox 3 may report the document as commonAncestorContainer
					if (parent.nodeType === HTMLArea.DOM.DOCUMENT_NODE) {
						parent = this.document.body;
					} else {
						while (parent && parent.nodeType === HTMLArea.DOM.TEXT_NODE) {
							parent = parent.parentNode;
						}
					}
					elements.push(parent);
				}
			} else {
				elements.push(this.getParentElement());
			}
		}
		return elements;
	},
	/*
	 * Get the node whose contents are currently fully selected
	 *
	 * @return	object		the fully selected node, if any, null otherwise
	 */
	getFullySelectedNode: function () {
		var node = null,
			isFullySelected = false;
		this.get();
		if (!this.isEmpty()) {
			var type = this.getType();
			var range = this.createRange();
			var ancestors = this.getAllAncestors();
			Ext.each(ancestors, function (ancestor) {
				if (HTMLArea.isIEBeforeIE9) {
					isFullySelected = (type !== 'Control' && ancestor.innerText == range.text) || (type === 'Control' && ancestor.innerText == range.item(0).text);
				} else {
					isFullySelected = (ancestor.textContent == range.toString());
				}
				if (isFullySelected) {
					node = ancestor;
					return false;
				}
			});
				// Working around bug with WebKit selection
			if (Ext.isWebKit && !isFullySelected) {
				var statusBarSelection = this.editor.statusBar ? this.editor.statusBar.getSelection() : null;
				if (statusBarSelection && statusBarSelection.textContent == range.toString()) {
					isFullySelected = true;
					node = statusBarSelection;
				}
			}
		}
		return node;
	},
	/*
	 * Get the block elements containing the start and the end points of the selection
	 *
	 * @return	object		object with properties start and end set to the end blocks of the selection
	 */
	getEndBlocks: function () {
		var range = this.createRange(),
			parentStart,
			parentEnd;
		if (HTMLArea.isIEBeforeIE9) {
			if (this.getType() === 'Control') {
				parentStart = range.item(0);
				parentEnd = parentStart;
			} else {
				var rangeEnd = range.duplicate();
				range.collapse(true);
				parentStart = range.parentElement();
				rangeEnd.collapse(false);
				parentEnd = rangeEnd.parentElement();
			}
		} else {
			parentStart = range.startContainer;
			if (/^(body)$/i.test(parentStart.nodeName)) {
				parentStart = parentStart.firstChild;
			}
			parentEnd = range.endContainer;
			if (/^(body)$/i.test(parentEnd.nodeName)) {
				parentEnd = parentEnd.lastChild;
			}
		}
		while (parentStart && !HTMLArea.DOM.isBlockElement(parentStart)) {
			parentStart = parentStart.parentNode;
		}
		while (parentEnd && !HTMLArea.DOM.isBlockElement(parentEnd)) {
			parentEnd = parentEnd.parentNode;
		}
		return {
			start: parentStart,
			end: parentEnd
		};
	},
	/*
	 * Determine whether the end poins of the current selection are within the same block
	 *
	 * @return	boolean		true if the end points of the current selection are in the same block
	 */
	endPointsInSameBlock: function() {
		var endPointsInSameBlock = true;
		this.get();
		if (!this.isEmpty()) {
			var parent = this.getParentElement();
			var endBlocks = this.getEndBlocks();
			endPointsInSameBlock = (endBlocks.start === endBlocks.end && !/^(table|thead|tbody|tfoot|tr)$/i.test(parent.nodeName));
		}
		return endPointsInSameBlock;
	},
	/*
	 * Retrieve the HTML contents of the current selection
	 *
	 * @return	string		HTML text of the current selection
	 */
	getHtml: function () {
		var range = this.createRange(),
			html = '';
		if (HTMLArea.isIEBeforeIE9) {
			if (this.getType() === 'Control') {
					// We have a controlRange collection
				var bodyRange = this.document.body.createTextRange();
				bodyRange.moveToElementText(range(0));
				html = bodyRange.htmlText;
			} else {
				html = range.htmlText;
			}
		} else if (!range.collapsed) {
			var cloneContents = range.cloneContents();
			if (!cloneContents) {
				cloneContents = this.document.createDocumentFragment();
			}
			html = this.editor.iframe.htmlRenderer.render(cloneContents, false);
		}
		return html;
	},
	 /*
	 * Insert a node at the current position
	 * Delete the current selection, if any.
	 * Split the text node, if needed.
	 *
	 * @param	object		toBeInserted: the node to be inserted
	 *
	 * @return	object		this
	 */
	insertNode: function (toBeInserted) {
		if (HTMLArea.isIEBeforeIE9) {
			this.insertHtml(toBeInserted.outerHTML);
		} else {
			var range = this.createRange();
			range.deleteContents();
			toBeSelected = (toBeInserted.nodeType === HTMLArea.DOM.DOCUMENT_FRAGMENT_NODE) ? toBeInserted.lastChild : toBeInserted;
			range.insertNode(toBeInserted);
			this.selectNodeContents(toBeSelected, false);
		}
		return this;
	},
	/*
	 * Insert HTML source code at the current position
	 * Delete the current selection, if any.
	 *
	 * @param	string		html: the HTML source code
	 *
	 * @return	object		this
	 */
	insertHtml: function (html) {
		if (HTMLArea.isIEBeforeIE9) {
			this.get();
			if (this.getType() === 'Control') {
				this.selection.clear();
				this.get();
			}
			var range = this.createRange();
			range.pasteHTML(html);
		} else {
			this.editor.focus();
			var fragment = this.document.createDocumentFragment();
			var div = this.document.createElement('div');
			div.innerHTML = html;
			while (div.firstChild) {
				fragment.appendChild(div.firstChild);
			}
			this.insertNode(fragment);
		}
		return this;
	},
	/*
	 * Surround the selection with an element specified by its start and end tags
	 * Delete the selection, if any.
	 *
	 * @param	string		startTag: the start tag
	 * @param	string		endTag: the end tag
	 *
	 * @return	void
	 */
	surroundHtml: function (startTag, endTag) {
		this.insertHtml(startTag + this.getHtml().replace(HTMLArea.DOM.RE_bodyTag, '') + endTag);
	},
	/*
	 * Execute some native execCommand command on the current selection
	 *
	 * @param	string		cmdID: the command name or id
	 * @param	object		UI:
	 * @param	object		param:
	 *
	 * @return	boolean		false
	 */
	execCommand: function (cmdID, UI, param) {
		var success = true;
		this.editor.focus();
		try {
			this.document.execCommand(cmdID, UI, param);
		} catch (e) {
			success = false;
			this.editor.appendToLog('HTMLArea.DOM.Selection', 'execCommand', e + ' by execCommand(' + cmdID + ')', 'error');
		}
		this.editor.updateToolbar();
		return success;
	},
	/*
	 * Handle backspace event on the current selection
	 *
	 * @return	boolean		true to stop the event and cancel the default action
	 */
	handleBackSpace: function () {
		var range = this.createRange();
		if (HTMLArea.isIEBeforeIE9) {
			if (this.getType() === 'Control') {
					// Deleting or backspacing on a control selection : delete the element
				var element = this.getParentElement();
				var parent = element.parentNode;
				parent.removeChild(el);
				return true;
			} else if (this.isEmpty()) {
					// Check if deleting an empty block with a table as next sibling
				var element = this.getParentElement();
				if (!element.innerHTML && HTMLArea.DOM.isBlockElement(element) && element.nextSibling && /^table$/i.test(element.nextSibling.nodeName)) {
					var previous = element.previousSibling;
					if (!previous) {
						this.selectNodeContents(element.nextSibling.rows[0].cells[0], true);
					} else if (/^table$/i.test(previous.nodeName)) {
						this.selectNodeContents(previous.rows[previous.rows.length-1].cells[previous.rows[previous.rows.length-1].cells.length-1], false);
					} else {
						range.moveStart('character', -1);
						range.collapse(true);
						range.select();
					}
					el.parentNode.removeChild(element);
					return true;
				}
			} else {
					// Backspacing into a link
				var range2 = range.duplicate();
				range2.moveStart('character', -1);
				var a = range2.parentElement();
				if (a != range.parentElement() && /^a$/i.test(a.nodeName)) {
					range2.collapse(true);
					range2.moveEnd('character', 1);
					range2.pasteHTML('');
					range2.select();
					return true;
				}
				return false;
			}
		} else {
			var self = this;
			window.setTimeout(function() {
				var range = self.createRange();
				var startContainer = range.startContainer;
				var startOffset = range.startOffset;
					// If the selection is collapsed...
				if (self.isEmpty()) {
						// ... and the cursor lies in a direct child of body...
					if (/^(body)$/i.test(startContainer.nodeName)) {
						var node = startContainer.childNodes[startOffset];
					} else if (/^(body)$/i.test(startContainer.parentNode.nodeName)) {
						var node = startContainer;
					} else {
						return false;
					}
						// ... which is a br or text node containing no non-whitespace character
					if (/^(br|#text)$/i.test(node.nodeName) && !/\S/.test(node.textContent)) {
							// Get a meaningful previous sibling in which to reposition de cursor
						var previousSibling = node.previousSibling;
						while (previousSibling && /^(br|#text)$/i.test(previousSibling.nodeName) && !/\S/.test(previousSibling.textContent)) {
							previousSibling = previousSibling.previousSibling;
						}
							// If there is no meaningful previous sibling, the cursor is at the start of body
						if (previousSibling) {
								// Remove the node
							HTMLArea.DOM.removeFromParent(node);
								// Position the cursor
							if (/^(ol|ul|dl)$/i.test(previousSibling.nodeName)) {
								self.selectNodeContents(previousSibling.lastChild, false);
							} else if (/^(table)$/i.test(previousSibling.nodeName)) {
								self.selectNodeContents(previousSibling.rows[previousSibling.rows.length-1].cells[previousSibling.rows[previousSibling.rows.length-1].cells.length-1], false);
							} else if (!/\S/.test(previousSibling.textContent) && previousSibling.firstChild) {
								self.selectNode(previousSibling.firstChild, true);
							} else {
								self.selectNodeContents(previousSibling, false);
							}
						}
					}
				}
			}, 10);
			return false;
		}
	},
	/*
	 * Detect emails and urls as they are typed in non-IE browsers
	 * Borrowed from Xinha (is not htmlArea) - http://xinha.gogo.co.nz/
	 *
	 * @param	object		event: the ExtJS key event
	 *
	 * @return	void
	 */
	detectURL: function (event) {
		var ev = event.browserEvent;
		var editor = this.editor;
		var selection = this.get().selection;
		if (!/^(a)$/i.test(this.getParentElement().nodeName)) {
			var autoWrap = function (textNode, tag) {
				var rightText = textNode.nextSibling;
				if (typeof(tag) === 'string') {
					tag = editor.document.createElement(tag);
				}
				var a = textNode.parentNode.insertBefore(tag, rightText);
				HTMLArea.DOM.removeFromParent(textNode);
				a.appendChild(textNode);
				selection.collapse(rightText, 0);
				rightText.parentNode.normalize();

				editor.unLink = function() {
					var t = a.firstChild;
					a.removeChild(t);
					a.parentNode.insertBefore(t, a);
					HTMLArea.DOM.removeFromParent(a);
					t.parentNode.normalize();
					editor.unLink = null;
					editor.unlinkOnUndo = false;
				};

				editor.unlinkOnUndo = true;
				return a;
			};
			switch (ev.which) {
					// Space or Enter or >, see if the text just typed looks like a URL, or email address and link it accordingly
				case 13:
				case 32:
					if (selection && selection.isCollapsed && selection.anchorNode.nodeType === HTMLArea.DOM.TEXT_NODE && selection.anchorNode.data.length > 3 && selection.anchorNode.data.indexOf('.') >= 0) {
						var midStart = selection.anchorNode.data.substring(0,selection.anchorOffset).search(/[a-zA-Z0-9]+\S{3,}$/);
						if (midStart == -1) {
							break;
						}
						if (this.getFirstAncestorOfType('a')) {
								// already in an anchor
							break;
						}
						var matchData = selection.anchorNode.data.substring(0,selection.anchorOffset).replace(/^.*?(\S*)$/, '$1');
						if (matchData.indexOf('@') != -1) {
							var m = matchData.match(HTMLArea.RE_email);
							if (m) {
								var leftText  = selection.anchorNode;
								var rightText = leftText.splitText(selection.anchorOffset);
								var midText   = leftText.splitText(midStart);
								var midEnd = midText.data.search(/[^a-zA-Z0-9\.@_\-]/);
								if (midEnd != -1) {
									var endText = midText.splitText(midEnd);
								}
								autoWrap(midText, 'a').href = 'mailto:' + m[0];
								break;
							}
						}
						var m = matchData.match(HTMLArea.RE_url);
						if (m) {
							var leftText  = selection.anchorNode;
							var rightText = leftText.splitText(selection.anchorOffset);
							var midText   = leftText.splitText(midStart);
							var midEnd = midText.data.search(/[^a-zA-Z0-9\._\-\/\&\?=:@]/);
							if (midEnd != -1) {
								var endText = midText.splitText(midEnd);
							}
							autoWrap(midText, 'a').href = (m[1] ? m[1] : 'http://') + m[3];
							break;
						}
					}
					break;
				default:
					if (ev.keyCode == 27 || (editor.unlinkOnUndo && ev.ctrlKey && ev.which == 122)) {
						if (editor.unLink) {
							editor.unLink();
							event.stopEvent();
						}
						break;
					} else if (ev.which || ev.keyCode == 8 || ev.keyCode == 46) {
						editor.unlinkOnUndo = false;
						if (selection.anchorNode && selection.anchorNode.nodeType === HTMLArea.DOM.TEXT_NODE) {
								// See if we might be changing a link
							var a = this.getFirstAncestorOfType('a');
							if (!a) {
								break;
							}
							if (!a.updateAnchorTimeout) {
								if (selection.anchorNode.data.match(HTMLArea.RE_email) && (a.href.match('mailto:' + selection.anchorNode.data.trim()))) {
									var textNode = selection.anchorNode;
									var fn = function() {
										a.href = 'mailto:' + textNode.data.trim();
										a.updateAnchorTimeout = setTimeout(fn, 250);
									};
									a.updateAnchorTimeout = setTimeout(fn, 250);
									break;
								}
								var m = selection.anchorNode.data.match(HTMLArea.RE_url);
								if (m && a.href.match(selection.anchorNode.data.trim())) {
									var textNode = selection.anchorNode;
									var fn = function() {
										var m = textNode.data.match(HTMLArea.RE_url);
										a.href = (m[1] ? m[1] : 'http://') + m[3];
										a.updateAnchorTimeout = setTimeout(fn, 250);
									}
									a.updateAnchorTimeout = setTimeout(fn, 250);
								}
							}
						}
					}
					break;
			}
		}
	},
	/*
	 * Enter event handler
	 *
	 * @return	boolean		true to stop the event and cancel the default action
	 */
	checkInsertParagraph: function() {
		var editor = this.editor;
		var i, left, right, rangeClone,
			sel	= this.get().selection,
			range	= this.createRange(),
			p	= this.getAllAncestors(),
			block	= null,
			a	= null,
			doc	= this.document;
		for (i = 0; i < p.length; ++i) {
			if (HTMLArea.DOM.isBlockElement(p[i]) && !/^(html|body|table|tbody|thead|tfoot|tr|dl)$/i.test(p[i].nodeName)) {
				block = p[i];
				break;
			}
		}
		if (block && /^(td|th|tr|tbody|thead|tfoot|table)$/i.test(block.nodeName) && this.editor.config.buttons.table && this.editor.config.buttons.table.disableEnterParagraphs) {
			return false;
		}
		if (!range.collapsed) {
			range.deleteContents();
		}
		this.empty();
		if (!block || /^(td|div|article|aside|footer|header|nav|section)$/i.test(block.nodeName)) {
			if (!block) {
				block = doc.body;
			}
			if (block.hasChildNodes()) {
				rangeClone = range.cloneRange();
				if (range.startContainer == block) {
						// Selection is directly under the block
					var blockOnLeft = null;
					var leftSibling = null;
						// Looking for the farthest node on the left that is not a block
					for (var i = range.startOffset; --i >= 0;) {
						if (HTMLArea.DOM.isBlockElement(block.childNodes[i])) {
							blockOnLeft = block.childNodes[i];
							break;
						} else {
							rangeClone.setStartBefore(block.childNodes[i]);
						}
					}
				} else {
						// Looking for inline or text container immediate child of block
					var inlineContainer = range.startContainer;
					while (inlineContainer.parentNode != block) {
						inlineContainer = inlineContainer.parentNode;
					}
						// Looking for the farthest node on the left that is not a block
					var leftSibling = inlineContainer;
					while (leftSibling.previousSibling && !HTMLArea.DOM.isBlockElement(leftSibling.previousSibling)) {
						leftSibling = leftSibling.previousSibling;
					}
					rangeClone.setStartBefore(leftSibling);
					var blockOnLeft = leftSibling.previousSibling;
				}
					// Avoiding surroundContents buggy in Opera and Safari
				left = doc.createElement('p');
				left.appendChild(rangeClone.extractContents());
				if (!left.textContent && !left.getElementsByTagName('img').length && !left.getElementsByTagName('table').length) {
					left.innerHTML = '<br />';
				}
				if (block.hasChildNodes()) {
					if (blockOnLeft) {
						left = block.insertBefore(left, blockOnLeft.nextSibling);
					} else {
						left = block.insertBefore(left, block.firstChild);
					}
				} else {
					left = block.appendChild(left);
				}
				block.normalize();
					// Looking for the farthest node on the right that is not a block
				var rightSibling = left;
				while (rightSibling.nextSibling && !HTMLArea.DOM.isBlockElement(rightSibling.nextSibling)) {
					rightSibling = rightSibling.nextSibling;
				}
				var blockOnRight = rightSibling.nextSibling;
				range.setEndAfter(rightSibling);
				range.setStartAfter(left);
					// Avoiding surroundContents buggy in Opera and Safari
				right = doc.createElement('p');
				right.appendChild(range.extractContents());
				if (!right.textContent && !right.getElementsByTagName('img').length && !right.getElementsByTagName('table').length) {
					right.innerHTML = '<br />';
				}
				if (!(left.childNodes.length == 1 && right.childNodes.length == 1 && left.firstChild.nodeName.toLowerCase() == 'br' && right.firstChild.nodeName.toLowerCase() == 'br')) {
					if (blockOnRight) {
						right = block.insertBefore(right, blockOnRight);
					} else {
						right = block.appendChild(right);
					}
					this.selectNodeContents(right, true);
				} else {
					this.selectNodeContents(left, true);
				}
				block.normalize();
			} else {
				var first = block.firstChild;
				if (first) {
					block.removeChild(first);
				}
				right = doc.createElement('p');
				if (Ext.isWebKit || Ext.isOpera) {
					right.innerHTML = '<br />';
				}
				right = block.appendChild(right);
				this.selectNodeContents(right, true);
			}
		} else {
			range.setEndAfter(block);
			var df = range.extractContents(), left_empty = false;
			if (!/\S/.test(block.innerHTML) || (!/\S/.test(block.textContent) && !/<(img|hr|table)/i.test(block.innerHTML))) {
				if (!Ext.isOpera) {
					block.innerHTML = '<br />';
				}
				left_empty = true;
			}
			p = df.firstChild;
			if (p) {
				if (!/\S/.test(p.innerHTML) || (!/\S/.test(p.textContent) && !/<(img|hr|table)/i.test(p.innerHTML))) {
					if (/^h[1-6]$/i.test(p.nodeName)) {
						p = HTMLArea.DOM.convertNode(p, 'p');
					}
					if (/^(dt|dd)$/i.test(p.nodeName)) {
						 p = HTMLArea.DOM.convertNode(p, /^(dt)$/i.test(p.nodeName) ? 'dd' : 'dt');
					}
					if (!Ext.isOpera) {
						p.innerHTML = '<br />';
					}
					if (/^li$/i.test(p.nodeName) && left_empty && (!block.nextSibling || !/^li$/i.test(block.nextSibling.nodeName))) {
						left = block.parentNode;
						left.removeChild(block);
						range.setEndAfter(left);
						range.collapse(false);
						p = HTMLArea.DOM.convertNode(p, /^(li|dd|td|th|p|h[1-6])$/i.test(left.parentNode.nodeName) ? 'br' : 'p');
					}
				}
				range.insertNode(df);
					// Remove any anchor created empty on both sides of the selection
				if (p.previousSibling) {
					var a = p.previousSibling.lastChild;
					if (a && /^a$/i.test(a.nodeName) && !/\S/.test(a.innerHTML)) {
						HTMLArea.DOM.convertNode(a, 'br');
					}
				}
				var a = p.lastChild;
				if (a && /^a$/i.test(a.nodeName) && !/\S/.test(a.innerHTML)) {
					HTMLArea.DOM.convertNode(a, 'br');
				}
					// Walk inside the deepest child element (presumably inline element)
				while (p.firstChild && p.firstChild.nodeType === HTMLArea.DOM.ELEMENT_NODE && !/^(br|img|hr|table)$/i.test(p.firstChild.nodeName)) {
					p = p.firstChild;
				}
				if (/^br$/i.test(p.nodeName)) {
					p = p.parentNode.insertBefore(doc.createTextNode('\x20'), p);
				} else if (!/\S/.test(p.innerHTML)) {
						// Need some element inside the deepest element
					p.appendChild(doc.createElement('br'));
				}
				this.selectNodeContents(p, true);
			} else {
				if (/^(li|dt|dd)$/i.test(block.nodeName)) {
					p = doc.createElement(block.nodeName);
				} else {
					p = doc.createElement('p');
				}
				if (!Ext.isOpera) {
					p.innerHTML = '<br />';
				}
				if (block.nextSibling) {
					p = block.parentNode.insertBefore(p, block.nextSibling);
				} else {
					p = block.parentNode.appendChild(p);
				}
				this.selectNodeContents(p, true);
			}
		}
		this.editor.scrollToCaret();
		return true;
	}
});
/***************************************************
 *  HTMLArea.DOM.BookMark: BookMark object
 ***************************************************/
HTMLArea.DOM.BookMark = function (config) {
};
HTMLArea.DOM.BookMark = Ext.extend(HTMLArea.DOM.BookMark, {
	/*
	 * Reference to the editor MUST be set in config
	 */
	editor: null,
	/*
	 * Reference to the editor document
	 */
	document: null,
	/*
	 * Reference to the editor selection object
	 */
	selection: null,
	/*
	 * HTMLArea.DOM.Selection constructor
	 */
	constructor: function (config) {
		    // Apply config
		Ext.apply(this, config);
			// Initialize references
		this.document = this.editor.document;
		this.selection = this.editor.getSelection();
	},
	/*
	 * Get a bookMark
	 *
	 * @param	object		range: the range to bookMark
	 * @param	boolean		nonIntrusive: if true, a non-intrusive bookmark is requested
	 *
	 * @return	object		the bookMark
	 */
	get: function (range, nonIntrusive) {
		var bookMark;
		if (HTMLArea.isIEBeforeIE9) {
			// Bookmarking will not work on control ranges
			try {
				bookMark = range.getBookmark();
			} catch (e) {
				bookMark = null;
			}
		} else {
			if (nonIntrusive) {
				bookMark = this.getNonIntrusiveBookMark(range, true);
			} else {
				bookMark = this.getIntrusiveBookMark(range);
			}
		}
		return bookMark;
	},
	/*
	 * Get an intrusive bookMark
	 * Adapted from FCKeditor
	 * This is an "intrusive" way to create a bookMark. It includes <span> tags
	 * in the range boundaries. The advantage of it is that it is possible to
	 * handle DOM mutations when moving back to the bookMark.
	 *
	 * @param	object		range: the range to bookMark
	 *
	 * @return	object		the bookMark
	 */
	getIntrusiveBookMark: function (range) {
		// Create the bookmark info (random IDs).
		var bookMark = {
			nonIntrusive: false,
			startId: (new Date()).valueOf() + Math.floor(Math.random()*1000) + 'S',
			endId: (new Date()).valueOf() + Math.floor(Math.random()*1000) + 'E'
		};
		var startSpan;
		var endSpan;
		var rangeClone = range.cloneRange();
		// For collapsed ranges, add just the start marker
		if (!range.collapsed ) {
			endSpan = this.document.createElement('span');
			endSpan.style.display = 'none';
			endSpan.id = bookMark.endId;
			endSpan.setAttribute('data-htmlarea-bookmark', true);
			endSpan.innerHTML = '&nbsp;';
			rangeClone.collapse(false);
			rangeClone.insertNode(endSpan);
		}
		startSpan = this.document.createElement('span');
		startSpan.style.display = 'none';
		startSpan.id = bookMark.startId;
		startSpan.setAttribute('data-htmlarea-bookmark', true);
		startSpan.innerHTML = '&nbsp;';
		var rangeClone = range.cloneRange();
		rangeClone.collapse(true);
		rangeClone.insertNode(startSpan);
		bookMark.startNode = startSpan;
		bookMark.endNode = endSpan;
		// Update the range position.
		if (endSpan) {
			range.setEndBefore(endSpan);
			range.setStartAfter(startSpan);
		} else {
			range.setEndAfter(startSpan);
			range.collapse(false);
		}
		return bookMark;
	},
	/*
	 * Get a non-intrusive bookMark
	 * Adapted from FCKeditor
	 *
	 * @param	object		range: the range to bookMark
	 * @param	boolean		normalized: if true, normalized enpoints are calculated
	 *
	 * @return	object		the bookMark
	 */
	getNonIntrusiveBookMark: function (range, normalized) {
		var startContainer = range.startContainer,
			endContainer = range.endContainer,
			startOffset = range.startOffset,
			endOffset = range.endOffset,
			collapsed = range.collapsed,
			child,
			previous,
			bookMark = {};
		if (!startContainer || !endContainer) {
			bookMark = {
				nonIntrusive: true,
				start: 0,
				end: 0
			};
		} else {
			if (normalized) {
				// Find out if the start is pointing to a text node that might be normalized
				if (startContainer.nodeType == HTMLArea.DOM.NODE_ELEMENT) {
					child = startContainer.childNodes[startOffset];
					// In this case, move the start to that text node
					if (
						child
						&& child.nodeType == HTMLArea.DOM.NODE_TEXT
						&& startOffset > 0
						&& child.previousSibling.nodeType == HTMLArea.DOM.NODE_TEXT
					) {
						startContainer = child;
						startOffset = 0;
					}
					// Get the normalized offset
					if (child && child.nodeType == HTMLArea.DOM.NODE_ELEMENT) {
						startOffset = HTMLArea.DOM.getPositionWithinParent(child, true);
					}
				}
				// Normalize the start
				while (
					startContainer.nodeType == HTMLArea.DOM.NODE_TEXT
					&& (previous = startContainer.previousSibling)
					&& previous.nodeType == HTMLArea.DOM.NODE_TEXT
				) {
					startContainer = previous;
					startOffset += previous.nodeValue.length;
				}
				// Process the end only if not collapsed
				if (!collapsed) {
					// Find out if the start is pointing to a text node that will be normalized
					if (endContainer.nodeType == HTMLArea.DOM.NODE_ELEMENT) {
						child = endContainer.childNodes[endOffset];
						// In this case, move the end to that text node
						if (
							child
							&& child.nodeType == HTMLArea.DOM.NODE_TEXT
							&& endOffset > 0
							&& child.previousSibling.nodeType == HTMLArea.DOM.NODE_TEXT
						) {
							endContainer = child;
							endOffset = 0;
						}
						// Get the normalized offset
						if (child && child.nodeType == HTMLArea.DOM.NODE_ELEMENT) {
							endOffset = HTMLArea.DOM.getPositionWithinParent(child, true);
						}
					}
					// Normalize the end
					while (
						endContainer.nodeType == HTMLArea.DOM.NODE_TEXT
						&& (previous = endContainer.previousSibling)
						&& previous.nodeType == HTMLArea.DOM.NODE_TEXT
					) {
						endContainer = previous;
						endOffset += previous.nodeValue.length;
					}
				}
			}
			bookMark = {
				start: this.editor.domNode.getPositionWithinTree(startContainer, normalized),
				end: collapsed ? null : getPositionWithinTree(endContainer, normalized),
				startOffset: startOffset,
				endOffset: endOffset,
				normalized: normalized,
				collapsed: collapsed,
				nonIntrusive: true
			};
		}
		return bookMark;
	},
	/*
	 * Get the end point of the bookMark
	 * Adapted from FCKeditor
	 *
	 * @param	object		bookMark: the bookMark
	 * @param	boolean		endPoint: true, for startPoint, false for endPoint
	 *
	 * @return	object		the endPoint node
	 */
	getEndPoint: function (bookMark, endPoint) {
		if (endPoint) {
			return this.document.getElementById(bookMark.startId);
		} else {
			return this.document.getElementById(bookMark.endId);
		}
	},
	/*
	 * Get a range and move it to the bookMark
	 *
	 * @param	object		bookMark: the bookmark to move to
	 *
	 * @return	object		the range that was bookmarked
	 */
	moveTo: function (bookMark) {
		var range = this.selection.createRange();
		if (HTMLArea.isIEBeforeIE9) {
			if (bookMark) {
				range.moveToBookmark(bookMark);
			}
		} else {
			if (bookMark.nonIntrusive) {
				range = this.moveToNonIntrusiveBookMark(range, bookMark);
			} else {
				range = this.moveToIntrusiveBookMark(range, bookMark);
			}
		}
		return range;
	},
	/*
	 * Move the range to the intrusive bookMark
	 * Adapted from FCKeditor
	 *
	 * @param	object		range: the range to be moved
	 * @param	object		bookMark: the bookmark to move to
	 *
	 * @return	object		the range that was bookmarked
	 */
	moveToIntrusiveBookMark: function (range, bookMark) {
		var startSpan = this.getEndPoint(bookMark, true),
			endSpan = this.getEndPoint(bookMark, false),
			parent;
		if (startSpan) {
			// If the previous sibling is a text node, let the anchorNode have it as parent
			if (startSpan.previousSibling && startSpan.previousSibling.nodeType === HTMLArea.DOM.TEXT_NODE) {
				range.setStart(startSpan.previousSibling, startSpan.previousSibling.data.length);
			} else {
				range.setStartBefore(startSpan);
			}
			HTMLArea.DOM.removeFromParent(startSpan);
		} else {
			// For some reason, the startSpan was removed or its id attribute was removed so that it cannot be retrieved
			range.setStart(this.document.body, 0);
		}
		// If the bookmarked range was collapsed, the end span will not be available
		if (endSpan) {
			// If the next sibling is a text node, let the focusNode have it as parent
			if (endSpan.nextSibling && endSpan.nextSibling.nodeType === HTMLArea.DOM.TEXT_NODE) {
				range.setEnd(endSpan.nextSibling, 0);
			} else {
				range.setEndBefore(endSpan);
			}
			HTMLArea.DOM.removeFromParent(endSpan);
		} else {
			range.collapse(true);
		}
		return range;
	},
	/*
	 * Move the range to the non-intrusive bookMark
	 * Adapted from FCKeditor
	 *
	 * @param	object		range: the range to be moved
	 * @param	object		bookMark: the bookMark to move to
	 *
	 * @return	object		the range that was bookmarked
	 */
	moveToNonIntrusiveBookMark: function (range, bookMark) {
		if (bookMark.start) {
			// Get the start information
			var startContainer = this.editor.getNodeByPosition(bookMark.start, bookMark.normalized),
				startOffset = bookMark.startOffset;
			// Set the start boundary
			range.setStart(startContainer, startOffset);
			// Get the end information
			var endContainer = bookMark.end && this.editor.getNodeByPosition(bookMark.end, bookMark.normalized),
				endOffset = bookMark.endOffset;
			// Set the end boundary. If not available, collapse the range
			if (endContainer) {
				range.setEnd(endContainer, endOffset);
			} else {
				range.collapse(true);
			}
		}
		return range;
	}
});
/***************************************************
 *  HTMLArea.DOM.Node: Node object
 ***************************************************/
HTMLArea.DOM.Node = function (config) {
};
HTMLArea.DOM.Node = Ext.extend(HTMLArea.DOM.Node, {
	/*
	 * Reference to the editor MUST be set in config
	 */
	editor: null,
	/*
	 * Reference to the editor document
	 */
	document: null,
	/*
	 * Reference to the editor selection object
	 */
	selection: null,
	/*
	 * Reference to the editor bookmark object
	 */
	bookMark: null,
	/*
	 * HTMLArea.DOM.Selection constructor
	 */
	constructor: function (config) {
		    // Apply config
		Ext.apply(this, config);
			// Initialize references
		this.document = this.editor.document;
		this.selection = this.editor.getSelection();
		this.bookMark = this.editor.getBookMark();
	},
	/*
	 * Remove the given element
	 *
	 * @param	object		element: the element to be removed, content and selection being preserved
	 *
	 * @return	void
	 */
	removeMarkup: function (element) {
		var bookMark = this.bookMark.get(this.selection.createRange());
		var parent = element.parentNode;
		while (element.firstChild) {
			parent.insertBefore(element.firstChild, element);
		}
		parent.removeChild(element);
		this.selection.selectRange(this.bookMark.moveTo(bookMark));
	},
	/*
	 * Wrap the range with an inline element
	 *
	 * @param	string	element: the node that will wrap the range
	 * @param	object	range: the range to be wrapped
	 *
	 * @return	void
	 */
	wrapWithInlineElement: function (element, range) {
		if (HTMLArea.isIEBeforeIE9) {
			var nodeName = element.nodeName;
			var bookMark = this.bookMark.get(range);
			if (range.parentElement) {
				var parent = range.parentElement();
				var rangeStart = range.duplicate();
				rangeStart.collapse(true);
				var parentStart = rangeStart.parentElement();
				var rangeEnd = range.duplicate();
				rangeEnd.collapse(true);
				var newRange = this.selection.createRange();

				var parentEnd = rangeEnd.parentElement();
				var upperParentStart = parentStart;
				if (parentStart !== parent) {
					while (upperParentStart.parentNode !== parent) {
						upperParentStart = upperParentStart.parentNode;
					}
				}

				element.innerHTML = range.htmlText;
					// IE eats spaces on the start boundary
				if (range.htmlText.charAt(0) === '\x20') {
					element.innerHTML = '&nbsp;' + element.innerHTML;
				}
				var elementClone = element.cloneNode(true);
				range.pasteHTML(element.outerHTML);
					// IE inserts the element as the last child of the start container
				if (parentStart !== parent
						&& parentStart.lastChild
						&& parentStart.lastChild.nodeType === HTMLArea.DOM.ELEMENT_NODE
						&& parentStart.lastChild.nodeName.toLowerCase() === nodeName) {
					parent.insertBefore(elementClone, upperParentStart.nextSibling);
					parentStart.removeChild(parentStart.lastChild);
						// Sometimes an empty previous sibling was created
					if (elementClone.previousSibling
							&& elementClone.previousSibling.nodeType === HTMLArea.DOM.ELEMENT_NODE
							&& !elementClone.previousSibling.innerText) {
						parent.removeChild(elementClone.previousSibling);
					}
						// The bookmark will not work anymore
					newRange.moveToElementText(elementClone);
					newRange.collapse(false);
					newRange.select();
				} else {
						// Working around IE boookmark bug
					if (parentStart != parentEnd) {
						var newRange = this.selection.createRange();
						if (newRange.moveToBookmark(bookMark)) {
							newRange.collapse(false);
							newRange.select();
						}
					} else {
						range.collapse(false);
					}
				}
				parent.normalize();
			} else {
				var parent = range.item(0);
				element = parent.parentNode.insertBefore(element, parent);
				element.appendChild(parent);
				this.bookMark.moveTo(bookMark);
			}
		} else {
			element.appendChild(range.extractContents());
			range.insertNode(element);
			element.normalize();
				// Sometimes Firefox inserts empty elements just outside the boundaries of the range
			var neighbour = element.previousSibling;
			if (neighbour && (neighbour.nodeType !== HTMLArea.DOM.TEXT_NODE) && !/\S/.test(neighbour.textContent)) {
				HTMLArea.DOM.removeFromParent(neighbour);
			}
			neighbour = element.nextSibling;
			if (neighbour && (neighbour.nodeType !== HTMLArea.DOM.TEXT_NODE) && !/\S/.test(neighbour.textContent)) {
				HTMLArea.DOM.removeFromParent(neighbour);
			}
			this.selection.selectNodeContents(element, false);
		}
	},
	/*
	 * Get the position of the node within the document tree.
	 * The tree address returned is an array of integers, with each integer
	 * indicating a child index of a DOM node, starting from
	 * document.documentElement.
	 * The position cannot be used for finding back the DOM tree node once
	 * the DOM tree structure has been modified.
	 * Adapted from FCKeditor
	 *
	 * @param	object		node: the DOM node
	 * @param	boolean		normalized: if true, a normalized position is calculated
	 *
	 * @return	array		the position of the node
	 */
	getPositionWithinTree: function (node, normalized) {
		var documentElement = this.document.documentElement,
			current = node,
			position = [];
		while (current && current != documentElement) {
			var parentNode = current.parentNode;
			if (parentNode) {
				// Get the current node position
				position.unshift(HTMLArea.DOM.getPositionWithinParent(current, normalized));
			}
			current = parentNode;
		}
		return position;
	},
	/*
	 * Clean Apple wrapping span and font elements under the specified node
	 *
	 * @param	object		node: the node in the subtree of which cleaning is performed
	 *
	 * @return	void
	 */
	cleanAppleStyleSpans: function (node) {
		if (Ext.isWebKit) {
			if (node.getElementsByClassName) {
				var spans = node.getElementsByClassName('Apple-style-span');
				for (var i = spans.length; --i >= 0;) {
					this.removeMarkup(spans[i]);
				}
			} else {
				var spans = node.getElementsByTagName('span');
				for (var i = spans.length; --i >= 0;) {
					if (HTMLArea.DOM.hasClass(spans[i], 'Apple-style-span')) {
						this.removeMarkup(spans[i]);
					}
				}
				var fonts = node.getElementsByTagName('font');
				for (i = fonts.length; --i >= 0;) {
					if (HTMLArea.DOM.hasClass(fonts[i], 'Apple-style-span')) {
						this.removeMarkup(fonts[i]);
					}
				}
			}
		}
	}
});


});
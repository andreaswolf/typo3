<?php
namespace TYPO3\CMS\T3editor;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides a javascript-driven code editor with syntax highlighting for TS, HTML, CSS and more
 *
 * @author Tobias Liebig <mail_typo3@etobi.de>
 */
class T3editor implements \TYPO3\CMS\Core\SingletonInterface {

	const MODE_TYPOSCRIPT = 'typoscript';
	const MODE_JAVASCRIPT = 'javascript';
	const MODE_CSS = 'css';
	const MODE_XML = 'xml';
	const MODE_HTML = 'html';
	const MODE_PHP = 'php';
	const MODE_SPARQL = 'sparql';
	const MODE_MIXED = 'mixed';
	/**
	 * @var string
	 */
	protected $mode = '';

	/**
	 * @var string
	 */
	protected $ajaxSaveType = '';

	/**
	 * Counts the editors on the current page
	 *
	 * @var int
	 */
	protected $editorCounter = 0;

	/**
	 * sets the type of code to edit (::MODE_TYPOSCRIPT, ::MODE_JAVASCRIPT)
	 *
	 * @param $mode	string Expects one of the predefined constants
	 * @return \TYPO3\CMS\T3editor\T3editor
	 */
	public function setMode($mode) {
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Set the AJAX save type
	 *
	 * @param string $ajaxSaveType
	 * @return \TYPO3\CMS\T3editor\T3editor
	 */
	public function setAjaxSaveType($ajaxSaveType) {
		$this->ajaxSaveType = $ajaxSaveType;
		return $this;
	}

	/**
	 * Set mode by file
	 *
	 * @param string $file
	 * @return string
	 */
	public function setModeByFile($file) {
		$fileInfo = GeneralUtility::split_fileref($file);
		return $this->setModeByType($fileInfo['fileext']);
	}

	/**
	 * Set mode by type
	 *
	 * @param string $type
	 * @return void
	 */
	public function setModeByType($type) {
		switch ($type) {
			case 'html':

			case 'htm':

			case 'tmpl':
				$mode = self::MODE_HTML;
				break;
			case 'js':
				$mode = self::MODE_JAVASCRIPT;
				break;
			case 'xml':

			case 'svg':
				$mode = self::MODE_XML;
				break;
			case 'css':
				$mode = self::MODE_CSS;
				break;
			case 'ts':
				$mode = self::MODE_TYPOSCRIPT;
				break;
			case 'sparql':
				$mode = self::MODE_SPARQL;
				break;
			case 'php':

			case 'phpsh':

			case 'inc':
				$mode = self::MODE_PHP;
				break;
			default:
				$mode = self::MODE_MIXED;
		}
		$this->setMode($mode);
	}

	/**
	 * Get mode
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}

	/**
	 * @return bool TRUE if the t3editor is enabled
	 * @deprecated since TYPO3 CMS 7, will be removed in TYPO3 CMS 8
	 */
	public function isEnabled() {
		GeneralUtility::logDeprecatedFunction();
		return TRUE;
	}

	/**
	 * Creates a new instance of the class
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:t3editor/locallang.xlf');
		// Disable pmktextarea to avoid conflicts (thanks Peter Klein for this suggestion)
		$GLOBALS['BE_USER']->uc['disablePMKTextarea'] = 1;
	}

	/**
	 * Retrieves JavaScript code (header part) for editor
	 *
	 * @param \TYPO3\CMS\Backend\Template\DocumentTemplate $doc
	 * @return string JavaScript code
	 */
	public function getJavascriptCode($doc) {
		$content = '';
		$path_t3e = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3editor');
		$path_codemirror = 'contrib/codemirror/js/';
		// Include needed javascript-frameworks
		$pageRenderer = $this->getPageRenderer();
		$pageRenderer->loadPrototype();
		$pageRenderer->loadScriptaculous();
		// Include editor-css
		$content .= '<link href="' . GeneralUtility::createVersionNumberedFilename(($GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3editor') . 'res/css/t3editor.css')) . '" type="text/css" rel="stylesheet" />';
		// Include editor-js-lib
		$doc->loadJavascriptLib($path_codemirror . 'codemirror.js');
		$doc->loadJavascriptLib($path_t3e . 'res/jslib/t3editor.js');
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/T3editor/T3editor');

		$content .= GeneralUtility::wrapJS(
			'T3editor = T3editor || {};' .
			'T3editor.lang = ' . json_encode($GLOBALS['LANG']->getLabelsWithPrefix('js.', 'label_')) . ';' . LF .
			'T3editor.PATH_t3e = "' . $GLOBALS['BACK_PATH'] . $path_t3e . '"; ' . LF .
			'T3editor.PATH_codemirror = "' . $GLOBALS['BACK_PATH'] . $path_codemirror . '"; ' . LF .
			'T3editor.template = ' . $this->getPreparedTemplate() . ';' . LF .
			'T3editor.ajaxSavetype = "' . $this->ajaxSaveType . '";' . LF
		);
		$content .= $this->getModeSpecificJavascriptCode();
		return $content;
	}

	/**
	 * Get mode specific JavaScript code
	 *
	 * @return string
	 */
	public function getModeSpecificJavascriptCode() {
		if (empty($this->mode)) {
			return '';
		}
		$path_t3e = $GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3editor');
		$content = '';
		if ($this->mode === self::MODE_TYPOSCRIPT) {
			$content .= '<script type="text/javascript" src="' . $path_t3e . 'res/jslib/ts_codecompletion/tsref.js' . '"></script>';
			$content .= '<script type="text/javascript" src="' . $path_t3e . 'res/jslib/ts_codecompletion/completionresult.js' . '"></script>';
			$content .= '<script type="text/javascript" src="' . $path_t3e . 'res/jslib/ts_codecompletion/tsparser.js' . '"></script>';
			$content .= '<script type="text/javascript" src="' . $path_t3e . 'res/jslib/ts_codecompletion/tscodecompletion.js' . '"></script>';
		}
		$content .= GeneralUtility::wrapJS('T3editor.parserfile = ' . $this->getParserfileByMode($this->mode) . ';' . LF . 'T3editor.stylesheet = ' . $this->getStylesheetByMode($this->mode) . ';');
		return $content;
	}

	/**
	 * Get the template code, prepared for javascript (no line breaks, quoted in single quotes)
	 *
	 * @return string The template code, prepared to use in javascript
	 */
	protected function getPreparedTemplate() {
		$T3editor_template = GeneralUtility::getUrl(GeneralUtility::getFileAbsFileName('EXT:t3editor/res/templates/t3editor.html'));
		$T3editor_template = addslashes($T3editor_template);
		$T3editor_template = str_replace(array(CR, LF), array('', '\' + \''), $T3editor_template);
		return '\'' . $T3editor_template . '\'';
	}

	/**
	 * Determine the correct parser js file for given mode
	 *
	 * @param string $mode
	 * @return string Parser file name
	 */
	protected function getParserfileByMode($mode) {
		switch ($mode) {
			case self::MODE_TYPOSCRIPT:
				$relPath = ($GLOBALS['BACK_PATH'] ? $GLOBALS['BACK_PATH'] : '../../../') . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3editor') . 'res/jslib/parse_typoscript/';
				$parserfile = '["' . $relPath . 'tokenizetyposcript.js", "' . $relPath . 'parsetyposcript.js"]';
				break;
			case self::MODE_JAVASCRIPT:
				$parserfile = '["tokenizejavascript.js", "parsejavascript.js"]';
				break;
			case self::MODE_CSS:
				$parserfile = '"parsecss.js"';
				break;
			case self::MODE_XML:
				$parserfile = '"parsexml.js"';
				break;
			case self::MODE_SPARQL:
				$parserfile = '"parsesparql.js"';
				break;
			case self::MODE_HTML:
				$parserfile = '["tokenizejavascript.js", "parsejavascript.js", "parsecss.js", "parsexml.js", "parsehtmlmixed.js"]';
				break;
			case self::MODE_PHP:

			case self::MODE_MIXED:
				$parserfile = '[' . '"tokenizejavascript.js", ' . '"parsejavascript.js", ' . '"parsecss.js", ' . '"parsexml.js", ' . '"../contrib/php/js/tokenizephp.js", ' . '"../contrib/php/js/parsephp.js", ' . '"../contrib/php/js/parsephphtmlmixed.js"' . ']';
				break;
		}
		return $parserfile;
	}

	/**
	 * Determine the correct css file for given mode
	 *
	 * @param string $mode
	 * @return string css file name
	 */
	protected function getStylesheetByMode($mode) {
		switch ($mode) {
			case self::MODE_TYPOSCRIPT:
				$stylesheet = 'T3editor.PATH_t3e + "res/css/typoscriptcolors.css"';
				break;
			case self::MODE_JAVASCRIPT:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/jscolors.css"';
				break;
			case self::MODE_CSS:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/csscolors.css"';
				break;
			case self::MODE_XML:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/xmlcolors.css"';
				break;
			case self::MODE_HTML:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/xmlcolors.css", ' . 'T3editor.PATH_codemirror + "../css/jscolors.css", ' . 'T3editor.PATH_codemirror + "../css/csscolors.css"';
				break;
			case self::MODE_SPARQL:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/sparqlcolors.css"';
				break;
			case self::MODE_PHP:
				$stylesheet = 'T3editor.PATH_codemirror + "../contrib/php/css/phpcolors.css"';
				break;
			case self::MODE_MIXED:
				$stylesheet = 'T3editor.PATH_codemirror + "../css/xmlcolors.css", ' . 'T3editor.PATH_codemirror + "../css/jscolors.css", ' . 'T3editor.PATH_codemirror + "../css/csscolors.css", ' . 'T3editor.PATH_codemirror + "../contrib/php/css/phpcolors.css"';
				break;
		}
		if ($stylesheet != '') {
			$stylesheet = '' . $stylesheet . ', ';
		}
		return '[' . $stylesheet . 'T3editor.PATH_t3e + "res/css/t3editor_inner.css"]';
	}

	/**
	 * Generates HTML with code editor
	 *
	 * @param string $name Name attribute of HTML tag
	 * @param string $class Class attribute of HTML tag
	 * @param string $content Content of the editor
	 * @param string $additionalParams Any additional editor parameters
	 * @param string $alt Alt attribute
	 * @param array $hiddenfields
	 * @return string Generated HTML code for editor
	 */
	public function getCodeEditor($name, $class = '', $content = '', $additionalParams = '', $alt = '', array $hiddenfields = array()) {
		$code = '';
		$this->editorCounter++;
		$class .= ' t3editor';
		$alt = htmlspecialchars($alt);
		if (!empty($alt)) {
			$alt = ' alt="' . $alt . '"';
		}
		$code .= '<div>' . '<textarea id="t3editor_' . $this->editorCounter . '" ' . 'name="' . $name . '" ' . 'class="' . $class . '" ' . $additionalParams . ' ' . $alt . '>' . htmlspecialchars($content) . '</textarea></div>';
		$checked = $GLOBALS['BE_USER']->uc['disableT3Editor'] ? 'checked="checked"' : '';
		$code .= '<div class="checkbox"><label for="t3editor_disableEditor_' . $this->editorCounter . '_checkbox"><input type="checkbox" class="checkbox t3editor_disableEditor" onclick="T3editor.toggleEditor(this);" name="t3editor_disableEditor" value="true" id="t3editor_disableEditor_' . $this->editorCounter . '_checkbox" ' . $checked . ' />' . $GLOBALS['LANG']->getLL('deactivate') . '</label></div>';
		if (!empty($hiddenfields)) {
			foreach ($hiddenfields as $name => $value) {
				$code .= '<input type="hidden" ' . 'name="' . $name . '" ' . 'value="' . $value . '" />';
			}
		}
		return $code;
	}

	/**
	 * Save the content from t3editor retrieved via Ajax
	 *
	 * new Ajax.Request('/dev/t3e/dummy/typo3/ajax.php', {
	 * parameters: {
	 * ajaxID: 'T3editor::saveCode',
	 * t3editor_savetype: 'TypoScriptTemplateInformationModuleFunctionController'
	 * }
	 * });
	 *
	 * @param array $params Parameters (not used yet)
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj AjaxRequestHandler to handle response
	 */
	public function ajaxSaveCode($params, $ajaxObj) {
		// cancel if its not an Ajax request
		if (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_AJAX) {
			$ajaxObj->setContentFormat('json');
			$codeType = GeneralUtility::_GP('t3editor_savetype');
			$savingsuccess = FALSE;
			try {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/t3editor/classes/class.tx_t3editor.php']['ajaxSaveCode'])) {
					$_params = array(
						'pObj' => &$this,
						'type' => $codeType,
						'ajaxObj' => &$ajaxObj
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/t3editor/classes/class.tx_t3editor.php']['ajaxSaveCode'] as $key => $_funcRef) {
						$savingsuccess = GeneralUtility::callUserFunction($_funcRef, $_params, $this) || $savingsuccess;
					}
				}
			} catch (\Exception $e) {
				$ajaxObj->setContent(array('result' => FALSE, 'exceptionMessage' => htmlspecialchars($e->getMessage()), 'exceptionCode' => $e->getCode()));
				return;
			}
			$ajaxObj->setContent(array('result' => $savingsuccess));
		}
	}

	/**
	 * Gets plugins that are defined at $TYPO3_CONF_VARS['EXTCONF']['t3editor']['plugins']
	 * (called by typo3/ajax.php)
	 *
	 * @param array $params additional parameters (not used here)
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj The AjaxRequestHandler object of this request
	 * @return void
	 * @author Oliver Hader <oliver@typo3.org>
	 */
	public function getPlugins($params, \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj) {
		$result = array();
		$plugins = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3editor']['plugins'];
		if (is_array($plugins)) {
			$result = array_values($plugins);
		}
		$ajaxObj->setContent($result);
		$ajaxObj->setContentFormat('jsonbody');
	}

	/**
	 * @return PageRenderer
	 */
	protected function getPageRenderer() {
		return GeneralUtility::makeInstance(PageRenderer::class);
	}

}

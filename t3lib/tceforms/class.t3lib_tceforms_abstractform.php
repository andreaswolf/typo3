<?php

require_once(PATH_t3lib.'tceforms/container/class.t3lib_tceforms_container_sheet.php');

// TODO: define abstract render() method
abstract class t3lib_TCEforms_AbstractForm {

	/**
	 * @var string  The fieldname of the save button
	 */
	protected $doSaveFieldName = 'doSave';

	/**
	 * @var string  The render mode. May be any of "mainFields", "soloField" or "listedFields"
	 */
	//protected $renderMode = 'mainFields';

	/**
	 * @var string  The table of the record to render
	 */
	protected $table;
	/**
	 * @var array  The record to render
	 */
	protected $record;

	/**
	 * @var array  shortcut to $GLOBALS['TCA'][$this->table] (reference!)
	 */
	protected $tableTCAconfig;

	protected $typeNumber;

	/**
	 * @var array  The list of items to display
	 */
	protected $itemList;

	/**
	 * @var boolean  Whether or not the palettes (secondary options) are collapsed
	 */
	protected $palettesCollapsed;

	/**
	 * @var array  The array of elements to exclude from rendering
	 * @see setExcludeElements()
	 */
	protected $excludeElements;

	/**
	 * @var boolean  Whether or not to use tabs
	 */
	protected $useTabs;

	/**
	 * @var array  All sheets of this form
	 */
	protected $sheets = array();

	/**
	 * @var t3lib_TCEforms_Sheet  The currently used sheet
	 */
	protected $currentSheet;

	/**
	 * May be safely removed as soon as all dependencies on the old TCEforms are removed!
	 *
	 * @var t3lib_TCEforms
	 */
	protected $TCEformsObject;

	protected $templateFile;
	protected $templateContent;


	protected $additionalCode_pre = array();			// Additional HTML code, printed before the form.
	protected $additionalJS_pre = array();			// Additional JavaScript, printed before the form
	protected $additionalJS_post = array();			// Additional JavaScript printed after the form
	protected $additionalJS_submit = array();			// Additional JavaScript executed on submit; If you set "OK" variable it will raise an error about RTEs not being loaded and offer to block further submission.

	/**
	 * @var array  The JavaScript code to add to various parts of the form. Contains e.g. the following
	 *             keys: evaluation, submit, pre (before the form), post (after the form)
	 */
	protected $JScode;

	protected $formName;
	protected $prependFormFieldNames;

	protected $hiddenFields = array();

	/**
	 * @var array  HTML code rendered for fields which are marked as hidden. Previously called hiddenFieldAccum
	 */
	protected $hiddenFields_HTMLcode = array();

	protected $backPath;

	protected $fieldList = array();
	protected $fieldsToList = array();

	/**
	 * @var string  Prefix for all form fields in this form. Usually starts with data[...]
	 */
	protected $formFieldNamePrefix;

	/**
	 * @var t3lib_TCEforms_AbstractForm  The form object containing this form
	 */
	protected $parentFormObject;


	protected static $cachedTSconfig;



	/**
	 * The constructor of this class
	 *
	 * @param  string   $table
	 * @param  array    $row
	 * @param  integer  $typeNumber
	 */
	public function __construct($table, $row) {
		global $TCA;

		$this->setTemplateFile(PATH_typo3 . 'templates/tceforms.html');


			// TODO: Refactor this!
		$this->prependFormFieldNames = 'data';
		$this->formName = 'editform';
		//$this->setNewBEDesign();
		$this->docLarge = $GLOBALS['BE_USER']->uc['edit_wideDocument'] ? 1 : 0;
		$this->edit_showFieldHelp = $GLOBALS['BE_USER']->uc['edit_showFieldHelp'];

		$this->edit_docModuleUpload = $GLOBALS['BE_USER']->uc['edit_docModuleUpload'];
		$this->titleLen = $GLOBALS['BE_USER']->uc['titleLen'];		// @deprecated

		//$this->inline->init($this);


		$this->requiredFields = array();


			// check if table exists in TCA. This is required!
		if (!$TCA[$table]) {
			// TODO: throw exception here

			die('Table '.$table.'does not exist! [1216891229]');
		}

			// set table and row
		$this->table = $table;
		$this->record = $row;

		// TODO: load TCA here
		$this->tableTCAconfig = &$TCA[$this->table];


		// Load the description content for the table.
		if ($this->edit_showFieldHelp || $this->TCEformsObject->doLoadTableDescr($table)) {
			$GLOBALS['LANG']->loadSingleTableDescription($table);
		}

			// set type number
		$this->setRecordTypeNumber();

		/*if ($this->typeNumber === '') {
			// TODO: throw exception here
			die('typeNumber '.$this->typeNumber.' does not exist for table '.$this->table.' [1216891550]');
		}*/



		$this->setExcludeElements();

	}

	public function setParentFormObject(t3lib_TCEforms_AbstractForm $formObject) {
		$this->parentFormObject = $formObject;

		return $this;
	}

	/**
	 * Finds possible field to add to the form, based on subtype fields.
	 *
	 * @return  array  An array containing two values: 1) Another array containing fieldnames to add and 2) the subtype value field.
	 * @see getMainFields()
	 */
	protected function getFieldsToAdd() {
		global $TCA;

			// Init:
		$addElements = array();

			// If a subtype field is defined for the type
		if ($this->tableTCAconfig['types'][$this->typeNumber]['subtype_value_field']) {
			$subtypeField = $this->tableTCAconfig['types'][$this->typeNumber]['subtype_value_field'];
			if (trim($this->tableTCAconfig['types'][$this->typeNumber]['subtypes_addlist'][$this->record[$subtypeField]])) {
				$addElements = t3lib_div::trimExplode(',', $this->tableTCAconfig['types'][$this->typeNumber]['subtypes_addlist'][$this->record[$subtypeField]], 1);
			}
		}

			// Return the array
		return array($addElements, $subtypeField);
	}

	/**
	 * Merges the current [types][showitem] array with the array of fields to add for the current subtype field of the "type" value.
	 *
	 * @param   array  A [types][showitem] list of fields, exploded by ","
	 * @param   array  The output from getFieldsToAdd()
	 * @return  array  Return the modified $fields array.
	 * @see getMainFields(),getFieldsToAdd()
	 */
	protected function mergeFieldsWithAddedFields($fields, $fieldsToAdd) {
		if (count($fieldsToAdd[0])) {
			$c = 0;
			reset($fields);
			foreach ($fields as $fieldInfo) {
				$parts = explode(';', $fieldInfo);
				if (!strcmp(trim($parts[0]), $fieldsToAdd[1])) {
					array_splice(
						$fields,
						$c+1,
						0,
						$fieldsToAdd[0]
					);
					break;
				}
				$c++;
			}
		}
		return $fields;
	}

	/**
	 * Returns the object representation for a database table field.
	 *
	 * @param   string   $field    The field name
	 * @param   string   $altName  Alternative field name label to show.
	 * @param   boolean  $palette  Set this if the field is on a palette (in top frame), otherwise not. (if set, field will render as a hidden field).
	 * @param   string   $extra    The "extra" options from "Part 4" of the field configurations found in the "types" "showitem" list. Typically parsed by $this->getSpecConfFromString() in order to get the options as an associative array.
	 * @param   integer  $pal      The palette pointer.
	 * @param   string   $formFieldName  The name of the field on the form
	 * @return  t3lib_TCEforms_AbstractElement
	 */
	// TODO: remove the extra parameters/use them if neccessary
	function getSingleField($theField, $altName='', $palette=0, $extra='', $pal=0, $formFieldName = '') {
		$fieldConf = $this->tableTCAconfig['columns'][$theField];

		// Using "form_type" locally in this script
		$fieldConf['config']['form_type'] = $fieldConf['config']['form_type'] ? $fieldConf['config']['form_type'] : $fieldConf['config']['type'];

		$elementClassname = $this->elementObjectFactory($fieldConf['config']['form_type']);
		//$this->table, $this->record
		$elementObject = new $elementClassname($theField, $fieldConf, $altName, $extra, $this);
		$elementObject->setTable($this->table)
		              ->setRecord($this->record)
			// don't set the container here because we can't be sure if this item
			// will be attached to $this->currentSheet or another sheet
		              ->setTCEformsObject($this->TCEformsObject)
		              ->set_TCEformsObject($this);
		if (is_array($this->defaultLanguageData)) {
			$elementObject->setDefaultLanguageValue($this->defaultLanguageData[$theField]);
		}

		// TODO: don't call init here, call it in the container after the element has been added to it
		$elementObject->init();


		return $elementObject;
	}

	public function getPaletteField($field) {

	}

	/**
	 * Factory method for form element objects. Defaults to type "unknown" if the class(file)
	 * is not found.
	 *
	 * @param  string  $type  The type of record to create - directly taken from TCA
	 * @return t3lib_TCEforms_AbstractElement  The element object
	 */
	// TODO: refactor this as soon as the autoloader is available in core
	protected function elementObjectFactory($type) {
		switch ($type) {
			default:
				$className = 't3lib_TCEforms_Element_'.$type;
				break;
		}

		if (!class_exists($className)) {
				// if class(file) does not exist, resolve to type "unknown"
			if (!@file_exists(PATH_t3lib.'tceforms/element/class.'.strtolower($className).'.php')) {
				return $this->elementObjectFactory('unknown');
			}
			include_once PATH_t3lib.'tceforms/element/class.'.strtolower($className).'.php';
		}

		return t3lib_div::makeInstanceClassName($className);
	}

	/**
	 * Returns if a given element is among the elements set via setExcludeElements(), i.e.
	 * not displayed in the form
	 *
	 * @param  string  $elementName  The name of the element to check
	 * @return boolean
	 */
	public function isExcludeElement($elementName) {
		return t3lib_div::inArray($this->excludeElements, $elementName);
	}

	/**
	 * Returns the full array of elements which are excluded and thus not displayed on the form
	 *
	 * @return array
	 */
	public function getExcludeElements() {
		return $this->excludeElements;
	}

	/**
	 * Producing an array of field names NOT to display in the form, based on settings
	 * from subtype_value_field, bitmask_excludelist_bits etc.
	 *
	 * NOTICE: This list is in NO way related to the "excludeField" flag
	 *
	 * Sets $this->excludeElements to an array with fieldnames as values. The fieldnames are
	 * those which should NOT be displayed "anyways"
	 *
	 * @return void
	 */
	protected function setExcludeElements() {
		global $TCA;

			// Init:
		$this->excludeElements = array();

			// If a subtype field is defined for the type
		if ($this->tableTCAconfig['types'][$this->typeNumber]['subtype_value_field']) {
			$subtypeField = $this->tableTCAconfig['types'][$this->typeNumber]['subtype_value_field'];
			if (trim($this->tableTCAconfig['types'][$this->typeNumber]['subtypes_excludelist'][$this->record[$subtypeField]])) {
				$this->excludeElements=t3lib_div::trimExplode(',',$this->tableTCAconfig['types'][$this->typeNumber]['subtypes_excludelist'][$this->record[$subtypeField]],1);
			}
		}

			// If a bitmask-value field has been configured, then find possible fields to exclude based on that:
		if ($this->tableTCAconfig['types'][$this->typeNumber]['bitmask_value_field']) {
			$subtypeField = $this->tableTCAconfig['types'][$this->typeNumber]['bitmask_value_field'];
			$subtypeValue = t3lib_div::intInRange($this->record[$subtypeField],0);
			if (is_array($this->tableTCAconfig['types'][$this->typeNumber]['bitmask_excludelist_bits'])) {
				reset($this->tableTCAconfig['types'][$this->typeNumber]['bitmask_excludelist_bits']);
				while(list($bitKey,$eList)=each($this->tableTCAconfig['types'][$this->typeNumber]['bitmask_excludelist_bits'])) {
					$bit=substr($bitKey,1);
					if (t3lib_div::testInt($bit)) {
						$bit = t3lib_div::intInRange($bit,0,30);
						if (
								(substr($bitKey,0,1)=='-' && !($subtypeValue&pow(2,$bit))) ||
								(substr($bitKey,0,1)=='+' && ($subtypeValue&pow(2,$bit)))
							) {
							$this->excludeElements = array_merge($this->excludeElements,t3lib_div::trimExplode(',',$eList,1));
						}
					}
				}
			}
		}
	}

	/**
	 * Returns the "special" configuration of an "extra" string (non-parsed)
	 *
	 * @param  string  The "Part 4" of the fields configuration in "types" "showitem" lists.
	 * @param  string  The ['defaultExtras'] value from field configuration
	 * @return array   An array with the special options in.
	 * @see getSpecConfForField(), t3lib_BEfunc::getSpecConfParts()
	 */
	function getSpecConfFromString($extraString, $defaultExtras)    {
		return t3lib_BEfunc::getSpecConfParts($extraString, $defaultExtras);
	}

	/**
	 * Sets an field's status to "hidden", thus not displaying it visibly on the form
	 *
	 * @param mixed  $fieldName  The fieldname to exclude. May also be an array of fieldnames.
	 */
	public function setHiddenField($fieldName) {
		if (is_array($fieldName)) {
			$this->hiddenFields = t3lib_div::array_merge($this->hiddenFields, $fieldName);
		} else {
			$this->hiddenFields[] = $fieldName;
		}
	}

	/**
	 * Adds HTML code for a hidden form field to the form
	 *
	 * WARNING: You may only add code for a field once. The second time you try to "add" code
	 *          for this field, the first code will be overwritten!
	 *
	 * @param string  $formFieldName  The fieldname for which the HTML is added.
	 * @param string  $code  The complete HTML to render for the field
	 */
	public function addHiddenFieldHTMLCode($formFieldName, $code) {
		$this->hiddenFields_HTMLcode[$formFieldName] = $code;
	}

	/**
	 * Calculate the current "types" pointer value for the record this form is instantiated for
	 *
	 * Sets $this->typeNumber to the types pointer value.
	 *
	 * @return void
	 */
	protected function setRecordTypeNumber() {
		global $TCA;

			// If there is a "type" field configured...
		if ($this->tableTCAconfig['ctrl']['type']) {
			$typeFieldName = $this->tableTCAconfig['ctrl']['type'];
			$this->typeNumber=$this->record[$typeFieldName];	// Get value of the row from the record which contains the type value.
			if (!strcmp($this->typeNumber,''))	$this->typeNumber = 0;			// If that value is an empty string, set it to "0" (zero)
		} else {
			$this->typeNumber = 0;	// If no "type" field, then set to "0" (zero)
		}

		$this->typeNumber = (string)$this->typeNumber;		// Force to string. Necessary for eg '-1' to be recognized as a type value.
		if (!$this->tableTCAconfig['types'][$this->typeNumber]) {	// However, if the type "0" is not found in the "types" array, then default to "1" (for historical reasons)
			$this->typeNumber = 1;
		}
	}

	/**
	 * Generic setter function
	 *
	 * @param string  $key    The var name to (over)write
	 * @param mixed   $value  The value to write to the variable
	 */
	public function __set($key, $value) {
			// DANGEROUS, may be used to overwrite *EVERYTHING* in this class! Should be secured later on
		$this->$key = $value;
	}

	/**
	 * Generic getter function, will be called by PHP if the given var is protected/private or does
	 * not exist at all. The latter case will fail at the moment, as the function does no mapping of
	 * non-existing keys at the moment.
	 *
	 * @param  string  $key  The name of the variable to return
	 * @return array
	 */
	public function __get($key) {
		// TODO: implement access check based on whitelist
		return $this->$key;
	}

	/**
	 * Factory method for sheet objects on forms.
	 *
	 * @param   string  $sheetIdentString  The identifier of the sheet. Must be unique for the whole form
	 *                                     (and all sub-forms!)
	 * @param   string  $header  The name of the sheet (e.g. displayed as the title in tabs
	 * @return  t3lib_TCEforms_Sheet
	 */
	protected function createSheetObject($sheetIdentString, $header) {
		$sheetObject = new t3lib_TCEforms_Sheet($sheetIdentString, $header);
		$sheetObject->setParentObject($this)
		            ->init();

		$this->sheets[] = $sheetObject;

		return $sheetObject;
	}

	/**
	 * Fetches language label for key
	 *
	 * @param   string  Language label reference, eg. 'LLL:EXT:lang/locallang_core.php:labels.blablabla'
	 * @return  string  The value of the label, fetched for the current backend language.
	 */
	// TODO: refactor the method name
	protected function sL($str) {
		return $GLOBALS['LANG']->sL($str);
	}

	/**
	 * Returns language label from locallang_core.php
	 * Labels must be prefixed with either "l_" or "m_".
	 * The prefix "l_" maps to the prefix "labels." inside locallang_core.php
	 * The prefix "m_" maps to the prefix "mess." inside locallang_core.php
	 *
	 * @param   string  The label key
	 * @return  string  The value of the label, fetched for the current backend language.
	 */
	protected function getLL($str) {
		$content = '';

		switch(substr($str, 0, 2)) {
			case 'l_':
				$content = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.' . substr($str,2));
			break;
			case 'm_':
				$content = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:mess.' . substr($str,2));
			break;
		}
		return $content;
	}

	/**
	 * Sets the (old) TCEforms-object used by this form.
	 *
	 * @param  t3lib_TCEforms $TCEformsObject
	 * @deprecated  since 4.2
	 */
	public function setTCEformsObject(t3lib_TCEforms $TCEformsObject) {
		$this->TCEformsObject = $TCEformsObject;
	}

	/**
	 * Sets the global status of all palettes to collapsed/uncollapsed
	 *
	 * @param  boolean  $collapsed
	 */
	public function setPalettesCollapsed($collapsed) {
		$this->palettesCollapsed = (bool)$collapsed;
	}

	/**
	 * Returns whether or not the palettes are collapsed
	 *
	 * @return boolean
	 */
	public function getPalettesCollapsed() {
		return $this->palettesCollapsed;
	}



	/********************************************
	 *
	 * Template functions
	 *
	 ********************************************/

	/**
	 * Sets the path to the template file. Also automatically loads the contents of this file.
	 * It may be accessed via getTemplateContent()
	 *
	 * @param  string  $filePath
	 */
	public function setTemplateFile($filePath) {
		$filePath = t3lib_div::getFileAbsFileName($filePath);

		if (!@file_exists($filePath)) {
			die('Template file <em>'.$filePath.'</em> does not exist. [1216911730]');
		}

		$this->templateContent = file_get_contents($filePath);
	}

	public function getTemplateContent() {
		return $this->templateContent;
	}

	/**
	 * Create dynamic tab menu
	 *
	 * @param	array		Parts for the tab menu, fed to template::getDynTabMenu()
	 * @param	string		ID string for the tab menu
	 * @param	integer		If set to '1' empty tabs will be removed, If set to '2' empty tabs will be disabled
	 * @return	string		HTML for the menu
	 */
	protected function getDynTabMenu($parts, $idString, $dividersToTabsBehaviour = 1) {
		if (is_object($GLOBALS['TBE_TEMPLATE'])) {
			return $GLOBALS['TBE_TEMPLATE']->getDynTabMenu($parts, $idString, 0, false, 50, 1, false, 1, $dividersToTabsBehaviour);
		} else {
			$output = '';
			foreach($parts as $singlePad) {
				$output .= '
				<h3>' . htmlspecialchars($singlePad['label']) . '</h3>
				' . ($singlePad['description'] ? '<p class="c-descr">' . nl2br(htmlspecialchars($singlePad['description'])) . '</p>' : '') . '
				' . $singlePad['content'];
			}

			return '<div class="typo3-dyntabmenu-divs">' . $output . '</div>';
		}
	}

	/**
	 * Wraps all the table rows into a single table.
	 * Used externally from scripts like alt_doc.php and db_layout.php (which uses TCEforms...)
	 *
	 * @param	string		Code to output between table-parts; table rows
	 * @return	string
	 */
	// TODO: refactorthe next two methods
	protected function wrapTotal($content) {
		$wrap = t3lib_parsehtml::getSubpart($this->templateContent, '###TOTAL_WRAP###');
		$content = $this->replaceTableWrap($wrap, $content);
		return $content . implode('', $this->hiddenFields_HTMLcode);
	}

	/**
	 * This replaces markers in the total wrap
	 *
	 * @param   array    An array of template parts containing some markers.
	 * @param   array    The record
	 * @param   string   The table name
	 * @return  string
	 */
	protected function replaceTableWrap($wrap, $content) {
		global $TCA;

			// Make "new"-label
		if (strstr($this->record['uid'],'NEW')) {
			$newLabel = ' <span class="typo3-TCEforms-newToken">'.
						$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.new',1).
						'</span>';

			#t3lib_BEfunc::fixVersioningPid($this->table,$this->record);	// Kasper: Should not be used here because NEW records are not offline workspace versions...
			$truePid = t3lib_BEfunc::getTSconfig_pidValue($this->table,$this->record['uid'],$this->record['pid']);
			$prec = t3lib_BEfunc::getRecordWSOL('pages',$truePid,'title');
			$rLabel = '<em>[PID: '.$truePid.'] '.htmlspecialchars(trim(t3lib_div::fixed_lgd_cs(t3lib_BEfunc::getRecordTitle('pages',$prec),40))).'</em>';
		} else {
			$newLabel = ' <span class="typo3-TCEforms-recUid">['.$this->record['uid'].']</span>';
			$rLabel  = htmlspecialchars(trim(t3lib_div::fixed_lgd_cs(t3lib_BEfunc::getRecordTitle($this->table,$this->record),40)));
		}

		$titleA = t3lib_BEfunc::titleAltAttrib($this->TCEformsObject->getRecordPath($this->table,$this->record));

		$markerArray = array(
			'###ID_NEW_INDICATOR###' => $newLabel,
			'###RECORD_LABEL###' => $rLabel,
			'###TABLE_TITLE###' => htmlspecialchars($this->sL($TCA[$this->table]['ctrl']['title'])),

			'###RECORD_ICON###' => t3lib_iconWorks::getIconImage($this->table,$this->record,$this->backPath,'class="absmiddle"'.$titleA),
			'###WRAP_CONTENT###' => $content
		);

		$wrap = t3lib_parsehtml::substituteMarkerArray($wrap, $markerArray);
		return $wrap;
	}

	/********************************************
	 *
	 * JavaScript related functions
	 *
	 ********************************************/

	/**
	 * Adds JavaScript code for form field evaluation. Used to be the global var <extJSCode in old t3lib_TCEforms
	 *
	 * @param string $JScode
	 */
	public function addToEvaluationJS($JScode) {
		$this->JScode['evaluation'] .= $JScode;
	}

	/**
	 * JavaScript code added BEFORE the form is drawn:
	 *
	 * @return	string		A <script></script> section with JavaScript.
	 */
	function JStop() {

		$out = '';

			// Additional top HTML:
		if (count($this->additionalCode_pre)) {
			$out.= implode('

				<!-- NEXT: -->
			',$this->additionalCode_pre);
		}

			// Additional top JavaScript
		if (count($this->additionalJS_pre)) {
			$out.='


		<!--
			JavaScript in top of page (before form):
		-->

		<script type="text/javascript">
			/*<![CDATA[*/

			'.implode('

				// NEXT:
			',$this->additionalJS_pre).'

			/*]]>*/
		</script>
			';
		}

			// Return result:
		return $out;
	}

	/**
	 * JavaScript code used for input-field evaluation.
	 *
	 * 		Example use:
	 *
	 * 		$msg.='Distribution time (hh:mm dd-mm-yy):<br /><input type="text" name="send_mail_datetime_hr" onchange="typo3form.fieldGet(\'send_mail_datetime\', \'datetime\', \'\', 0,0);"'.$GLOBALS['TBE_TEMPLATE']->formWidth(20).' /><input type="hidden" value="'.time().'" name="send_mail_datetime" /><br />';
	 * 		$this->extJSCODE.='typo3form.fieldSet("send_mail_datetime", "datetime", "", 0,0);';
	 *
	 * 		... and then include the result of this function after the form
	 *
	 * @param	string		$formname: The identification of the form on the page.
	 * @param	boolean		$update: Just extend/update existing settings, e.g. for AJAX call
	 * @return	string		A section with JavaScript - if $update is false, embedded in <script></script>
	 */
	function JSbottom($formname='forms[0]', $update = false) {
		$jsFile = array();
		$elements = array();

			// required:
		foreach ($this->sheets as $sheet) {
			foreach ($sheet->getRequiredFields() as $itemImgName => $itemName) {
				$match = array();
				if (preg_match('/^(.+)\[((\w|\d|_)+)\]$/', $itemName, $match)) {
					$record = $match[1];
					$field = $match[2];
					$elements[$record][$field]['required'] = 1;
					$elements[$record][$field]['requiredImg'] = $itemImgName;
					if (isset($this->requiredAdditional[$itemName]) && is_array($this->requiredAdditional[$itemName])) {
						$elements[$record][$field]['additional'] = $this->requiredAdditional[$itemName];
					}
				}
			}
				// range:
			foreach ($sheet->getRequiredElements() as $itemName => $range) {
				if (preg_match('/^(.+)\[((\w|\d|_)+)\]$/', $itemName, $match)) {
					$record = $match[1];
					$field = $match[2];
					$elements[$record][$field]['range'] = array($range[0], $range[1]);
					$elements[$record][$field]['rangeImg'] = $range['imgName'];
				}
			}
		}

		$this->TBE_EDITOR_fieldChanged_func='TBE_EDITOR.fieldChanged_fName(fName,formObj[fName+"_list"]);';

		if (!$update) {
			if ($this->loadMD5_JS) {
				$jsFile[] =	'<script type="text/javascript" src="'.$this->backPath.'md5.js"></script>';
			}

			$jsFile[] = '<script type="text/javascript" src="'.$this->backPath.'contrib/prototype/prototype.js"></script>';
			$jsFile[] = '<script type="text/javascript" src="'.$this->backPath.'contrib/scriptaculous/scriptaculous.js"></script>';
			$jsFile[] =	'<script type="text/javascript" src="'.$this->backPath.'../t3lib/jsfunc.evalfield.js"></script>';
			$jsFile[] =	'<script type="text/javascript" src="'.$this->backPath.'jsfunc.tbe_editor.js"></script>';
			$jsFile[] =	'<script type="text/javascript" src="'.$this->backPath.'js/tceforms.js"></script>';

				// if IRRE fields were processed, add the JavaScript functions:
			if ($this->inline->inlineCount) {
				$jsFile[] = '<script src="'.$this->backPath.'contrib/scriptaculous/scriptaculous.js" type="text/javascript"></script>';
				$jsFile[] = '<script src="'.$this->backPath.'../t3lib/jsfunc.inline.js" type="text/javascript"></script>';
				$out .= '
				inline.setPrependFormFieldNames("'.$this->inline->prependNaming.'");
				inline.setNoTitleString("'.addslashes(t3lib_BEfunc::getNoRecordTitle(true)).'");
				';
			}

				// Toggle icons:
			$toggleIcon_open = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/pil2down.gif','width="12" height="7"').' hspace="2" alt="Open" title="Open" />';
			$toggleIcon_close = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/pil2right.gif','width="7" height="12"').' hspace="2" alt="Close" title="Close" />';

			$out .= '
			var toggleIcon_open = \''.$toggleIcon_open.'\';
			var toggleIcon_close = \''.$toggleIcon_close.'\';

			TBE_EDITOR.images.req.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/required_h.gif','',1).'";
			TBE_EDITOR.images.cm.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/content_client.gif','',1).'";
			TBE_EDITOR.images.sel.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/content_selected.gif','',1).'";
			TBE_EDITOR.images.clear.src = "'.$this->backPath.'clear.gif";

			TBE_EDITOR.auth_timeout_field = '.intval($GLOBALS['BE_USER']->auth_timeout_field).';
			TBE_EDITOR.formname = "'.$formname.'";
			TBE_EDITOR.formnameUENC = "'.rawurlencode($formname).'";
			TBE_EDITOR.backPath = "'.addslashes($this->backPath).'";
			TBE_EDITOR.prependFormFieldNames = "'.$this->prependFormFieldNames.'";
			TBE_EDITOR.prependFormFieldNamesUENC = "'.rawurlencode($this->prependFormFieldNames).'";
			TBE_EDITOR.prependFormFieldNamesCnt = '.substr_count($this->prependFormFieldNames,'[').';
			TBE_EDITOR.isPalettedoc = '.($this->isPalettedoc ? addslashes($this->isPalettedoc) : 'null').';
			TBE_EDITOR.doSaveFieldName = "'.($this->doSaveFieldName ? addslashes($this->doSaveFieldName) : '').'";
			TBE_EDITOR.labels.fieldsChanged = '.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.fieldsChanged')).';
			TBE_EDITOR.labels.fieldsMissing = '.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.fieldsMissing')).';
			TBE_EDITOR.labels.refresh_login = '.$GLOBALS['LANG']->JScharCode($this->getLL('m_refresh_login')).';
			TBE_EDITOR.labels.onChangeAlert = '.$GLOBALS['LANG']->JScharCode($this->getLL('m_onChangeAlert')).';
			evalFunc.USmode = '.($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat']?'1':'0').';
			';
		}

			// add JS required for inline fields
		if (count($this->inline->inlineData)) {
			$out .=	'
			inline.addToDataArray('.t3lib_div::array2json($this->inline->inlineData).');
			';
		}
			// Registered nested elements for tabs or inline levels:
		if (count($this->requiredNested)) {
			$out .= '
			TBE_EDITOR.addNested('.t3lib_div::array2json($this->requiredNested).');
			';
		}
			// elements which are required or have a range definition:
		if (count($elements)) {
			$out .= '
			TBE_EDITOR.addElements('.t3lib_div::array2json($elements).');
			TBE_EDITOR.initRequired();
			';
		}
			// $this->additionalJS_submit:
		if ($this->additionalJS_submit) {
			$additionalJS_submit = implode('', $this->additionalJS_submit);
			$additionalJS_submit = str_replace("\r", '', $additionalJS_submit);
			$additionalJS_submit = str_replace("\n", '', $additionalJS_submit);
			$out .= '
			TBE_EDITOR.addActionChecks("submit", "'.addslashes($additionalJS_submit).'");
			';
		}

		$out .= chr(10).implode(chr(10),$this->additionalJS_post).chr(10).$this->JScode['evaluation'];
		$out .= '
			TBE_EDITOR.loginRefreshed();
		';

			// Regular direct output:
		if (!$update) {
			$spacer = chr(10).chr(9);
			$out  = $spacer.implode($spacer, $jsFile).t3lib_div::wrapJS($out);
		}

		return $out;
	}

	/**
	 * Prints necessary JavaScript for TCEforms (after the form HTML).
	 *
	 * @return  string  The JavaScript code
	 */
	public function getBottomJavaScript() {
		$javascript = $this->JSbottom($this->formName).'


			<!--
			 	JavaScript after the form has been drawn:
			-->

			<script type="text/javascript">
				/*<![CDATA[*/

				formObj = document.forms[0]
				backPath = "'.$this->backPath.'";

				function TBE_EDITOR_fieldChanged_func(fName, formObj) {
					'.$this->TCEformsObject->TBE_EDITOR_fieldChanged_func.'
				}

				/*]]>*/
			</script>';

		return $javascript;
	}

	/**
	 * Returns necessary JavaScript for the top
	 *
	 * @return	void
	 */
	function printNeededJSFunctions_top() {
			// JS evaluation:
		$out = $this->JStop($this->formName);
		return $out;
	}


	/************************************************************
	 *
	 * Display of localized content etc.
	 *
	 ************************************************************/

	/**
	 * Will register data from original language records if the current record is a translation of another.
	 * The original data is shown with the edited record in the form. The information also includes possibly diff-views of what changed in the original record.
	 * Function called from outside (see alt_doc.php + quick edit) before rendering a form for a record
	 *
	 * @param	string		Table name of the record being edited
	 * @param	array		Record array of the record being edited
	 * @return	void
	 */
	function registerDefaultLanguageData()	{
			// Add default language:
		if ($this->tableTCAconfig['ctrl']['languageField']
				&& $this->record[$this->tableTCAconfig['ctrl']['languageField']] > 0
				&& $this->tableTCAconfig['ctrl']['transOrigPointerField']
				&& intval($this->record[$this->tableTCAconfig['ctrl']['transOrigPointerField']]) > 0) {

			$lookUpTable = $this->tableTCAconfig['ctrl']['transOrigPointerTable'] ? $this->tableTCAconfig['ctrl']['transOrigPointerTable'] : $this->table;

				// Get data formatted:
			$this->defaultLanguageData = t3lib_BEfunc::getRecordWSOL($lookUpTable, intval($this->record[$this->tableTCAconfig['ctrl']['transOrigPointerField']]));

				// Get data for diff:
			if ($this->tableTCAconfig['ctrl']['transOrigDiffSourceField'])	{
				$this->defaultLanguageData_diff = unserialize($this->record[$this->tableTCAconfig['ctrl']['transOrigDiffSourceField']]);
			}

				// If there are additional preview languages, load information for them also:
			$prLang = $this->getAdditionalPreviewLanguages();
			foreach($prLang as $prL) {
				$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
				$tInfo = $t8Tools->translationInfo($lookUpTable,intval($this->record[$this->tableTCAconfig['ctrl']['transOrigPointerField']]),$prL['uid']);
				if (is_array($tInfo['translations'][$prL['uid']]))	{
					$this->additionalPreviewLanguageData[$prL['uid']] = t3lib_BEfunc::getRecordWSOL($this->table, intval($tInfo['translations'][$prL['uid']]['uid']));
				}
			}
		}
	}

	/**
	 * Generates and return information about which languages the current user should see in preview, configured by options.additionalPreviewLanguages
	 *
	 * return array	Array of additional languages to preview
	 */
	function getAdditionalPreviewLanguages()	{
		if (!isset($this->cachedAdditionalPreviewLanguages)) 	{
			if ($GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages'))	{
				$uids = t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages'));
				foreach($uids as $uid)	{
					if ($sys_language_rec = t3lib_BEfunc::getRecord('sys_language',$uid))	{
						$this->cachedAdditionalPreviewLanguages[$uid] = array('uid' => $uid);

						if ($sys_language_rec['static_lang_isocode'] && t3lib_extMgm::isLoaded('static_info_tables'))	{
							$staticLangRow = t3lib_BEfunc::getRecord('static_languages',$sys_language_rec['static_lang_isocode'],'lg_iso_2');
							if ($staticLangRow['lg_iso_2']) {
								$this->cachedAdditionalPreviewLanguages[$uid]['uid'] = $uid;
								$this->cachedAdditionalPreviewLanguages[$uid]['ISOcode'] = $staticLangRow['lg_iso_2'];
							}
						}
					}
				}
			} else {
					// None:
				$this->cachedAdditionalPreviewLanguages = array();
			}
		}
		return $this->cachedAdditionalPreviewLanguages;
	}

	/**
	 * Initializes language icons etc.
	 *
	 * param	string	Table name
	 * param	array	Record
	 * param	string	Sys language uid OR ISO language code prefixed with "v", eg. "vDA"
	 * @return	void
	 */
	function getLanguageIcon($sys_language_uid)	{
		global $TCA,$LANG;

		$mainKey = $this->table.':'.$this->record['uid'];

		if (!isset($this->cachedLanguageFlag[$mainKey]))	{
			t3lib_BEfunc::fixVersioningPid($this->table, $this->record);
			list($tscPID,$thePidValue) = $this->getTSCpid($this->table, $this->record['uid'], $this->record['pid']);

			$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
			$this->cachedLanguageFlag[$mainKey] = $t8Tools->getSystemLanguages($tscPID, $this->backPath);
		}

			// Convert sys_language_uid to sys_language_uid if input was in fact a string (ISO code expected then)
		if (!t3lib_div::testInt($sys_language_uid))	{
			foreach($this->cachedLanguageFlag[$mainKey] as $rUid => $cD)	{
				if ('v'.$cD['ISOcode']===$sys_language_uid)	{
					$sys_language_uid = $rUid;
				}
			}
		}

		return ($this->cachedLanguageFlag[$mainKey][$sys_language_uid]['flagIcon'] ? '<img src="'.$this->cachedLanguageFlag[$mainKey][$sys_language_uid]['flagIcon'].'" class="absmiddle" alt="" />' : ($this->cachedLanguageFlag[$mainKey][$sys_language_uid]['title'] ? '['.$this->cachedLanguageFlag[$mainKey][$sys_language_uid]['title'].']' : '')).'&nbsp;';
	}

	/**
	 * Return TSCpid (cached)
	 * Using t3lib_BEfunc::getTSCpid()
	 *
	 * @param	string		Tablename
	 * @param	string		UID value
	 * @param	string		PID value
	 * @return	integer		Returns the REAL pid of the record, if possible. If both $uid and $pid is strings, then pid=-1 is returned as an error indication.
	 * @see t3lib_BEfunc::getTSCpid()
	 */
	function getTSCpid()	{
		$key = $this->table.':'.$this->record['uid'].':'.$this->record['pid'];
		if (!isset($this->cache_getTSCpid[$key]))	{
			$this->cache_getTSCpid[$key] = t3lib_BEfunc::getTSCpid($this->table, $this->record['uid'], $this->record['pid']);
		}
		return $this->cache_getTSCpid[$key];
	}

	public function getFormFieldNamePrefix() {
		return $this->formFieldNamePrefix;
	}

	public function setFormFieldNamePrefix($prefix) {
		$this->formFieldNamePrefix = $prefix;
	}

	/**
	 * Returns TSconfig for table/row
	 * Multiple requests to this function will return cached content so there is no performance loss in calling this many times since the information is looked up only once.
	 *
	 * @param	string		The table name
	 * @param	array		The table row (Should at least contain the "uid" value, even if "NEW..." string. The "pid" field is important as well, and negative values will be intepreted as pointing to a record from the same table.)
	 * @param	string		Optionally you can specify the field name as well. In that case the TSconfig for the field is returned.
	 * @return	mixed		The TSconfig values (probably in an array)
	 * @see t3lib_BEfunc::getTCEFORM_TSconfig()
	 */
	public static function getTSconfig($table, $row, $field='') {
		$mainKey = $table.':'.$row['uid'];
		if (!isset(self::$cachedTSconfig[$mainKey])) {
			self::$cachedTSconfig[$mainKey] = t3lib_BEfunc::getTCEFORM_TSconfig($table, $row);
		}
		if ($field) {
			return self::$cachedTSconfig[$mainKey][$field];
		} else {
			return self::$cachedTSconfig[$mainKey];
		}
	}
}

?>

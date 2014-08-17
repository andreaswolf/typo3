define('TYPO3/CMS/Rtehtmlarea/TYPO3RTE', [
	'jquery',
	'TYPO3/CMS/Rtehtmlarea/HtmlArea',
	'TYPO3/CMS/Rtehtmlarea/Component/Button',
	'TYPO3/CMS/Rtehtmlarea/Component/Combo',
	'TYPO3/CMS/Rtehtmlarea/Component/Editor',
	'TYPO3/CMS/Rtehtmlarea/Component/Framework',
	'TYPO3/CMS/Rtehtmlarea/Component/Iframe',
	'TYPO3/CMS/Rtehtmlarea/Component/Plugin',
	'TYPO3/CMS/Rtehtmlarea/Component/Statusbar',
	'TYPO3/CMS/Rtehtmlarea/Component/Toolbar',
	'TYPO3/CMS/Rtehtmlarea/Component/ToolbarText',
	'TYPO3/CMS/Rtehtmlarea/Utility/CssParser',
	'TYPO3/CMS/Rtehtmlarea/Utility/Tips'
], function($, HTMLArea) {
// No-op, this is just a shortcut for loading the complete RTE with all its components

	return HTMLArea;
});
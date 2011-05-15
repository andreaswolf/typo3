<?php
/* $Id$ */

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
	'EXT:' . $_EXTKEY . '/cli/fal_cli.php', '_CLI_fal'
);

?>
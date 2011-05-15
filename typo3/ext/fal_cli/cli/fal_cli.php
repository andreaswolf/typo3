<?php
/***************************************************************
*  Copyright notice
*
*  (c)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	if (!isset($_SERVER['argv'][1])) {
		// TODO: output some help message
	}

	if (!isset($_SERVER['argv'][2])) {
		$path = $TYPO3_CONF_VARS['BE']['fileadminDir'];
	} else {
		//$path = PATH_site . $TYPO3_CONF_VARS['BE']['fileadminDir'] . $_SERVER['argv'][2];
		// TODO: Handle cases where partial path is passed into the CLI
	}

	switch ($_SERVER['argv'][1]) {
		case 'index':
			print "Beginning to index " . $path . "\n\n";
			$vfsIndexer = t3lib_div::makeInstance('t3lib_vfs_Indexer');
			$vfsIndexer->indexNodeAtPath($path);
			break;
		case 'ls':
			// TODO: print file & folder list
			break;
	}
}
?>
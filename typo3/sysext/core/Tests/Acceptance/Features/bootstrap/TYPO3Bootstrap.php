<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andreas Wolf <andreas.wolf@typo3.org>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * User acceptance (Behat) testing bootstrap for TYPO3.
 */


define('TYPO3_MODE', 'BE');
define('TYPO3_cliMode', TRUE);

require_once __DIR__ . '/../../../../Classes/Core/Bootstrap.php';

\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
	->baseSetup('bin/')
	->loadConfigurationAndInitialize()
	->loadTypo3LoadedExtAndExtLocalconf(TRUE)
	->applyAdditionalConfigurationSettings()
	->initializeTypo3DbGlobal();

// TODO use another CLI user if possible
array_splice($_SERVER['argv'], 1, 0, array('phpunit'));
\TYPO3\CMS\Core\Core\CliBootstrap::initializeCliKeyOrDie();
array_splice($_SERVER['argv'], 1, 1);

\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
		->loadExtensionTables(TRUE)
		->initializeBackendUser('admin');

// TODO this should be fixed
$GLOBALS['BE_USER']->setBeUserByName('admin');

\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
	->initializeBackendUserMounts()
	->initializeLanguageObject();

// The TYPO3 Core often provokes warnings and notices because of e.g. missing array keys.
// Without this line, these all would lead to an exception and a failing test.
define("BEHAT_ERROR_REPORTING", E_ALL ^ E_NOTICE ^ E_WARNING);

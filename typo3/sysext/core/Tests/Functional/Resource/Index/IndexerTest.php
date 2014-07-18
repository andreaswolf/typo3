<?php
namespace TYPO3\CMS\Core\Tests\Functional\Resource\Index;

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

use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Functional test case for the FAL indexer.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class IndexerTest extends FunctionalTestCase {

	protected $pathsToLinkInTestInstance = array(
		'typo3/sysext/core/Tests/Functional/Resource/Fixtures/Folders/fileadmin/user_upload' => 'fileadmin/user_upload',
		'typo3/sysext/core/Tests/Functional/Resource/Fixtures/IndexerTestExtTables.php' => 'typo3conf/extTables.php',
	);

	protected $coreExtensionsToLoad = array('filemetadata');

	/**
	 * @test
	 */
	public function defaultValuesFromTcaAreUsedInNewImageIndexRecord() {
		/** @var StorageRepository $storageRepository */
		$storageRepository = GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\StorageRepository');
		$storage = $storageRepository->findByUid(1);
		$indexer = new Indexer($storage);

		$indexedFile = $indexer->createIndexEntry('user_upload/Stray_kitten_Rambo002.jpg');
		$indexer->runMetaDataExtraction();

		$this->assertEquals('The creator', $indexedFile->getProperty('creator'));
	}

}

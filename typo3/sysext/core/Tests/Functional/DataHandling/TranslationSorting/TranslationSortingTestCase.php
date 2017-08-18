<?php
namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\TranslationSorting;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Functional test for the sorting behaviour of DataHandler in page/record translations.
 */

class TranslationSortingTestCase extends \TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase
{
    const VALUE_PageId = 1;

    // "first" and "last" are already translated and we have to translate
    const VALUE_ContentIdDefaultFirst = 2;
    const VALUE_ContentIdDefaultMiddle = 7;
    const VALUE_ContentIdDefaultLast = 3;
    const VALUE_TranslationSortingFirst = 512;
    const VALUE_TranslationSortingLast = 1536;

    const VALUE_TranslatedContainerId = 6;

    const VALUE_LanguageId = 3;

    const TABLE_Content = 'tt_content';

    /**
     * @var string
     */
    protected $scenarioDataSetDirectory = 'typo3/sysext/core/Tests/Functional/DataHandling/TranslationSorting/DataSet/';

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/test_datahandler',
    ];

    /**
     * @var array
     */
    protected $pathsToLinkInTestInstance = [];

    /**
     * @var ActionService
     */
    protected $actionService;


    protected function setUp()
    {
        parent::setUp();
        $this->importScenarioDataSet('LiveDefaultPages');
        $this->importScenarioDataSet('LiveDefaultElements');

        /*$GLOBALS['TYPO3_DB'] = new DatabaseConnection();
        $GLOBALS['TYPO3_DB']->setDatabaseUsername('typo3');
        $GLOBALS['TYPO3_DB']->setDatabasePassword('typo3');
        $GLOBALS['TYPO3_DB']->setDatabaseName('typo3_dev_87');
        $GLOBALS['TYPO3_DB']->sql_select_db();

        $conn = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_gridelements_backend_layout');
        $conn->insert(
                'tx_gridelements_backend_layout',
                [
                    'uid'                 =>              1,
                    'pid'                 =>              0,
                    'tstamp'              =>              1502171036,
                    'crdate'              =>              1502170941,
                    'cruser_id'           =>              1,
                    'sorting'             =>              256,
                    'deleted'             =>              0,
                    'hidden'              =>              0,
                    'title'               =>              "Test-CE",
                    'alias'               =>              "",
                    'frame'               =>              0,
                    'description'         =>              "",
                    'horizontal'          =>              0,
                    'top_level_layout'    =>              0,
                    'config'              =>              "backend_layout {
colCount = 1
rowCount = 1
rows {
1 {
columns {
1 {
name = Main
colPos = 10
}
}
}
}
}",
                    'pi_flexform_ds'       => "",
                    'pi_flexform_ds_file'  => "",
                    'icon'                 => ""
            ]
        );*/

    }

    /**
     * @return ActionService
     */
    protected function getActionService()
    {
        return GeneralUtility::makeInstance(
            ActionService::class
        );
    }


    /**
     * @test
     */
    public function translatingPageKeepsSortingOrderOfElements()
    {
        $result = $this->actionService->synchronizeInlineLocalization(
            'tt_content', self::VALUE_TranslatedContainerId, 'tx_testdatahandler_children', self::VALUE_LanguageId, [2,7,3]
        );

        $newlyLocalizedUid = $result['tt_content'][self::VALUE_ContentIdDefaultMiddle];


        $translations = [
            BackendUtility::getRecordLocalization('tt_content', self::VALUE_ContentIdDefaultFirst, self::VALUE_LanguageId)[0],
            BackendUtility::getRecordLocalization('tt_content', self::VALUE_ContentIdDefaultMiddle, self::VALUE_LanguageId)[0],
            BackendUtility::getRecordLocalization('tt_content', self::VALUE_ContentIdDefaultLast, self::VALUE_LanguageId)[0],
        ];

        $qb = $this->getQueryBuilderForTable('tt_content');
        $qb->getRestrictions()->removeAll();
        $res = $qb->select('uid','pid','sorting', 'l18n_parent','header', 'tx_testdatahandler_container', 'tx_testdatahandler_children')
            ->from('tt_content')
            ->execute()->fetchAll(\PDO::FETCH_ASSOC);

        print_r($res);

        $this->assertLessThan($translations[1]['sorting'], $translations[0]['sorting'], 'middle element was not sorted after first one');
        $this->assertLessThan($translations[2]['sorting'], $translations[1]['sorting'], 'last element was not sorted after middle one');
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected static function getQueryBuilderForTable($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}

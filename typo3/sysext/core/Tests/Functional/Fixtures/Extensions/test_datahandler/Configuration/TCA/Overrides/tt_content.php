<?php
declare(strict_types=1);

// NOTE the declarations in this file have been borrowed (and adapted) from Gridelements

$tempColumns = [
    'tx_testdatahandler_children' => [
        'exclude' => 1,
        'label' => 'Child records',
        'config' => [
            'type' => 'inline',
            'appearance' => [
                'levelLinksPosition' => 'top',
                'showPossibleLocalizationRecords' => true,
                'showRemovedLocalizationRecords' => true,
                'showAllLocalizationLink' => true,
                'showSynchronizationLink' => true,
                'enabledControls' => [
                    'info' => true,
                    'new' => false,
                    'dragdrop' => false,
                    'sort' => false,
                    'hide' => true,
                    'delete' => true,
                    'localize' => true,
                ]
            ],
            'inline' => [
                'inlineNewButtonStyle' => 'display: inline-block;',
            ],
            'behaviour' => [
                'localizationMode' => 'select',
                'localizeChildrenAtParentLocalization' => true,
            ],
            'foreign_table' => 'tt_content',
            'foreign_field' => 'tx_testdatahandler_container',
            'foreign_record_defaults' => [
                'colPos' => -1,
            ],
            'foreign_sortby' => 'sorting',
            'size' => 5,
            'autoSizeMax' => 20,
        ]
    ],
    'tx_testdatahandler_container' => [
        'exclude' => 1,
        'label' => 'Container',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    '',
                    0
                ],
            ],
            'default' => 0,
            'foreign_table' => 'tt_content',
            'foreign_table_where' => 'AND (tt_content.sys_language_uid = ###REC_FIELD_sys_language_uid### OR tt_content.sys_language_uid = -1) AND tt_content.pid=###CURRENT_PID### AND tt_content.CType=\'testdatahandler_pi1\' AND (tt_content.uid != ###THIS_UID###) AND (tt_content.tx_testdatahandler_container != ###THIS_UID### OR tt_content.tx_testdatahandler_container=0) ORDER BY tt_content.header, tt_content.uid',
            'dontRemapTablesOnCopy' => 'tt_content',
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns);

$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',tx_testdatahandler_container,tx_testdatahandler_columns,colPos';
$GLOBALS['TCA']['tt_content']['ctrl']['useColumnsForDefaultValues'] .= ',tx_testdatahandler_container,tx_testdatahandler_columns';
$GLOBALS['TCA']['tt_content']['ctrl']['shadowColumnsForNewPlaceholders'] .= ',tx_testdatahandler_container,tx_testdatahandler_columns';

// TODO check if it is relevant for our case that the CType "testdatahandler_container" is not really defined anywhere

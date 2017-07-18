<?php
return [
    'ctrl' => [
        'label' => 'domainName', // TODO replace by function
        'label_userFunc' => \TYPO3\CMS\Frontend\Hooks\SiteLabelHook::class . '->renderLabel',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
    ],
    'columns' => [
        'domainName' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:sys_site.domainName',
            'config' => [
                'type' => 'input',
                'size' => 35,
                'max' => 80,
                'eval' => 'required,lower,trim,domainname',
                'softref' => 'substitute'
            ]
        ],
        'basePath' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:sys_site.basePath',
            'config' => [
                'type' => 'input',
                'size' => 35,
                'max' => 80,
                'default' => '/',
                'eval' => 'required,lower,trim'
            ]
        ],
        'language' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:sys_site.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [],
                'default' => 0,
            ]
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, domainName,basePath,language
            ',
        ],
    ],
];

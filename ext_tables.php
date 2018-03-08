<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript/',
    'Search Algolia'
);


/**
 * Include Backend Module
 */
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Mahu.search_algolia',
        'web',
        'm1',
        '',
        [
            'Module' => 'list, triggerReIndexing'
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:'.$_EXTKEY.'/Resources/Public/Icons/algolia.svg',
            'labels' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_mod.xlf'
        ]
    );
}

<?php

call_user_func(
    function ($extensionKey) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\Container\Container')
            ->registerImplementation(
                'Codappix\SearchCore\Connection\ConnectionInterface',
                'Mahu\SearchAlgolia\Connection\Algolia'
            );
    },
    $_EXTKEY
);

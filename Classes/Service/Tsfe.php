<?php

namespace Mahu\SearchAlgolia\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;


class Tsfe implements SingletonInterface
{
    /**
     * Tsfe constructor.
     *
     * @param int $pageId The page id to initialize the TSFE for
     * @param int $language System language uid, optional, defaults to 0
     * @param bool $useCache Use cache to reuse TSFE
     */
    public function __construct($pageId, $language = 0, $useCache = true)
    {
        static $tsfeCache = [];
        // resetting, a TSFE instance with data from a different page Id could be set already
        unset($GLOBALS['TSFE']);
        $cacheId = $pageId . '|' . $language;
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class, false);
        }
        if (!isset($tsfeCache[$cacheId]) || !$useCache) {
            GeneralUtility::_GETset($language, 'L');
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                $pageId,
                0
            );
            // for certain situations we need to trick TSFE into granting us
            // access to the page in any case to make getPageAndRootline() work
            // see http://forge.typo3.org/issues/42122
            $pageRecord = BackendUtility::getRecord('pages', $pageId, 'fe_group');
            $groupListBackup = $GLOBALS['TSFE']->gr_list;
            $GLOBALS['TSFE']->gr_list = $pageRecord['fe_group'];
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
            $GLOBALS['TSFE']->getPageAndRootline();
            // restore gr_list
            $GLOBALS['TSFE']->gr_list = $groupListBackup;
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->forceTemplateParsing = true;
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->initUserGroups();
            //  $GLOBALS['TSFE']->getCompressedTCarray(); // seems to cause conflicts sometimes
            $GLOBALS['TSFE']->no_cache = true;
            $GLOBALS['TSFE']->tmpl->start($GLOBALS['TSFE']->rootLine);
            $GLOBALS['TSFE']->no_cache = false;
            $GLOBALS['TSFE']->getConfigArray();
            $GLOBALS['TSFE']->settingLanguage();
            if (!$useCache) {
                $GLOBALS['TSFE']->settingLocale();
            }
            $GLOBALS['TSFE']->newCObj();
            $GLOBALS['TSFE']->absRefPrefix = '';
            $GLOBALS['TSFE']->calculateLinkVars();
            if ($useCache) {
                $tsfeCache[$cacheId] = $GLOBALS['TSFE'];
            }
        }
        if ($useCache) {
            $GLOBALS['TSFE'] = $tsfeCache[$cacheId];
            $GLOBALS['TSFE']->settingLocale();
        }
    }

}
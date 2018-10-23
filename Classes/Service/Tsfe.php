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
            self::getPageAndRootlineOfTSFE($pageId);
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

    /**
     * @deprecated This is only implemented to provide compatibility for TYPO3 8 and 9 when we drop TYPO3 8 support this
     * should changed to use a middleware stack
     * @param integer $pageId
     */
    private static function getPageAndRootlineOfTSFE($pageId)
    {
        //@todo This can be dropped when TYPO3 8 compatibility is dropped
        if (self::getIsTYPO3VersionBelow9()) {
            $GLOBALS['TSFE']->getPageAndRootline();
        } else {
            //@todo When we drop the support of TYPO3 8 we should use the frontend middleware stack instead of initializing this on our own
            $GLOBALS['TSFE']->getPageAndRootlineWithDomain(1);
        }
    }

    /**
     * @todo This method is just added for pages_language_overlay compatibility checks and will be removed when TYPO8 support is dropped
     * @return boolean
     */
    public static function getIsTYPO3VersionBelow9()
    {
        return (bool)version_compare(TYPO3_branch, '9.0', '<');
    }

}
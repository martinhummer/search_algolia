<?php 

namespace Mahu\SearchAlgolia\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Codappix\SearchCore\Domain\Index\TcaIndexer\InvalidArgumentException;

class RelationResolver
{
    protected $tableName;

    public function __construct()
    {
        $this->initializeTsfe(1, 0, false);
    }

    public function processRelations($config, &$record)
    {
        $this->tableName = $config['tableName'];

        foreach (array_keys($record) as $column) {
            $this->getRelations($column, $record, $config['relationLabelField'][$column]);
        }

        //var_dump($record);
    }

    protected function getRelations($column, &$record, $relationLabelField = 'title')
    {
        try {
            $columnConfig = $this->getColumnConfig($column);
            if ($this->isRelation($columnConfig)) {
                $loadDBGroup = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\RelationHandler::class);
                $loadDBGroup->start(
                            $record[$column],
                            // @todo That depends on the type (group, select, inline)
                            $columnConfig['foreign_table'],
                            $columnConfig['MM'],
                            $record['uid'],
                            $this->tableName,
                            $columnConfig
                        );
                $relationUids = $loadDBGroup->tableArray[$columnConfig['foreign_table']];
            }
        } catch (InvalidArgumentException $e) {
            // Column is not configured.
        }

        foreach ($relationUids as $relationUid) {
            $relationRecord = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($columnConfig['foreign_table'])
            ->select(
                ['*'], // fields to select
                $columnConfig['foreign_table'], // from
                ['uid' => $relationUid] // where
            )->fetch();

            if ($relationRecord) {
                $overlayRecord = $this->getRecordOverlay($columnConfig['foreign_table'], $relationRecord, $record['sys_language_uid']);
                $relations[] = $overlayRecord[$relationLabelField];
            }
        }

        if ($relations) {
            $record[$column] = $relations;
        }
    }

    protected function getRecordOverlay($tableName, $relationRecord, $sysLanguageUid)
    {
        $page = GeneralUtility::makeInstance(PageRepository::class);
        $page->init(false);
        $overlayRecord = $page->getRecordOverlay($tableName, $relationRecord, $sysLanguageUid, 'content_fallback');

        return $overlayRecord;
    }

    /**
     * @param string $columnName
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getColumnConfig($columnName) : array
    {
        if (!isset($GLOBALS['TCA'][$this->tableName]['columns'][$columnName])) {
            throw new InvalidArgumentException(
                'Column does not exist.',
                InvalidArgumentException::COLUMN_DOES_NOT_EXIST
            );
        }

        return $GLOBALS['TCA'][$this->tableName]['columns'][$columnName]['config'];
    }

    protected function isRelation(array &$config) : bool
    {
        return isset($config['foreign_table'])
            || (isset($config['renderType']) && $config['renderType'] !== 'selectSingle')
            || (isset($config['internal_type']) && strtolower($config['internal_type']) === 'db');
    }

    /**
    * Initializes the TSFE for a given page ID and language.
    *
    * @param int $pageId The page id to initialize the TSFE for
    * @param int $language System language uid, optional, defaults to 0
    * @param bool $useCache Use cache to reuse TSFE
    * @return void
    */
    public static function initializeTsfe($pageId, $language = 0, $useCache = true)
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

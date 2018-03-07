<?php

namespace Mahu\SearchAlgolia\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Page\PageRepository;
use Codappix\SearchCore\Domain\Index\TcaIndexer\InvalidArgumentException;

class RelationResolver
{
    protected $tableName;

    public function __construct()
    {
        GeneralUtility::makeInstance(Tsfe::class, 1);
    }

    public function processRelations($config, &$record)
    {
        $this->tableName = $config['tableName'];

        foreach (array_keys($config['relationLabelField']) as $column) {
            try {

                $columnConfig = $this->getColumnConfig($column);
                if ($this->isRelation($columnConfig)) {
                    $this->getRelations($column, $record, $config['relationLabelField'][$column], $columnConfig);
                }

            } catch (InvalidArgumentException $e) {

            }

        }
    }

    protected function getRelations($column, &$record, $relationLabelField = 'title', $columnConfig)
    {
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

        if ($relationUids) {
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
        || (isset($config['internal_type']) && strtolower($config['internal_type']) === 'db');
    }


}

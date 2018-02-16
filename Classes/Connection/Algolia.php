<?php

namespace Mahu\SearchAlgolia\Connection;

/*
 * Copyright (C) 2017 Martin Hummer>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use Codappix\SearchCore\Connection\SearchRequestInterface;
use Codappix\SearchCore\Connection\SearchResultInterface;
use Codappix\SearchCore\Domain\Search\QueryFactory;
use TYPO3\CMS\Core\SingletonInterface as Singleton;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use Codappix\SearchCore\Connection\ConnectionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Outer wrapper to algolia.
 */
class Algolia implements Singleton, ConnectionInterface
{
    /**
     * @var Algolia\Connection
     */
    protected $connection;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected $taskId;

    protected $taskObserver;

    /**
     * Inject log manager to get concrete logger from it.
     *
     * @param \TYPO3\CMS\Core\Log\LogManager $logManager
     */
    public function injectLogger(\TYPO3\CMS\Core\Log\LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Algolia\Connection $connection
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        Algolia\Connection $connection,

        QueryFactory $queryFactory
    ) {
        $this->connection = $connection;
        $this->queryFactory = $queryFactory;
        $this->taskObserver = GeneralUtility::makeInstance('Mahu\SearchAlgolia\Connection\Algolia\TaskObserver');
    }

    public function addDocument($documentType, array $document)
    {
        $request = $this->connection->getIndex()->addObject($document, $document['uid']); //PHP Algolia Search Client

        $this->taskObserver->setTaskId($request['taskID']); //store the current taskId
    }

    public function addDocuments($documentType, array $documents)
    {
        $this->connection->getIndex()->addObjects($documents); //PHP Algolia Search Client
    }

    /**
     * Will update an existing document.
     *
     * NOTE: Batch updating is not yet supported.
     *
     * @param string $documentType
     * @param array $document
     *
     * @return void
     */
    public function updateDocument($documentType, array $document)
    {
        return $this->connection->getIndex()->addObject($document, $document['uid']); //Same as addDocument()
    }

    /**
     * Will remove an existing document.
     *
     * NOTE: Batch deleting is not yet supported.
     *
     * @param string $documentType
     * @param int $identifier
     *
     * @return void
     */
    public function deleteDocument($documentType, $identifier)
    {
        $request = $this->connection->getIndex()->deleteObject($identifier); //PHP Algolia Search Client

        $this->taskObserver->setTaskId($request['taskID']); //store the current taskId
    }

    /**
     * Search by given request and return result.
     *
     * @param SearchRequestInterface $searchRequest
     *
     * @return SearchResultInterface
     */
    public function search(SearchRequestInterface $searchRequest)
    {
        // TODO: Implement search() method.
    }

    /**
     * Will delete the whole index / db.
     *
     * @param string $documentType
     *
     * @return void
     */
    public function deleteIndex($documentType)
    {
        // TODO: Implement deleteIndex() method.
    }
}

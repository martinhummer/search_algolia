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
use TYPO3\CMS\Core\SingletonInterface as Singleton;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use Codappix\SearchCore\Connection\ConnectionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use AlgoliaSearch\AlgoliaConnectionException;
use Codappix\SearchCore\Configuration\InvalidArgumentException;

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
     * @var Algolia\IndexFactory
     */
    protected $indexFactory;

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

    protected $configuration;

    /**
     * Inject log manager to get concrete logger from it.
     *
     * @param \TYPO3\CMS\Core\Log\LogManager $logManager
     */
    public function injectLogger( \TYPO3\CMS\Core\Log\LogManager $logManager )
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager( ObjectManagerInterface $objectManager )
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Algolia\Connection $connection
     * @param Algolia\IndexFactory $indexFactory
     */
    public function __construct(
        Algolia\Connection $connection,
        Algolia\IndexFactory $indexFactory
    )
    {
        $this->connection = $connection;
        $this->indexFactory = $indexFactory;
        $this->taskObserver = GeneralUtility::makeInstance('Mahu\SearchAlgolia\Connection\Algolia\TaskObserver');
        $this->configuration = $connection->getConfiguration();
    }

    /**
     * @param string $documentType
     * @param array $document
     */
    public function addDocument( string $documentType, array $document )
    {
        $this->logger->info('Start indexing single record.', ['Table: ' . $documentType, 'UID: ' . $document['uid']]);

        try {
            $request = $this->getIndex($this->connection, $documentType)->addObject($document, $document['uid']); //PHP Algolia Search Client
            $this->taskObserver->setTaskId($request['taskID']); //store the current taskId
            $this->renderFlashMessage('Algolia Search Indexer', 'Record was successfully indexed, TaskID: ' . $request['taskID'] . ', Table: ' . $documentType, \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
        } catch (AlgoliaConnectionException $e) {
            $this->renderFlashMessage('Algolia Search Indexer', 'Record could not be indexed', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            $this->logger->error('Error while indexing Record', ['Table: ' . $documentType, 'UID: ' . $document['uid'], 'ErrorMessage: ' . $e->getMessage()]);
        }

    }

    public function addDocuments(string $documentType, array $documents)
    {
        foreach ($documents as $document) {
            $this->addDocument($documentType, $document);
        }

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
    public function updateDocument(string $documentType, array $document)
    {
        //Same as addDocument()
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
    public function deleteDocument(string $documentType, string $identifier)
    {
        try {
            $request = $this->getIndex($this->connection, $documentType)->deleteObject($identifier); //PHP Algolia Search Client
            $this->taskObserver->setTaskId($request['taskID']); //store the current taskId
        }  catch (AlgoliaConnectionException $e) {
            $this->renderFlashMessage('Algolia Search Indexer', 'Record could not be deleted', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
            $this->logger->error('Error while deleting Record', ['Table: ' . $documentType, 'UID: ' . $identifier, 'ErrorMessage: ' . $e->getMessage()]);
        }

    }

    /**
     * Search by given request and return result.
     *
     * @param SearchRequestInterface $searchRequest
     *
     * @return SearchResultInterface
     */
    public function search(SearchRequestInterface $searchRequest) : SearchResultInterface
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
    public function deleteIndex(string $documentType)
    {
        // TODO: Implement deleteIndex() method.
    }

    /**
     *
     * Get the index
     *
     * @param Algolia\Connection $connection
     * @param string $documentType tableName
     */
    public function getIndex( $connection, $documentType )
    {
        return $this->indexFactory->getIndex($connection, $documentType);
    }

    protected function renderFlashMessage( $title, $message, $severity )
    {
        try {
            if ($this->configuration->get('debug')) {
                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                    $message,
                    $title,
                    $severity, // [optional] the severity defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                    true // [optional] whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is false)
                );
                $flashMessageService = $this->objectManager->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
            }
        } catch (InvalidArgumentException $e) {
            //do nothing, because we are in production Application Context
        }
    }
}

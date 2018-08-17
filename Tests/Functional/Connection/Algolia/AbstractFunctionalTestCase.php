<?php

namespace Mahu\SearchAlgolia\Tests\Functional\Connection\Algolia;

/*
 * Copyright (C) 2018  Martin Hummer <ma.hummer@gmail.com>
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

use Mahu\SearchAlgolia\Tests\Functional\AbstractFunctionalTestCase as BaseFunctionalTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Dotenv\Dotenv;


/**
 * All functional tests should extend this base class.
 *
 * It will take care of leaving a clean environment for next test.
 */
abstract class AbstractFunctionalTestCase extends BaseFunctionalTestCase
{
    /**
     * @var \AlgoliaSearch\Client
     */
    protected $client;

    protected $configuration;

    protected $index;

    protected $indexName;

    /**
     * @var \Mahu\SearchAlgolia\Connection\Algolia\TaskObserver
     * */
    protected $taskObserver;

    public function setUp()
    {
        parent::setUp();

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        $this->configuration = $objectManager->get(ConfigurationContainerInterface::class);

        // Make instance of the TaskObserver which holds the current TaskId if an action is performed on the index
        $this->taskObserver = GeneralUtility::makeInstance('Mahu\SearchAlgolia\Connection\Algolia\TaskObserver');

        // Create client to make requests and assert something.
        $this->client = new \AlgoliaSearch\Client(
            getenv('ALGOLIA_APP_ID'),
            getenv('ALGOLIA_API_KEY')
        );
    }

    public function tearDown()
    {
        // Make system clean again.
        $this->cleanUp();
    }

    protected function cleanUp()
    {
        $request = $this->client->deleteIndex($this->indexName);
        $this->index->waitTask($request['taskID']);
    }

    /**
     * returns the Algolia Index based on the indexName which is specified in the typoscript configuration.
     * If no indexName is specified in the configuration, then the tablename (documentType) will be used instead.
     *
     * @param $documentType tableName
     */
    public function initIndex($documentType)
    {
        $this->indexName = $this->getIndexNameFromConfiguration($documentType);

        if ($this->indexName) {
            $this->index = $this->client->initIndex($this->indexName);
            $request = $this->index->clearIndex();
            $this->index->waitTask($request['taskID']);
        } else {
            $this->index = $this->client->initIndex($documentType);
            $request = $this->index->clearIndex();
            $this->index->waitTask($request['taskID']);
        }
    }

    protected function getIndexNameFromConfiguration($documentType)
    {
        try {
            $config = $this->configuration->get('indexing.' . $documentType);

            if (isset($config['indexName'])) {
                return $config['indexName'];
            }
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}

<?php

namespace Mahu\SearchAlgolia\Tests\Functional\Connection\Algolia;

/*
 * Copyright (C) 2016  Daniel Siepmann <coding@daniel-siepmann.de>
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

    /**
     * @var \AlgoliaSearch\Index
     * */
    protected $algoliaIndex;

    protected $configuration;

    /**
     * @var \Mahu\SearchAlgolia\TaskObserver
     * */
    protected $taskObserver;

    public function setUp()
    {
        parent::setUp();

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
        $this->configuration = $objectManager->get(ConfigurationContainerInterface::class);

        // Make instance of the TaskObserver which holds the current TaskId
        $this->taskObserver = GeneralUtility::makeInstance('Mahu\SearchAlgolia\Connection\Algolia\TaskObserver');

        // Create client to make requests and assert something.
        $this->client = new \AlgoliaSearch\Client(
            $this->configuration->get('connections.algolia.applicationID'),
            $this->configuration->get('connections.algolia.apiKey')
        );

        $this->algoliaIndex = $this->client->initIndex(
            $this->configuration->get('connections.algolia.indexName')
        );

        // Start with clean system for test.
        $this->cleanUp();
    }

    public function tearDown()
    {
        // Make system clean again.
        //$this->cleanUp();
    }

    protected function cleanUp()
    {
        $this->client->deleteIndex($this->configuration->get('connections.algolia.indexName'));
    }
}

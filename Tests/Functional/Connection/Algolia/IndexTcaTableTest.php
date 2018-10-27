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

use Codappix\SearchCore\Domain\Index\IndexerFactory;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\DataHandling\DataHandler as Typo3DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexTcaTableTest extends AbstractFunctionalTestCase
{
    protected function getDataSets()
    {
        return array_merge(
            parent::getDataSets(),
            ['EXT:search_algolia/Tests/Functional/Fixtures/Indexing/IndexTcaTable.xml']
        );
    }

    /**
     * @group tt_content
     * @test
     */
    public function indexMultipleTtContent()
    {
        $this->initIndex('tt_content');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tt_content')
            ->indexAllDocuments();

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 3, 'Not exactly 3 documents were indexed.');

    }

    /**
     * 
     * @test
     */
    public function indexSingleTtContent()
    {
        $this->initIndex('tt_content');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tt_content')
            ->indexDocument(6);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => ['header' => 'indexed content element', 'starttime' => 1480686370]],
            $response['hits'],
            false,
            'tt_content Record was not indexed.'
        );
    }



    /**
     * @group bla
     * @test
     */
    /*public function indexSinglePageContent()
    {
        $this->initIndex('pages');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('pages')
            ->indexDocument(1);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        var_dump($response['hits'][0]);

        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => ['header' => 'indexed content element']],
            $response['hits'],
            false,
            'tt_content Record was not indexed.'
        );
    }*/

    /**
     * 
    * @test
    */
    public function updateSingleTtContent()
    {
        $this->initIndex('tt_content');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tt_content')
            ->indexDocument(6);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId
        $this->index->waitTask($taskId);

        $this->getConnectionPool()->getConnectionForTable('tt_content')
            ->update(
                'tt_content',
                ['header' => 'update the header new'],
                ['uid' => 6]
            );
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tt_content')
            ->indexDocument(6);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        $this->assertArraySubset(
            [0 => ['header' => 'update the header new']],
            $response['hits'],
            false,
            'tt_content record was not updated correctly.'
        );
    }

    /**
    * @group tt_content_delete
    * @test
    */
    public function deleteSingleTtContent()
    {

        $this->initIndex('tt_content');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tt_content')
            ->indexDocument(6);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);

        $tce = GeneralUtility::makeInstance(Typo3DataHandler::class);
        $tce->stripslashes_values = 0;
        $tce->start([], [
                'tt_content' => [
                    '6' => [
                        'delete' => true,
                    ],
                ],
            ]);
        $tce->process_cmdmap();

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId); //wait until Angolia has finished the task

        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 0, 'Not exactly 0 documents were indexed.');
    }
}

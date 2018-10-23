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

class IndexPagesTableTest extends AbstractFunctionalTestCase
{
    protected function getDataSets()
    {
        return array_merge(
            parent::getDataSets(),
            ['EXT:search_algolia/Tests/Functional/Fixtures/Indexing/IndexTcaTable.xml']
        );
    }



    /**
     * @group pages
     * @test
     */
    public function indexSinglePageContent()
    {
        $this->initIndex('pages');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('pages')
            ->indexDocument(1);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        //var_dump($response['hits'][0]);

        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => ['content' => 'this is the content of header content element that should get indexed Some text in paragraph']],
            $response['hits'],
            false,
            'pages Record was not indexed.'
        );
    }

    /**
     * @group pages
     * @test
     */
    public function indexTranslatedPage()
    {
        $this->initIndex('pages');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('pages')
            ->indexDocument(2);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => [
                'title' => '[Uebersetzung Deutsch] Startseite',
                'content' => '[Translate to Deutsch:] Deutscher Inhalt'
            ]],
            $response['hits'],
            false,
            'pages Record was not indexed.'
        );
    }





}

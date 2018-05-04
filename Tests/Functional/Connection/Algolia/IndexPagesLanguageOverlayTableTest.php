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

class IndexPagesLanguageOverlayTableTest extends AbstractFunctionalTestCase
{
    protected function getDataSets()
    {
        return array_merge(
            parent::getDataSets(),
            ['Tests/Functional/Fixtures/Indexing/PagesLanguageOverlay.xml']
        );
    }



    /**
     * @group pages_language_overlay
     * @test
     */
    public function indexSinglePageContent()
    {
        $this->initIndex('pages_language_overlay');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('pages_language_overlay')
            ->indexDocument(2);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);
        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => ['content' => '[Translate to English:] this is the content of header content element that should get indexed']],
            $response['hits'],
            false,
            'pages Record was not indexed.'
        );
    }

    /**
     * There is a problem with deleting pages_language_overlay records
     * because DataHandler does not trigger a delete action for pages_language_overlay if the parent (default language) page gets deleted.
     *
     * Maybe we can find another hook in Datahandler which gets triggered if a page is deleted
     *
     * @group pages_language_overlay_delete
     * @test
     */
    /*public function deleteSinglePage()
    {

        $this->initIndex('pages_language_overlay');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('pages_language_overlay')
            ->indexDocument(2);

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        $this->index->waitTask($taskId);

        $tce = GeneralUtility::makeInstance(Typo3DataHandler::class);
        $tce->stripslashes_values = 0;
        $tce->start([], [
            'pages' => [
                '2' => [
                    'delete' => true,
                ],
            ],
        ]);
        $tce->process_cmdmap();

        $taskId = $this->taskObserver->getTaskId(); //holds the current taskId

        //$this->index->waitTask($taskId); //wait until Angolia has finished the task

        $response = $this->index->search('*');

        $this->assertSame($response['nbHits'], 0, 'Not exactly 0 documents were indexed.');
    }*/


}

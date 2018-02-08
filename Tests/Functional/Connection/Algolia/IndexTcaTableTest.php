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

use Codappix\SearchCore\Domain\Index\IndexerFactory;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\DataHandling\DataHandler as Typo3DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TODO: https://github.com/DanielSiepmann/search_core/issues/16
 */
class IndexTcaTableTest extends AbstractFunctionalTestCase
{
    protected function getDataSets()
    {
        return array_merge(
            parent::getDataSets(),
            ['Tests/Functional/Fixtures/Indexing/IndexNewsTable.xml']
        );
    }

    /**
     * @test
     */
    public function indexSingleNewsContent()
    {
        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

        $this->algoliaIndex->waitTask($request['taskID']);
        $response = $this->algoliaIndex->search('*');
        
        $this->assertSame($response['nbHits'], 1, 'Not exactly 1 document was indexed.');
        $this->assertArraySubset(
            [0 => ['title' => 'Single News Record']],
            $response['hits'],
            false,
            'Single News Record was not indexed.'
        );
    }

    /**
    * @test
    */
    public function updateSingleNewsContent()
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

        $this->getConnectionPool()->getConnectionForTable('tx_news_domain_model_news')
            ->update(
                'tx_news_domain_model_news',
                ['title' => 'update the title'],
                ['uid' => 456]
            );
        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

        $this->algoliaIndex->waitTask($request['taskID']);
        $response = $this->algoliaIndex->search('*');

        $this->assertArraySubset(
            [0 => ['title' => 'update the title']],
            $response['hits'],
            false,
            'Record was not updated correctly.'
        );
    }

    /**
    * @test
    */
    public function deleteSingleNewsContent()
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

            //TODO: find a solution to test this on the index
            $tce = GeneralUtility::makeInstance(Typo3DataHandler::class);
            $tce->stripslashes_values = 0;
            $tce->start([], [
                'tx_news_domain_model_news' => [
                    '456' => [
                        'delete' => true,
                    ],
                ],
            ]);
            $tce->process_cmdmap();
    }
}

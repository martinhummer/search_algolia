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
    public function indexNewsContent()
    {
        $this->markTestSkipped('must be revisited.');

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

        //$searchQuery = $this->algoliaIndex->search('*');
        //print_r(array_keys($searchQuery));

        /*$response = $this->client->request('typo3content/_search?q=*:*');

        $this->assertTrue($response->isOK(), 'Elastica did not answer with ok code.');
        $this->assertSame($response->getData()['hits']['total'], 2, 'Not exactly 2 documents were indexed.');
        $this->assertArraySubset(
            ['_source' => ['header' => 'indexed content element']],
            $response->getData()['hits']['hits'][1],
            false,
            'Record was not indexed.'
        );*/
    }

    /**
    * @test
    */
    public function updateNewsContent()
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

       // $this->algoliaIndex->waitTask($request['taskID']);
        //$results = $this->algoliaIndex->search('*');
        //var_dump($results);
        //$searchQuery = $this->algoliaIndex->search('*');
        //print_r(array_keys($searchQuery));
        //var_dump($searchQuery);
    }

    /**
    * @test
    */
    public function searchNewsContent()
    {
        $this->markTestSkipped('must be revisited.');

        $searchQuery = $this->algoliaIndex->search('*');
        //var_dump($searchQuery);
    }
}

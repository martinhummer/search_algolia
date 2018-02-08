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

        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
            ->get(IndexerFactory::class)
            ->getIndexer('tx_news_domain_model_news')
            ->indexDocument(456);

        $this->algoliaIndex->waitTask($request['taskID']);
        $results = $this->algoliaIndex->search('*');
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

        $this->algoliaIndex->waitTask($request['taskID']);
        $results = $this->algoliaIndex->search('*');
    }
}

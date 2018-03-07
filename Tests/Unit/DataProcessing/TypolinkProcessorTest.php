<?php

namespace Mahu\SearchAlgolia\Tests\Unit\Connection\Algolia;

/*
 * Copyright (C) 2017  Daniel Siepmann <coding@daniel-siepmann.de>
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

use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use Mahu\SearchAlgolia\Connection\Algolia\Connection;
use Mahu\SearchAlgolia\Connection\Algolia\IndexFactory;
use Mahu\SearchAlgolia\Tests\Unit\AbstractUnitTestCase;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TypolinkProcessorTest extends UnitTestCase  {

    protected $mockedTypolinkProcessor;

    protected function setUp()
    {
        $this->mockedTypolinkProcessor = $this->getAccessibleMock('Mahu\\SearchAlgolia\\DataProcessing\\TypolinkProcessor', ['dummy']);
    }

    /**
     * @test
     * @dataProvider getDetailPidFromDefaultDetailPidReturnsCorrectValueDataProvider
     */
    public function getDetailPidFromDefaultDetailPidReturnsCorrectValue($configuration, $expected)
    {
        $this->mockedTypolinkProcessor->_set('configuration', $configuration);

        $result = $this->mockedTypolinkProcessor->_call('createLinkConfig');
        $this->assertEquals($expected, $result['parameter']);
    }

    public function getDetailPidFromDefaultDetailPidReturnsCorrectValueDataProvider()
    {
        return [
            [['detailPid' => '789'], 789],
            [['detailPid' => '45'], 45]
        ];
    }

}

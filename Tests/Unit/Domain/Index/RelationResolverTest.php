<?php
namespace Mahu\SearchAlgolia\Tests\Unit\Domain\Index;

/*
 * Copyright (C) 2018  Daniel Siepmann <coding@daniel-siepmann.de>
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

use Codappix\SearchCore\Domain\Index\TcaIndexer\RelationResolver;
use Codappix\SearchCore\Domain\Index\TcaIndexer\TcaTableServiceInterface;
use Mahu\SearchAlgolia\Tests\Unit\AbstractUnitTestCase;

class RelationResolverTest extends AbstractUnitTestCase
{
    /**
     * @var RelationResolver
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new RelationResolver();
    }

    /**
     *
     * @test
     */
    public function renderTypeInputDateTimeIsHandled()
    {
        $originalRecord = [
            'starttime' => 1523010960
        ];
        $record = $originalRecord;
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'columns' => [
                    'starttime' => [
                        'exclude' => true,
                        'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
                        'config' => [
                            'type' => 'input',
                            'renderType' => 'inputDateTime',
                            'eval' => 'datetime,int',
                            'default' => 0
                        ],
                        'l10n_mode' => 'exclude',
                        'l10n_display' => 'defaultAsReadonly'
                    ],
                ],
            ],
        ];
        $tableServiceMock = $this->getMockBuilder(TcaTableServiceInterface::class)->getMock();
        $tableServiceMock->expects($this->any())
            ->method('getTableName')
            ->willReturn('tt_content');
        $tableServiceMock->expects($this->any())
            ->method('getColumnConfig')
            ->willReturn($GLOBALS['TCA']['tt_content']['columns']['starttime']['config']);

        $this->subject->resolveRelationsForRecord($tableServiceMock, $record);

        $this->assertSame(
            $originalRecord,
            $record,
            'starttime'
        );
    }

    /**
     * @group lang
     * @test
     */
    public function sysLanguageUidZeroIsKept()
    {
        $record = [
            'sys_language_uid' => 0,
        ];
        $GLOBALS['TCA'] = [
            'tt_content' => [
                'columns' => [
                    'sys_language_uid' => [
                        'config' => [
                            'default' => 0,
                            'items' => [
                                [
                                    'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                                    '-1',
                                    'flags-multiple',
                                ],
                            ],
                            'renderType' => 'selectSingle',
                            'special' => 'languages',
                            'type' => 'select',
                            'exclude' => '1',
                            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
                        ],
                    ],
                ],
            ],
        ];
        $tableServiceMock = $this->getMockBuilder(TcaTableServiceInterface::class)->getMock();
        $tableServiceMock->expects($this->any())
            ->method('getTableName')
            ->willReturn('tt_content');
        $tableServiceMock->expects($this->any())
            ->method('getLanguageUidColumn')
            ->willReturn('sys_language_uid');
        $tableServiceMock->expects($this->any())
            ->method('getColumnConfig')
            ->willReturn($GLOBALS['TCA']['tt_content']['columns']['sys_language_uid']['config']);
        $this->subject->resolveRelationsForRecord($tableServiceMock, $record);
        $this->assertSame(
            [
                'sys_language_uid' => 0,
            ],
            $record,
            'sys_language_uid was not kept as zero.'
        );
    }
}


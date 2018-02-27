<?php
/*
 * Copyright (C) 2018  Martin Hummer
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

namespace Mahu\SearchAlgolia\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Codappix\SearchCore\DataProcessing\ProcessorInterface;

/**
 * Processes every record before it is sent to the Index
 */
class RelationProcessor implements ProcessorInterface
{
    protected $relationResolver;

    public function __construct(
    ) {
        $this->relationResolver = GeneralUtility::makeInstance('Mahu\SearchAlgolia\Service\RelationResolver');
    }

    public function processRecord(array $record, array $configuration) : array
    {
        $this->relationResolver->processRelations($configuration, $record);

        return $record;
    }
}

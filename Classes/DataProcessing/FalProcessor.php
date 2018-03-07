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

use Codappix\SearchCore\DataProcessing\ProcessorInterface;

/**
 * Fetches the publicUrl of FileReferences and stores it in the record
 */
class FalProcessor implements ProcessorInterface
{
    protected $configuration;


    /**
     *
     * @param array $record
     * @param array $configuration
     * @return array
     */
    public function processData(array $record, array $configuration) : array
    {
        $this->configuration = $configuration;

        if (isset($this->configuration['fieldName']) && isset($record[$this->configuration['fieldName']])) {

            $fieldName = $this->configuration['fieldName'];
            $fileReferences = $record[$fieldName];

            foreach ($fileReferences as $fileReference) {
                if ($fileReference instanceof \TYPO3\CMS\Core\Resource\FileReference) {
                    $resolvedFalUrls[] = $fileReference->getOriginalFile()->getPublicUrl();
                }
            }

            $record[$fieldName] = $resolvedFalUrls;

        }

        return $record;
    }


}

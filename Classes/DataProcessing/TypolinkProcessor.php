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
class TypolinkProcessor implements ProcessorInterface
{
    protected $configuration;
    protected $record;
    protected $cObj;

    public function processData(array $record, array $configuration) : array
    {
        $this->configuration = $configuration;
        $this->record = $record;
        $this->cObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);

        $record['linkUrl'] = $this->getTypolink($this->createLinkConfig());

        return $record;
    }

    protected function createLinkConfig()
    {
        $linkConfig = [];
        $linkConfig['useCacheHash'] = 1;
        $linkConfig['parameter'] = $this->getDetailPid();
        $linkConfig['additionalParams'] .=
            '&' . $this->getPluginName() . '[' . $this->getDomainModel() . ']=' . $this->getRecordUid() .
            '&' . $this->getPluginName() . '[controller]=' . $this->getControllerName() .
            '&' . $this->getPluginName() . '[action]=' . $this->getActionName();
        return $linkConfig;
    }

    protected function getTypolink($linkConfig)
    {
        return $this->cObj->typoLink_URL($linkConfig);
    }

    protected function getDetailPid()
    {
        return $this->configuration['detailPid'];
    }

    protected function getPluginName()
    {
        return $this->configuration['plugin'];
    }

    protected function getDomainModel()
    {
        return $this->configuration['domainModel'];
    }

    protected function getControllerName()
    {
        return $this->configuration['controller'];
    }

    protected function getActionName()
    {
        return $this->configuration['action'];
    }

    protected function getRecordUid()
    {
        return $this->record['uid'];
    }


}

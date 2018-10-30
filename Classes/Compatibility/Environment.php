<?php

namespace Mahu\SearchAlgolia\Compatibility;

use Mahu\SearchAlgolia\Utility\EnvironmentInterface;

class Environment extends \TYPO3\CMS\Core\Core\Environment implements EnvironmentInterface
{
    public function getVarPath()
    {
        return static::getVarPath();
    }
}
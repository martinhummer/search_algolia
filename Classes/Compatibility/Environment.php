<?php

namespace Mahu\SearchAlgolia\Compatibility;

class Environment extends \TYPO3\CMS\Core\Core\Environment implements EnvironmentInterface
{
    public function loggingPath()
    {
        return self::getVarPath() . '/log';
    }
}

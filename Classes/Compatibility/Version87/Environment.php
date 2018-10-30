<?php

namespace Mahu\SearchAlgolia\Compatibility\Version87;

use Mahu\SearchAlgolia\Compatibility\EnvironmentInterface;

class Environment implements EnvironmentInterface
{
    public function getVarPath()
    {
        return 'typo3temp';
    }
}
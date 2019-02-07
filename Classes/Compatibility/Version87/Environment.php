<?php

namespace Mahu\SearchAlgolia\Compatibility\Version87;

use Mahu\SearchAlgolia\Compatibility\EnvironmentInterface;

class Environment implements EnvironmentInterface
{
    public function loggingPath()
    {
        return 'typo3temp/log';
    }
}

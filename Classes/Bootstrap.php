<?php

namespace Mahu\SearchAlgolia;

use Mahu\SearchAlgolia\Compatibility\EnvironmentInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Bootstrap
{
    /**
     * @return object|ObjectManager
     */
    public static function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return EnvironmentInterface
     */
    public static function getEnvironment()
    {
        return static::getObjectManager()->get(
            EnvironmentInterface::class
        );
    }
}
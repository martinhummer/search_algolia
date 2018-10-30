<?php

namespace Mahu\SearchAlgolia\Compatibility;

use Mahu\SearchAlgolia\Compatibility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;

class ImplementationRegistrationService
{
    public static function registerImplementations()
    {
        /** @var Container $container */
        $container = GeneralUtility::makeInstance(Container::class);

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
            $container->registerImplementation(
                Compatibility\EnvironmentInterface::class,
                Compatibility\Environment::class
            );
        } else if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            $container->registerImplementation(
                Compatibility\EnvironmentInterface::class,
                Compatibility\Version87\Environment::class
            );
        }
    }
}
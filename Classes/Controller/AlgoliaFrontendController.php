<?php

namespace Mahu\SearchAlgolia\Controller;

use Mahu\SearchAlgolia\Domain\Model\Dto\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
class AlgoliaFrontendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /** @var ExtensionConfiguration */
    protected $extensionConfiguration;

    /**
     * @var Mahu\SearchAlgolia\Domain\Repository\AlgoliaIndexRepository
     * @inject
     */
    protected $algoliaIndexRepository;


    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    }

    /**
     * Displays a search Page
     */
    public function searchAction()
    {
        /** TODO: Use secured Api Keys https://www.algolia.com/doc/guides/security/api-keys/#secured-api-keys */

        $algoliaSettings = [
            'appId' => $this->extensionConfiguration->getAppId(),
            'apiKey' => $this->extensionConfiguration->getReadOnlyApiKey(),
            'indexName' => $this->settings['algoliaIndex']
        ];

        $this->view->assignMultiple([
            'algoliaSettings' => $algoliaSettings
        ]);
    }

}
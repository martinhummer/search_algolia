<?php

namespace Mahu\SearchAlgolia\Hooks\Backend;

use Mahu\SearchAlgolia\Domain\Model\Dto\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class ItemsProcFunc
{

    /** @var ExtensionConfiguration */
    protected $extensionConfiguration;

    /**
     * @var \AlgoliaSearch\Client
     */
    protected $client;

    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

        $this->client = new \AlgoliaSearch\Client(
            $this->getAppId(),
            $this->getApiKey()
        );

    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        $key = $this->extensionConfiguration->getAdminApiKey();

        return $key;
    }

    /**
     * @return mixed
     */
    protected function getAppId()
    {
        $appId = $this->extensionConfiguration->getAppId();

        return $appId;
    }

    /**
     * Applies all Angolia indexes to a list in the plugin config
     *
     * @param array $config
     */
    public function getAlgoliaIndexes(array &$config)
    {

        $indexes = $this->client->listIndexes()['items'];

        foreach ($indexes as $index) {
            $config['items'][] = [
                $index['name'],
                $index['name']
            ];
        }
    }

    public function getIndexAttributesForFaceting(array &$config){
        //var_dump($pObj);

        //not working because we have no connection to the parent flexform
        //$index = $this->client->initIndex($config['row']['settings.algoliaIndex']);
        //$config['items'][] = $index->getSettings()['attributesForFaceting'];
    }


}

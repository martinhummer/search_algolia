<?php
namespace Mahu\SearchAlgolia\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use AlgoliaSearch\AlgoliaConnectionException;

/**
 * Class ModuleController for backend modules
 */
class ModuleController extends \TYPO3\CMS\Belog\Controller\AbstractController
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configManager;


    /**
     * @var array
     */
    protected $settings = array();

    protected $client;


    /**
     *
     */
    public function initializeAction()
    {
        $this->configManager = GeneralUtility::makeInstance(\Codappix\SearchCore\Configuration\ConfigurationContainer::class);
        $this->configManager->injectConfigurationManager($this->configurationManager);
    }


    /**
     * List View Backend
     *
     * @return void
     */
    public function listAction()
    {
        try {
            $connection = GeneralUtility::makeInstance(\Mahu\SearchAlgolia\Connection\Algolia\Connection::class, $this->configManager);
            $this->client = $connection->getClient();
            $indexList = $this->getIndexList();

        } catch (AlgoliaConnectionException $e) {
            $this->view->assignMultiple(['algoliaException' => $e->getMessage()]);
        }


        $this->view->assignMultiple(
            [
                'indexList' => $indexList
            ]
        );
    }

    protected function getIndexList()
    {
        return $indexList = $this->client->listIndexes()['items'];
    }


}

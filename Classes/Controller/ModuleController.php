<?php
namespace Mahu\SearchAlgolia\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use AlgoliaSearch\AlgoliaConnectionException;
use Codappix\SearchCore\Domain\Index\IndexerFactory;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Mahu\SearchAlgolia\Service\Log;

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

    /**
     * @var \AlgoliaSearch\Client
     */
    protected $client;

    protected $logService;


    /**
     * Initialize configurationManager, which holds all settings of search_core Typoscript config.
     */
    public function initializeAction()
    {
        $this->configManager = GeneralUtility::makeInstance(\Codappix\SearchCore\Configuration\ConfigurationContainer::class);
        $this->configManager->injectConfigurationManager($this->configurationManager);
    }


    /**
     * Shows Algolia Status, a list of all remote Indexes and all indexes which are configured via Typoscript Plugin config.
     */
    public function listAction()
    {
        try {
            $connection = GeneralUtility::makeInstance(\Mahu\SearchAlgolia\Connection\Algolia\Connection::class, $this->configManager);
            $this->client = $connection->getClient();
            $remoteIndexList = $this->getRemoteIndexList();
            $localIndexList = $this->getLocalIndexList();

            $this->logService = GeneralUtility::makeInstance(Log::class);
            $logContent = $this->logService->getLogContent();
        } catch (AlgoliaConnectionException $e) {
            $this->view->assignMultiple(['algoliaException' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            $this->view->assignMultiple(['logException' => $e->getMessage()]);
        }

        $this->view->assignMultiple(
            [
                'remoteIndexList' => $remoteIndexList,
                'localIndexList' => $localIndexList,
                'logContent' => $logContent
            ]
        );
    }

    /**
     * Reindexes all records of given tableName which are present in the Database
     */
    public function triggerReIndexingAction()
    {
        if ($this->request->hasArgument('indexName') && $this->request->hasArgument('tableName')) {
            try {
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class)
                    ->get(IndexerFactory::class)
                    ->getIndexer($this->request->getArgument('tableName'))
                    ->indexAllDocuments();

                $this->addFlashMessage(
                    'Successfully reindexed ' . $this->request->getArgument('indexName'),
                    $messageTitle = 'Algolia Status',
                    $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK,
                    $storeInSession = TRUE
                );
            } catch (AlgoliaConnectionException $e) {
                $this->addFlashMessage(
                    $e->getMessage(),
                    $messageTitle = 'Algolia Exception',
                    $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                    $storeInSession = TRUE
                );
            }
        }

        $this->redirect('list');
    }

    /**
     * Fetches all indexes which are configured via Typoscript
     * Eg. plugin.tx_searchcore.indexing
     *
     * @return array
     */
    protected function getLocalIndexList()
    {
        return $this->configManager->get('indexing');
    }

    /**
     * Fetches all available indexes from Algolia remote Server
     *
     * @return array
     */
    protected function getRemoteIndexList()
    {
        return $indexList = $this->client->listIndexes()['items'];
    }


}

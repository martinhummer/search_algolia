<?php
namespace Mahu\SearchAlgolia\Controller;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
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


    /**
     *
     */
    public function initializeAction() {
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


        try{
            $connection = GeneralUtility::makeInstance(\Mahu\SearchAlgolia\Connection\Algolia\Connection::class, $this->configManager);
            $connection->getClient()->listIndexes();
        } catch (AlgoliaConnectionException $e) {
            debug($e);
        }


        $this->view->assignMultiple(
            [

            ]
        );
    }

    protected function getConnectionConfiguration(){
        if (isset($this->settings['connections']['algolia']['applicationID']) && $this->settings['connections']['algolia']['apiKey']) {

        } else {
            throw new InvalidArgumentException(
                'The configuration for Algolia does not exist.',
                InvalidArgumentException::OPTION_DOES_NOT_EXIST
            );
        }
    }



}

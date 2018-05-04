<?php
namespace Mahu\SearchAlgolia\Domain\Index\TcaIndexer;

use Codappix\SearchCore\Domain\Index\TcaIndexer\PagesIndexer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use Codappix\SearchCore\Connection\ConnectionInterface;
use Codappix\SearchCore\Domain\Index\TcaIndexer;
use Codappix\SearchCore\Domain\Index\TcaIndexer\TcaTableService;
use Codappix\SearchCore\Domain\Index\TcaIndexer\TcaTableServiceInterface;

/**
 * PagesLanguageOverlayIndexer which extends the original PagesIndexer of search_core
 */
class PagesLanguageOverlayIndexer extends PagesIndexer
{
    /**
     * Fetch translated tt_content elements
     * This is strict and does no content_fallback
     *
     * @param int $uid of pagesLanguageOverlay record
     * @return array of content elements
     */
    protected function fetchContentForPage(int $uid) : array
    {
        //current pagesLanguageOverlay record
        $pagesLanguageOverlayRecord = $this->getRecord($uid);

        $objManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        //get the service for the table pages
        //it's important to get the instance via ObjectManager to make DI work
        $pagesTcaTableService = $objManager->get(TcaTableService::class, 'pages');

        //get the page record in default language
        $page = $pagesTcaTableService->getRecord($pagesLanguageOverlayRecord['pid']);

        if ($this->contentTableService instanceof TcaTableService) {
            $queryBuilder = $this->contentTableService->getQuery();
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.pid',
                    $queryBuilder->createNamedParameter($page['uid'], \PDO::PARAM_INT)
                ),
                //add constraint to only get results with sys_language_uid = 1
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.sys_language_uid',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                )
            );
            $contentElements = $queryBuilder->execute()->fetchAll();
        }

        if ($contentElements === null) {
            $this->logger->debug('No content for page ' . $uid);
            return [];
        }

        $this->logger->debug('Fetched content for page ' . $uid);
        $images = [];
        $content = [];
        foreach ($contentElements as $contentElement) {
            $images = array_merge(
                $images,
                $this->getContentElementImages($contentElement['uid'])
            );
            $content[] = $this->getContentFromContentElement($contentElement);
        }

        return [
            // Remove Tags.
            // Interpret escaped new lines and special chars.
            // Trim, e.g. trailing or leading new lines.
            'content' => trim(stripcslashes(strip_tags(implode(' ', $content)))),
            'images' => $images,
        ];
    }



}

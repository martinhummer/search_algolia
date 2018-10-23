<?php
namespace Mahu\SearchAlgolia\Domain\Index\TcaIndexer;

use Codappix\SearchCore\Configuration\ConfigurationContainerInterface;
use Codappix\SearchCore\Connection\ConnectionInterface;
use Codappix\SearchCore\Domain\Index\TcaIndexer;
use Codappix\SearchCore\Domain\Index\TcaIndexer\TcaTableService;
use Codappix\SearchCore\Domain\Index\TcaIndexer\PagesIndexer as SearchCorePagesIndexer;

/**
 * PagesIndexer which extends the original PagesIndexer of search_core
 *
 */
class PagesIndexer extends SearchCorePagesIndexer
{

    /**
     * Fetches tt_content records corresponding to page record
     * Takes language handling into account, which is the main difference to search_core implementation
     *
     * @param int page $uid
     * @return array
     */
    protected function fetchContentForPage(int $uid) : array
    {
        $page = $this->getRecord($uid);

        if ($this->contentTableService instanceof TcaTableService) {
            $queryBuilder = $this->contentTableService->getQuery();
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.pid',
                    //if l10n_parent is set, this page is translated and l10n_parent is set to the non translated page uid
                    //tt_content pid is always pointing to the uid of the non translated page uid
                    $queryBuilder->createNamedParameter($page['l10n_parent'] ? (int)$page['l10n_parent'] : $uid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.sys_language_uid',
                    $queryBuilder->createNamedParameter((int)$page['sys_language_uid'], \PDO::PARAM_INT)
                )
            );
            $contentElements = $queryBuilder->execute()->fetchAll();
        } else {
            $contentElements = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                $this->contentTableService->getFields(),
                $this->contentTableService->getTableClause(),
                $this->contentTableService->getWhereClause() .
                sprintf(' AND %s.pid = %u', $this->contentTableService->getTableName(), $uid)
            );
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

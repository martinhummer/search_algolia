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
     * Because of language handling we need to add a constraint (sys_language_uid = 0)
     * If this constraint is missing, the PagesIndexer would index all available tt_content elements (also translalted ones witch sys_language_uid != 0)
     *
     * We can not set this constraint via typocsript configurtion for tt_content because this would also affect PagesLanguageOverlayIndexer
     *
     * @param int $uid
     * @return array
     */
    protected function fetchContentForPage(int $uid) : array
    {
        if ($this->contentTableService instanceof TcaTableService) {
            $queryBuilder = $this->contentTableService->getQuery();
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.pid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ),
                //constraint added for language handling, this is the only part which is modified in PagesIndexer
                $queryBuilder->expr()->eq(
                    $this->contentTableService->getTableName() . '.sys_language_uid',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
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

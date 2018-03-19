<?php

namespace Mahu\SearchAlgolia\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Frontend\Page\PageRepository;
use Codappix\SearchCore\Domain\Index\TcaIndexer\InvalidArgumentException;

class Log
{

    protected $logFile;
    protected $tableName;

    /**
     * @throws RuntimeException
     *
     * Log constructor.
     */
    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['LOG']['Mahu']['SearchAlgolia']['writerConfiguration'][6]['TYPO3\CMS\Core\Log\Writer\FileWriter']['logFile'])) {
            $this->logFile = $GLOBALS['TYPO3_CONF_VARS']['LOG']['Mahu']['SearchAlgolia']['writerConfiguration'][6]['TYPO3\CMS\Core\Log\Writer\FileWriter']['logFile'];
        } else {
            throw new \RuntimeException('$GLOBALS[\'TYPO3_CONF_VARS\'][\'LOG\'][\'Mahu\'][\'SearchAlgolia\'][\'writerConfiguration\'][6][\'TYPO3\CMS\Core\Log\Writer\FileWriter\'][\'logFile\'] could not be found');
        }
    }

    /**
     * @return array
     */
    public function getLogContent()
    {
        return array_reverse(explode("\n", GeneralUtility::getUrl(PATH_site . $this->logFile)));
    }

}

<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteRepository
{

    private function createQueryBuilder()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_site');
    }

    /**
     * @param $hostname
     * @return array A list of site records that were found
     */
    public function findByHostname($hostname)
    {
        $queryBuilder = $this->createQueryBuilder();

        $res = $queryBuilder
            ->select('*')
            ->from('sys_site')
            ->where($queryBuilder->expr()->eq('domainName', $queryBuilder->createNamedParameter($hostname, \PDO::PARAM_STR)))
            ->execute();

        return $res->fetchAll();
    }

    public function findByLanguage($languageId)
    {
        $queryBuilder = $this->createQueryBuilder();

        $res = $queryBuilder
            ->select('*')
            ->from('sys_site')
            ->where($queryBuilder->expr()->eq('language', $queryBuilder->createNamedParameter($languageId, \PDO::PARAM_INT)))
            ->execute();

        $sites = [];
        while ($row = $res->fetch()) {
            $sites[$row['uid']] = $row;
        }

        return $sites;
    }
}

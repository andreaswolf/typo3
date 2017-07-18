<?php
declare(strict_types=1);
namespace TYPO3\CMS\Frontend\Routing;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UrlRepository
{

    private function createQueryBuilder()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_url');
    }

    public function findBySiteAndPath(int $siteId, string $path)
    {
        $queryBuilder = $this->createQueryBuilder();

        $res = $queryBuilder
            ->select('*')
            ->from('sys_url')
            ->where($queryBuilder->expr()->eq('site', $siteId))
            ->andWhere($queryBuilder->expr()->eq('path', $queryBuilder->createNamedParameter($path)))
            ->execute();

        return $res->fetchAll();
    }

    public function findByTarget($targetType, $targetUid)
    {
        $queryBuilder = $this->createQueryBuilder();

        $res = $queryBuilder
            ->select('*')
            ->from('sys_url')
            ->where($queryBuilder->expr()->eq('target_type', $queryBuilder->createNamedParameter($targetType)))
            ->andWhere($queryBuilder->expr()->eq('target_uid', $queryBuilder->createNamedParameter($targetUid)))
            ->execute();

        return $res->fetchAll();
    }

}

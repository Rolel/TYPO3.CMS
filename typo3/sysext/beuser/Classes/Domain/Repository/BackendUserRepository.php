<?php
namespace TYPO3\CMS\Beuser\Domain\Repository;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for \TYPO3\CMS\Beuser\Domain\Model\BackendUser
 */
class BackendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository
{
    /**
     * Finds Backend Users on a given list of uids
     *
     * @param array $uidList
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult<\TYPO3\CMS\Beuser\Domain\Model\BackendUser>
     */
    public function findByUidList(array $uidList)
    {
        $query = $this->createQuery();
        return $query->matching($query->in('uid', array_map('intval', $uidList)))->execute();
    }

    /**
     * Find Backend Users matching to Demand object properties
     *
     * @param \TYPO3\CMS\Beuser\Domain\Model\Demand $demand
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult<\TYPO3\CMS\Beuser\Domain\Model\BackendUser>
     */
    public function findDemanded(\TYPO3\CMS\Beuser\Domain\Model\Demand $demand)
    {
        $constraints = [];
        $query = $this->createQuery();
        // Find invisible as well, but not deleted
        $constraints[] = $query->equals('deleted', 0);
        $query->setOrderings(['userName' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        // Username
        if ($demand->getUserName() !== '') {
            $searchConstraints = [];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');
            foreach (['userName', 'uid', 'realName'] as $field) {
                $searchConstraints[] = $query->like(
                    $field, '%' . $queryBuilder->escapeLikeWildcards($demand->getUserName()) . '%'
                );
            }
            $constraints[] = $query->logicalOr($searchConstraints);
        }
        // Only display admin users
        if ($demand->getUserType() == \TYPO3\CMS\Beuser\Domain\Model\Demand::USERTYPE_ADMINONLY) {
            $constraints[] = $query->equals('admin', 1);
        }
        // Only display non-admin users
        if ($demand->getUserType() == \TYPO3\CMS\Beuser\Domain\Model\Demand::USERTYPE_USERONLY) {
            $constraints[] = $query->equals('admin', 0);
        }
        // Only display active users
        if ($demand->getStatus() == \TYPO3\CMS\Beuser\Domain\Model\Demand::STATUS_ACTIVE) {
            $constraints[] = $query->equals('disable', 0);
        }
        // Only display in-active users
        if ($demand->getStatus() == \TYPO3\CMS\Beuser\Domain\Model\Demand::STATUS_INACTIVE) {
            $constraints[] = $query->logicalOr($query->equals('disable', 1));
        }
        // Not logged in before
        if ($demand->getLogins() == \TYPO3\CMS\Beuser\Domain\Model\Demand::LOGIN_NONE) {
            $constraints[] = $query->equals('lastlogin', 0);
        }
        // At least one login
        if ($demand->getLogins() == \TYPO3\CMS\Beuser\Domain\Model\Demand::LOGIN_SOME) {
            $constraints[] = $query->logicalNot($query->equals('lastlogin', 0));
        }
        // In backend user group
        // @TODO: Refactor for real n:m relations
        if ($demand->getBackendUserGroup()) {
            $constraints[] = $query->logicalOr(
                $query->equals('usergroup', (int)$demand->getBackendUserGroup()->getUid()),
                $query->like('usergroup', (int)$demand->getBackendUserGroup()->getUid() . ',%'),
                $query->like('usergroup', '%,' . (int)$demand->getBackendUserGroup()->getUid()),
                $query->like('usergroup', '%,' . (int)$demand->getBackendUserGroup()->getUid() . ',%')
            );
            $query->contains('usergroup', $demand->getBackendUserGroup());
        }
        $query->matching($query->logicalAnd($constraints));
        return $query->execute();
    }

    /**
     * Find Backend Users currently online
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult<\TYPO3\CMS\Beuser\Domain\Model\BackendUser>
     */
    public function findOnline()
    {
        $uids = [];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_sessions');

        $res = $queryBuilder
            ->select('ses_userid')
            ->from('be_sessions')
            ->groupBy('ses_userid')
            ->execute();

        while ($row = $res->fetch()) {
            $uids[] = $row['ses_userid'];
        }

        $query = $this->createQuery();
        $query->matching($query->in('uid', $uids));
        return $query->execute();
    }

    /**
     * Overwrite createQuery to don't respect enable fields
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setIncludeDeleted(true);
        return $query;
    }
}

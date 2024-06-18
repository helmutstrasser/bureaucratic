<?php
declare(strict_types=1);

namespace JosefGlatz\Bureaucratic\Services;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BackendUserService class for interacting with be_users table
 */
class BackendUserService
{
    protected $connection;

    public function __construct(
        Connection $connection
    )
    {
        $this->connection = $connection;
    }

    /**
     * Return all rows of disabled backend users.
     *
     * Optionally with a specific tstamp time range and or specific groups.
     *
     * @param int $daysNotModified
     * @param mixed $group
     * @return \Doctrine\DBAL\Result
     */
    public function getLongLastingDisabledUsers(int $daysNotModified = 0, mixed $group = '')
    {
        // Calculate notModified timespan
        $now = time();
        $daysNotModifiedTimestamp = $now - ($daysNotModified * 86400); // 86400sec === 1day

        // Create select statement
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from('be_users')
            // select only disabled users
            // and select only records not modified since n number of days
            ->where(
                $queryBuilder->expr()->eq(
                    $this->getDisabledColumnName(),
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->lt(
                    'tstamp',
                    $queryBuilder->createNamedParameter($daysNotModifiedTimestamp, \PDO::PARAM_INT)
                )
            );

        // select by group(s)
        if (trim($group) !== '' || $group !== 0) {
            // limit records to a specific group uid
            if (ctype_digit($group)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->inSet('usergroup', $group)
                );
            }
            // limit records by given string
            if (!ctype_digit($group)) {
                $group = str_replace('"', '', $group);
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        'usergroup',
                        $queryBuilder->createNamedParameter($group, \PDO::PARAM_STR)
                    )
                );
            }
        }

        return $queryBuilder->executeQuery();
    }

    /**
     * Delete a user by UID without removing database row
     *
     * @param int $userId Unique ID of record to delete
     * @return int Amount of affected records (should be only 1)
     */
    public function deleteUserByUid($userId): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('be_users')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($userId, \PDO::PARAM_INT)
                )
            )
            ->set($this->getDeletedColumnName(), $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT))
            ->set('tstamp', $queryBuilder->createNamedParameter(time(), \PDO::PARAM_INT));

        return $queryBuilder->executeStatement();
    }

    public function getDisabledColumnName(): string
    {
        return $GLOBALS['TCA']['be_users']['ctrl']['enablecolumns']['disabled'];
    }

    public function getDeletedColumnName(): string
    {
        return $GLOBALS['TCA']['be_users']['ctrl']['delete'];
    }


}
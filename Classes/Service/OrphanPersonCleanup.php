<?php

namespace CPSIT\T3eventsReservation\Service;

use CPSIT\T3eventsReservation\Domain\Model\Person;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Raw SQL on purpose: "live" means deleted = 0 only, hidden/starttime/endtime do not matter.
 */
class OrphanPersonCleanup
{
    private const TABLE_RESERVATION = 'tx_t3eventsreservation_domain_model_reservation';
    private const TABLE_PERSON = 'tx_t3events_domain_model_person';
    private const TABLE_PARTICIPANTS_MM = 'tx_t3eventsreservation_reservation_participants_person_mm';

    private const GROUP_A = '(p.reservation > 0 AND (r.uid IS NULL OR r.deleted = 1))';
    private const GROUP_B = '(p.reservation = 0 AND p.tx_extbase_type <> :participant
        AND NOT EXISTS (SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r1 WHERE r1.contact = p.uid AND r1.deleted = 0)
        AND NOT EXISTS (SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r2 WHERE r2.billing_address = p.uid AND r2.deleted = 0))';

    private ConnectionPool $connectionPool;

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param int[] $pids
     * @return array{groupA: int, groupB: int}
     */
    public function countOrphans(array $pids): array
    {
        $this->assertPids($pids);
        $from = ' FROM ' . self::TABLE_PERSON . ' p
            LEFT JOIN ' . self::TABLE_RESERVATION . ' r ON r.uid = p.reservation';

        $counts = [];
        foreach (['groupA' => self::GROUP_A, 'groupB' => self::GROUP_B] as $group => $condition) {
            $counts[$group] = (int)$this->getConnection()->executeQuery(
                'SELECT COUNT(*)' . $from . $this->whereClause($condition),
                $this->parameters($pids),
                $this->types()
            )->fetchOne();
        }

        return $counts;
    }

    /**
     * @param int[] $pids
     */
    public function softDeleteOrphans(array $pids): int
    {
        $this->assertPids($pids);

        return (int)$this->getConnection()->executeStatement(
            'UPDATE ' . self::TABLE_PERSON . ' p
            LEFT JOIN ' . self::TABLE_RESERVATION . ' r ON r.uid = p.reservation
            SET p.deleted = 1, p.tstamp = :now'
            . $this->whereClause('(' . self::GROUP_A . ' OR ' . self::GROUP_B . ')'),
            $this->parameters($pids) + ['now' => time()],
            $this->types() + ['now' => \PDO::PARAM_INT]
        );
    }

    private function whereClause(string $orphanCondition): string
    {
        return '
            WHERE p.deleted = 0 AND p.pid IN (:pids)
              AND p.tx_extbase_type IN (:types)
              AND ' . $orphanCondition . '
              AND NOT EXISTS (SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r3
                  WHERE r3.deleted = 0 AND (r3.contact = p.uid OR r3.billing_address = p.uid))
              AND NOT EXISTS (SELECT 1 FROM ' . self::TABLE_PARTICIPANTS_MM . ' mm
                  JOIN ' . self::TABLE_RESERVATION . ' r4 ON r4.uid = mm.uid_local AND r4.deleted = 0
                  WHERE mm.uid_foreign = p.uid)';
    }

    private function parameters(array $pids): array
    {
        return [
            'pids' => array_map('intval', $pids),
            'types' => ReservationPersonResolver::PERSON_TYPES,
            'participant' => Person::PERSON_TYPE_PARTICIPANT,
        ];
    }

    private function types(): array
    {
        return [
            'pids' => Connection::PARAM_INT_ARRAY,
            'types' => Connection::PARAM_STR_ARRAY,
            'participant' => Connection::PARAM_STR,
        ];
    }

    private function assertPids(array $pids): void
    {
        if ($pids === []) {
            throw new \InvalidArgumentException('At least one pid is required.', 1762675200);
        }
    }

    private function getConnection(): Connection
    {
        return $this->connectionPool->getConnectionForTable(self::TABLE_PERSON);
    }
}

<?php

namespace CPSIT\T3eventsReservation\Service;

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use DWenzel\T3events\Domain\Model\Person as EventsPerson;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Raw SQL on purpose: "live" means deleted = 0 only, hidden/starttime/endtime do not matter.
 */
class ReservationPersonResolver
{
    public const PERSON_TYPES = [
        Person::PERSON_TYPE_PARTICIPANT,
        EventsPerson::PERSON_TYPE_CONTACT,
        BillingAddress::PERSON_TYPE_BILLING_ADDRESS,
    ];

    private const TABLE_RESERVATION = 'tx_t3eventsreservation_domain_model_reservation';
    private const TABLE_PERSON = 'tx_t3events_domain_model_person';
    private const TABLE_PARTICIPANTS_MM = 'tx_t3eventsreservation_reservation_participants_person_mm';

    private ConnectionPool $connectionPool;

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    public function isReservationLive(int $reservationUid): bool
    {
        return (bool)$this->getConnection()->executeQuery(
            'SELECT 1 FROM ' . self::TABLE_RESERVATION . ' WHERE uid = :uid AND deleted = 0',
            ['uid' => $reservationUid],
            ['uid' => Connection::PARAM_INT]
        )->fetchOne();
    }

    /**
     * @return int[]
     */
    public function collectPersonUids(int $reservationUid, array $reservationRow): array
    {
        $connection = $this->getConnection();
        $mmUids = $connection->executeQuery(
            'SELECT uid_foreign FROM ' . self::TABLE_PARTICIPANTS_MM . ' WHERE uid_local = :uid',
            ['uid' => $reservationUid],
            ['uid' => Connection::PARAM_INT]
        )->fetchFirstColumn();
        $ownFieldUids = $connection->executeQuery(
            'SELECT uid FROM ' . self::TABLE_PERSON . ' WHERE reservation = :uid',
            ['uid' => $reservationUid],
            ['uid' => Connection::PARAM_INT]
        )->fetchFirstColumn();

        $uids = array_merge(
            $mmUids,
            $ownFieldUids,
            [$reservationRow['contact'] ?? 0, $reservationRow['billing_address'] ?? 0]
        );

        return array_values(array_unique(array_filter(array_map('intval', $uids))));
    }

    /**
     * Separate NOT EXISTS per column: MariaDB 10.2 does not index-merge an OR inside a correlated subquery.
     *
     * @param int[] $personUids
     * @return int[]
     */
    public function filterDeletable(array $personUids, int $reservationUid): array
    {
        $personUids = array_values(array_unique(array_filter(array_map('intval', $personUids))));
        if ($personUids === []) {
            return [];
        }

        $sql = 'SELECT p.uid FROM ' . self::TABLE_PERSON . ' p
            WHERE p.uid IN (:uids)
              AND p.deleted = 0
              AND p.tx_extbase_type IN (:types)
              AND NOT EXISTS (
                  SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r1
                  WHERE r1.contact = p.uid AND r1.deleted = 0 AND r1.uid <> :reservation
              )
              AND NOT EXISTS (
                  SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r2
                  WHERE r2.billing_address = p.uid AND r2.deleted = 0 AND r2.uid <> :reservation
              )
              AND NOT EXISTS (
                  SELECT 1 FROM ' . self::TABLE_PARTICIPANTS_MM . ' mm
                  JOIN ' . self::TABLE_RESERVATION . ' r3 ON r3.uid = mm.uid_local AND r3.deleted = 0
                  WHERE mm.uid_foreign = p.uid AND mm.uid_local <> :reservation
              )
              AND NOT EXISTS (
                  SELECT 1 FROM ' . self::TABLE_RESERVATION . ' r4
                  WHERE r4.uid = p.reservation AND r4.deleted = 0 AND r4.uid <> :reservation
              )';

        $rows = $this->getConnection()->executeQuery(
            $sql,
            ['uids' => $personUids, 'types' => self::PERSON_TYPES, 'reservation' => $reservationUid],
            ['uids' => Connection::PARAM_INT_ARRAY, 'types' => Connection::PARAM_STR_ARRAY, 'reservation' => Connection::PARAM_INT]
        )->fetchFirstColumn();

        return array_map('intval', $rows);
    }

    private function getConnection(): Connection
    {
        return $this->connectionPool->getConnectionForTable(self::TABLE_PERSON);
    }
}

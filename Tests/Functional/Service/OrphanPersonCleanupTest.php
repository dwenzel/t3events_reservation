<?php

declare(strict_types=1);

namespace CPSIT\T3eventsReservation\Tests\Functional\Service;

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Service\OrphanPersonCleanup;
use DWenzel\T3events\Domain\Model\Person as EventsPerson;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

class OrphanPersonCleanupTest extends FunctionalTestCase
{
    private const PID = 4086;
    private const OTHER_PID = 4087;
    private const FOREIGN_PID = 999;
    private const TABLE_RESERVATION = 'tx_t3eventsreservation_domain_model_reservation';
    private const TABLE_PERSON = 'tx_t3events_domain_model_person';
    private const TABLE_MM = 'tx_t3eventsreservation_reservation_participants_person_mm';
    private const LIVE_RESERVATION = 10;
    private const DELETED_RESERVATION = 20;
    private const MISSING_RESERVATION = 30;
    private const OLD_TSTAMP = 1000;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/t3extension_tools',
        'typo3conf/ext/t3events',
        'typo3conf/ext/t3events_course',
        'typo3conf/ext/t3events_reservation',
    ];

    private function subject(): OrphanPersonCleanup
    {
        return new OrphanPersonCleanup();
    }

    /**
     * @test
     */
    public function softDeleteOrphansSoftDeletesPersonWhoseReservationIsMissing(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::MISSING_RESERVATION]);

        self::assertSame(1, $this->subject()->softDeleteOrphans([self::PID]));
        self::assertSame(1, $this->fetchPerson(1)['deleted']);
    }

    /**
     * @test
     */
    public function softDeleteOrphansSoftDeletesPersonWhoseReservationIsDeleted(): void
    {
        $this->insertReservation(self::DELETED_RESERVATION, ['deleted' => 1]);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT, ['reservation' => self::DELETED_RESERVATION]);

        self::assertSame(1, $this->subject()->softDeleteOrphans([self::PID]));
        $row = $this->fetchPerson(1);
        self::assertSame(1, $row['deleted']);
        self::assertGreaterThan(self::OLD_TSTAMP, $row['tstamp']);
    }

    /**
     * @test
     */
    public function softDeleteOrphansSoftDeletesUnreferencedContactAndBillingAddress(): void
    {
        $this->insertReservation(self::DELETED_RESERVATION, ['deleted' => 1, 'contact' => 3, 'billing_address' => 4]);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT);
        $this->insertPerson(2, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);
        $this->insertPerson(3, EventsPerson::PERSON_TYPE_CONTACT);
        $this->insertPerson(4, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);

        self::assertSame(4, $this->subject()->softDeleteOrphans([self::PID]));
        foreach ([1, 2, 3, 4] as $uid) {
            self::assertSame(1, $this->fetchPerson($uid)['deleted'], 'person ' . $uid);
        }
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesParticipantWithUnsetReservationUntouched(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        $this->assertPersonUntouched(1);
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesPersonsOfLiveReservationUntouched(): void
    {
        $this->insertReservation(self::LIVE_RESERVATION, ['contact' => 1, 'billing_address' => 2]);
        $this->insertMm(self::LIVE_RESERVATION, 3);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT, ['reservation' => self::LIVE_RESERVATION]);
        $this->insertPerson(2, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);
        $this->insertPerson(3, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::LIVE_RESERVATION]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        foreach ([1, 2, 3] as $uid) {
            $this->assertPersonUntouched($uid);
        }
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesSharedPersonUntouched(): void
    {
        $this->insertReservation(self::DELETED_RESERVATION, ['deleted' => 1]);
        $this->insertReservation(self::LIVE_RESERVATION, ['contact' => 1]);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT, ['reservation' => self::DELETED_RESERVATION]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        $this->assertPersonUntouched(1);
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesSharedMmParticipantUntouched(): void
    {
        $this->insertReservation(self::DELETED_RESERVATION, ['deleted' => 1]);
        $this->insertReservation(self::LIVE_RESERVATION);
        $this->insertMm(self::LIVE_RESERVATION, 1);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::DELETED_RESERVATION]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        $this->assertPersonUntouched(1);
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesRowsOutsideConfiguredPidsUntouched(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, [
            'pid' => self::FOREIGN_PID,
            'reservation' => self::MISSING_RESERVATION,
        ]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        $this->assertPersonUntouched(1);
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesOtherPersonTypesUntouched(): void
    {
        $this->insertPerson(1, 'Tx_IhkofEvents_Partner', ['reservation' => self::MISSING_RESERVATION]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        $this->assertPersonUntouched(1);
    }

    /**
     * @test
     */
    public function softDeleteOrphansLeavesAlreadyDeletedRowsUntouched(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, [
            'deleted' => 1,
            'reservation' => self::MISSING_RESERVATION,
        ]);

        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
        self::assertSame(self::OLD_TSTAMP, $this->fetchPerson(1)['tstamp']);
    }

    /**
     * @test
     */
    public function softDeleteOrphansIncludesHiddenPersons(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, [
            'hidden' => 1,
            'reservation' => self::MISSING_RESERVATION,
        ]);

        self::assertSame(1, $this->subject()->softDeleteOrphans([self::PID]));
        self::assertSame(1, $this->fetchPerson(1)['deleted']);
    }

    /**
     * @test
     */
    public function softDeleteOrphansIsIdempotent(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::MISSING_RESERVATION]);
        $this->insertPerson(2, EventsPerson::PERSON_TYPE_CONTACT);

        self::assertSame(2, $this->subject()->softDeleteOrphans([self::PID]));
        self::assertSame(0, $this->subject()->softDeleteOrphans([self::PID]));
    }

    /**
     * @test
     */
    public function softDeleteOrphansHonoursMultiplePids(): void
    {
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::MISSING_RESERVATION]);
        $this->insertPerson(2, Person::PERSON_TYPE_PARTICIPANT, [
            'pid' => self::OTHER_PID,
            'reservation' => self::MISSING_RESERVATION,
        ]);

        self::assertSame(2, $this->subject()->softDeleteOrphans([self::PID, self::OTHER_PID]));
        self::assertSame(1, $this->fetchPerson(1)['deleted']);
        self::assertSame(1, $this->fetchPerson(2)['deleted']);
    }

    /**
     * @test
     */
    public function countOrphansReportsGroupsSeparatelyAndDoesNotWrite(): void
    {
        $this->insertReservation(self::DELETED_RESERVATION, ['deleted' => 1]);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::MISSING_RESERVATION]);
        $this->insertPerson(2, EventsPerson::PERSON_TYPE_CONTACT, ['reservation' => self::DELETED_RESERVATION]);
        $this->insertPerson(3, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);

        self::assertSame(['groupA' => 2, 'groupB' => 1], $this->subject()->countOrphans([self::PID]));
        foreach ([1, 2, 3] as $uid) {
            $this->assertPersonUntouched($uid);
        }
    }

    /**
     * @test
     */
    public function countOrphansRejectsEmptyPidList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subject()->countOrphans([]);
    }

    /**
     * @test
     */
    public function softDeleteOrphansRejectsEmptyPidList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subject()->softDeleteOrphans([]);
    }

    private function assertPersonUntouched(int $uid): void
    {
        $row = $this->fetchPerson($uid);
        self::assertSame(0, $row['deleted'], 'person ' . $uid . ' deleted');
        self::assertSame(self::OLD_TSTAMP, $row['tstamp'], 'person ' . $uid . ' tstamp');
    }

    private function fetchPerson(int $uid): array
    {
        $row = $this->getConnectionPool()->getConnectionForTable(self::TABLE_PERSON)
            ->executeQuery('SELECT deleted, tstamp FROM ' . self::TABLE_PERSON . ' WHERE uid = ?', [$uid])
            ->fetchAssociative();

        return array_map('intval', $row);
    }

    private function insertReservation(int $uid, array $fields = []): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE_RESERVATION)->insert(
            self::TABLE_RESERVATION,
            array_merge(['uid' => $uid, 'pid' => self::PID, 'deleted' => 0, 'hidden' => 0], $fields)
        );
    }

    private function insertPerson(int $uid, string $type, array $fields = []): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE_PERSON)->insert(
            self::TABLE_PERSON,
            array_merge(
                [
                    'uid' => $uid,
                    'pid' => self::PID,
                    'deleted' => 0,
                    'hidden' => 0,
                    'tstamp' => self::OLD_TSTAMP,
                    'tx_extbase_type' => $type,
                ],
                $fields
            )
        );
    }

    private function insertMm(int $reservationUid, int $personUid): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE_MM)->insert(
            self::TABLE_MM,
            ['uid_local' => $reservationUid, 'uid_foreign' => $personUid]
        );
    }
}

<?php

declare(strict_types=1);

namespace CPSIT\T3eventsReservation\Tests\Functional\Service;

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Service\ReservationPersonResolver;
use DWenzel\T3events\Domain\Model\Person as EventsPerson;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

class ReservationPersonResolverTest extends FunctionalTestCase
{
    private const PID = 4086;
    private const TABLE_RESERVATION = 'tx_t3eventsreservation_domain_model_reservation';
    private const TABLE_PERSON = 'tx_t3events_domain_model_person';
    private const TABLE_MM = 'tx_t3eventsreservation_reservation_participants_person_mm';
    private const RESERVATION = 10;
    private const OTHER_RESERVATION = 20;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/t3extension_tools',
        'typo3conf/ext/t3events',
        'typo3conf/ext/t3events_course',
        'typo3conf/ext/t3events_reservation',
    ];

    private function subject(): ReservationPersonResolver
    {
        return new ReservationPersonResolver();
    }

    /**
     * @test
     */
    public function isReservationLiveReturnsTrueForLiveAndFalseForDeletedOrMissing(): void
    {
        $this->insertReservation(self::RESERVATION, ['deleted' => 0]);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 1]);

        self::assertTrue($this->subject()->isReservationLive(self::RESERVATION));
        self::assertFalse($this->subject()->isReservationLive(self::OTHER_RESERVATION));
        self::assertFalse($this->subject()->isReservationLive(999));
    }

    /**
     * @test
     */
    public function isReservationLiveTreatsHiddenReservationAsLive(): void
    {
        $this->insertReservation(self::RESERVATION, ['deleted' => 0, 'hidden' => 1]);

        self::assertTrue($this->subject()->isReservationLive(self::RESERVATION));
    }

    /**
     * @test
     */
    public function collectPersonUidsReturnsMmParticipantsOwnReservationFieldContactAndBillingAddress(): void
    {
        $this->insertReservation(self::RESERVATION, ['contact' => 3, 'billing_address' => 4]);
        $this->insertMm(self::RESERVATION, 1);
        $this->insertMm(self::RESERVATION, 2);
        $this->insertMm(self::OTHER_RESERVATION, 5);
        $this->insertPerson(2, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::RESERVATION]);
        $this->insertPerson(6, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::OTHER_RESERVATION]);

        $result = $this->subject()->collectPersonUids(
            self::RESERVATION,
            ['contact' => '3', 'billing_address' => '4']
        );

        self::assertEqualsCanonicalizing([1, 2, 3, 4], $result);
        self::assertSame($result, array_values(array_unique($result)));

        $withoutContact = $this->subject()->collectPersonUids(
            self::RESERVATION,
            ['contact' => 0, 'billing_address' => '0']
        );
        self::assertEqualsCanonicalizing([1, 2], $withoutContact);
    }

    /**
     * @test
     */
    public function filterDeletableReturnsOnlyLiveReservationTypePersons(): void
    {
        $this->insertReservation(self::RESERVATION);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT);
        $this->insertPerson(2, EventsPerson::PERSON_TYPE_CONTACT);
        $this->insertPerson(3, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);
        $this->insertPerson(4, Person::PERSON_TYPE_PARTICIPANT, ['deleted' => 1]);
        $this->insertPerson(5, 'Tx_IhkofEvents_Partner');

        self::assertEqualsCanonicalizing(
            [1, 2, 3],
            $this->subject()->filterDeletable([1, 2, 3, 4, 5, 999], self::RESERVATION)
        );
    }

    /**
     * @test
     */
    public function filterDeletableReturnsHiddenOrphan(): void
    {
        $this->insertReservation(self::RESERVATION);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['hidden' => 1]);

        self::assertSame([1], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableKeepsPersonReferencedByOtherLiveReservationAsContact(): void
    {
        $this->insertReservation(self::RESERVATION, ['contact' => 1]);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 0, 'contact' => 1]);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT);

        self::assertSame([], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableKeepsPersonReferencedByOtherLiveReservationAsBillingAddress(): void
    {
        $this->insertReservation(self::RESERVATION, ['billing_address' => 1]);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 0, 'billing_address' => 1]);
        $this->insertPerson(1, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);

        self::assertSame([], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableKeepsPersonInMmOfOtherLiveReservation(): void
    {
        $this->insertReservation(self::RESERVATION);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 0]);
        $this->insertMm(self::RESERVATION, 1);
        $this->insertMm(self::OTHER_RESERVATION, 1);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT);

        self::assertSame([], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableKeepsPersonWhoseOwnReservationFieldPointsToOtherLiveReservation(): void
    {
        $this->insertReservation(self::RESERVATION);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 0]);
        $this->insertMm(self::RESERVATION, 1);
        $this->insertPerson(1, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::OTHER_RESERVATION]);

        self::assertSame([], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableKeepsPersonReferencedByHiddenLiveReservation(): void
    {
        $this->insertReservation(self::RESERVATION, ['contact' => 1]);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 0, 'hidden' => 1, 'contact' => 1]);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT);

        self::assertSame([], $this->subject()->filterDeletable([1], self::RESERVATION));
    }

    /**
     * @test
     */
    public function filterDeletableIgnoresReferencesFromTheGivenReservationAndFromSoftDeletedOnes(): void
    {
        $this->insertReservation(self::RESERVATION, ['deleted' => 0, 'contact' => 1, 'billing_address' => 2]);
        $this->insertReservation(self::OTHER_RESERVATION, ['deleted' => 1, 'contact' => 3, 'billing_address' => 2]);
        $this->insertMm(self::RESERVATION, 4);
        $this->insertMm(self::OTHER_RESERVATION, 4);
        $this->insertPerson(1, EventsPerson::PERSON_TYPE_CONTACT);
        $this->insertPerson(2, BillingAddress::PERSON_TYPE_BILLING_ADDRESS);
        $this->insertPerson(3, EventsPerson::PERSON_TYPE_CONTACT);
        $this->insertPerson(4, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::RESERVATION]);
        $this->insertPerson(5, Person::PERSON_TYPE_PARTICIPANT, ['reservation' => self::OTHER_RESERVATION]);

        self::assertEqualsCanonicalizing(
            [1, 2, 3, 4, 5],
            $this->subject()->filterDeletable([1, 2, 3, 4, 5], self::RESERVATION)
        );
    }

    /**
     * @test
     */
    public function filterDeletableReturnsEmptyArrayForEmptyInput(): void
    {
        self::assertSame([], $this->subject()->filterDeletable([], self::RESERVATION));
    }

    private function insertReservation(int $uid, array $fields = []): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE_RESERVATION)->insert(
            self::TABLE_RESERVATION,
            array_merge(['uid' => $uid, 'pid' => self::PID, 'deleted' => 1, 'hidden' => 0], $fields)
        );
    }

    private function insertPerson(int $uid, string $type, array $fields = []): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE_PERSON)->insert(
            self::TABLE_PERSON,
            array_merge(
                ['uid' => $uid, 'pid' => self::PID, 'deleted' => 0, 'hidden' => 0, 'tx_extbase_type' => $type],
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

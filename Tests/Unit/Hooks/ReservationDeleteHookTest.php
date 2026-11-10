<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Hooks;

use CPSIT\T3eventsReservation\Hooks\ReservationDeleteHook;
use CPSIT\T3eventsReservation\Service\ReservationPersonResolver;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class ReservationDeleteHookTest extends UnitTestCase
{
    private const RESERVATION_TABLE = 'tx_t3eventsreservation_domain_model_reservation';
    private const PERSON_TABLE = 'tx_t3events_domain_model_person';

    /** @var ReservationPersonResolver|MockObject */
    private $resolver;

    /** @var DataHandler|MockObject */
    private $dataHandler;

    /** @var ReservationDeleteHook */
    private $subject;

    protected function setUp(): void
    {
        $this->resolver = $this->getMockBuilder(ReservationPersonResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isReservationLive', 'collectPersonUids', 'filterDeletable'])
            ->getMock();
        $this->dataHandler = $this->getMockBuilder(DataHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteEl', 'deleteAction'])
            ->getMock();
        $this->subject = new ReservationDeleteHook($this->resolver);
    }

    /**
     * @test
     */
    public function ignoresOtherTables(): void
    {
        $wasDeleted = false;
        $this->dataHandler->expects(self::never())->method('deleteEl');
        $this->resolver->expects(self::never())->method('isReservationLive');

        $this->subject->processCmdmap_deleteAction(self::PERSON_TABLE, 42, [], $wasDeleted, $this->dataHandler);

        self::assertFalse($wasDeleted);
    }

    /**
     * @test
     */
    public function deletesReservationAndFlagsRecordAsDeleted(): void
    {
        $wasDeleted = false;
        $this->dataHandler->expects(self::once())->method('deleteEl')->with(self::RESERVATION_TABLE, 42);
        $this->resolver->method('isReservationLive')->willReturn(true);

        $this->subject->processCmdmap_deleteAction(self::RESERVATION_TABLE, 42, [], $wasDeleted, $this->dataHandler);

        self::assertTrue($wasDeleted);
    }

    /**
     * @test
     */
    public function castsStringIdToInt(): void
    {
        $wasDeleted = false;
        $row = ['uid' => 42, 'contact' => 3];
        $this->dataHandler->expects(self::once())->method('deleteEl')->with(self::RESERVATION_TABLE, 42);
        $this->resolver->expects(self::once())->method('isReservationLive')->with(42)->willReturn(false);
        $this->resolver->expects(self::once())->method('collectPersonUids')->with(42, $row)->willReturn([]);
        $this->resolver->method('filterDeletable')->willReturn([]);

        $this->subject->processCmdmap_deleteAction(self::RESERVATION_TABLE, '42', $row, $wasDeleted, $this->dataHandler);
    }

    /**
     * @test
     */
    public function stopsWhenReservationIsStillLive(): void
    {
        $wasDeleted = false;
        $this->resolver->method('isReservationLive')->with(42)->willReturn(true);
        $this->resolver->expects(self::never())->method('collectPersonUids');
        $this->dataHandler->expects(self::never())->method('deleteAction');

        $this->subject->processCmdmap_deleteAction(self::RESERVATION_TABLE, 42, [], $wasDeleted, $this->dataHandler);
    }

    /**
     * @test
     */
    public function deletesEveryDeletablePersonViaDataHandler(): void
    {
        $wasDeleted = false;
        $row = ['uid' => 42];
        $this->resolver->method('isReservationLive')->willReturn(false);
        $this->resolver->method('collectPersonUids')->with(42, $row)->willReturn([7, 8, 9]);
        $this->resolver->method('filterDeletable')->with([7, 8, 9], 42)->willReturn([7, 9]);
        $this->dataHandler->expects(self::exactly(2))->method('deleteAction')
            ->withConsecutive([self::PERSON_TABLE, 7], [self::PERSON_TABLE, 9]);

        $this->subject->processCmdmap_deleteAction(self::RESERVATION_TABLE, 42, $row, $wasDeleted, $this->dataHandler);
    }

    /**
     * @test
     */
    public function skipsDeleteElWhenAnotherHookAlreadyDeletedTheRecord(): void
    {
        $wasDeleted = true;
        $this->dataHandler->expects(self::never())->method('deleteEl');
        $this->resolver->method('isReservationLive')->willReturn(false);
        $this->resolver->expects(self::once())->method('collectPersonUids')->willReturn([]);
        $this->resolver->method('filterDeletable')->willReturn([]);

        $this->subject->processCmdmap_deleteAction(self::RESERVATION_TABLE, 42, [], $wasDeleted, $this->dataHandler);
    }
}

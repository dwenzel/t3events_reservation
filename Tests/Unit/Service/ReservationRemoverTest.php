<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Service;

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use CPSIT\T3eventsReservation\Service\ReservationPersonResolver;
use CPSIT\T3eventsReservation\Service\ReservationRemover;
use DWenzel\T3events\Domain\Repository\NotificationRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ReservationRemoverTest extends UnitTestCase
{
    /** @var PersonRepository|MockObject */
    private $personRepository;
    /** @var ContactRepository|MockObject */
    private $contactRepository;
    /** @var BillingAddressRepository|MockObject */
    private $billingAddressRepository;
    /** @var NotificationRepository|MockObject */
    private $notificationRepository;
    /** @var ReservationRepository|MockObject */
    private $reservationRepository;
    /** @var ReservationPersonResolver|MockObject */
    private $resolver;
    private ReservationRemover $subject;

    protected function setUp(): void
    {
        $this->personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()->onlyMethods(['remove'])->getMock();
        $this->contactRepository = $this->getMockBuilder(ContactRepository::class)
            ->disableOriginalConstructor()->onlyMethods(['remove'])->getMock();
        $this->billingAddressRepository = $this->getMockBuilder(BillingAddressRepository::class)
            ->disableOriginalConstructor()->onlyMethods(['remove'])->getMock();
        $this->notificationRepository = $this->getMockBuilder(NotificationRepository::class)
            ->disableOriginalConstructor()->onlyMethods(['remove'])->getMock();
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->disableOriginalConstructor()->onlyMethods(['remove'])->getMock();
        $this->resolver = $this->getMockBuilder(ReservationPersonResolver::class)
            ->disableOriginalConstructor()->onlyMethods(['filterDeletable'])->getMock();

        $this->subject = new ReservationRemover(
            $this->personRepository,
            $this->contactRepository,
            $this->billingAddressRepository,
            $this->notificationRepository,
            $this->reservationRepository,
            $this->resolver
        );
    }

    private function withUid($object, int $uid)
    {
        $object->_setProperty('uid', $uid);

        return $object;
    }

    private function buildReservation(): Reservation
    {
        $reservation = $this->withUid(new Reservation(), 5);
        $reservation->setLesson($this->createMock(Schedule::class));

        return $reservation;
    }

    private function buildFullReservation(): Reservation
    {
        $reservation = $this->buildReservation();
        $reservation->setContact($this->withUid(new Contact(), 20));
        $reservation->setBillingAddress($this->withUid(new BillingAddress(), 30));
        $reservation->addParticipant($this->withUid(new Person(), 11));
        $reservation->addParticipant($this->withUid(new Person(), 12));
        $reservation->addNotification(new Notification());
        $reservation->addNotification(new Notification());

        return $reservation;
    }

    /**
     * @test
     */
    public function removeRemovesParticipantsContactBillingAddressNotificationsAndReservation(): void
    {
        $reservation = $this->buildFullReservation();
        $this->resolver->method('filterDeletable')->willReturnArgument(0);

        $removedParticipants = [];
        $this->personRepository->expects($this->exactly(2))->method('remove')
            ->willReturnCallback(function ($person) use (&$removedParticipants) {
                $removedParticipants[] = $person->getUid();
            });
        $this->contactRepository->expects($this->once())->method('remove')
            ->with($reservation->getContact());
        $this->billingAddressRepository->expects($this->once())->method('remove')
            ->with($reservation->getBillingAddress());
        $this->notificationRepository->expects($this->exactly(2))->method('remove');
        $this->reservationRepository->expects($this->once())->method('remove')
            ->with($reservation);

        $this->subject->remove($reservation);

        self::assertSame([11, 12], $removedParticipants);
    }

    /**
     * @test
     */
    public function removeSkipsNullContactAndBillingAddressAndEmptyParticipants(): void
    {
        $reservation = $this->buildReservation();
        $this->resolver->method('filterDeletable')->willReturn([]);

        $this->personRepository->expects($this->never())->method('remove');
        $this->contactRepository->expects($this->never())->method('remove');
        $this->billingAddressRepository->expects($this->never())->method('remove');
        $this->notificationRepository->expects($this->never())->method('remove');
        $this->reservationRepository->expects($this->once())->method('remove')
            ->with($reservation);

        $this->subject->remove($reservation);
    }

    /**
     * @test
     */
    public function removeSkipsPersonsTheResolverDoesNotReturn(): void
    {
        $reservation = $this->buildReservation();
        $reservation->setContact($this->withUid(new Contact(), 20));
        $reservation->setBillingAddress($this->withUid(new BillingAddress(), 30));
        $participant = $this->withUid(new Person(), 11);
        $reservation->addParticipant($participant);
        $this->resolver->method('filterDeletable')->with([11, 20, 30], 5)->willReturn([11, 30]);

        $this->contactRepository->expects($this->never())->method('remove');
        $this->personRepository->expects($this->once())->method('remove')
            ->with($participant);
        $this->billingAddressRepository->expects($this->once())->method('remove')
            ->with($reservation->getBillingAddress());
        $this->reservationRepository->expects($this->once())->method('remove')
            ->with($reservation);

        $this->subject->remove($reservation);
    }

    /**
     * @test
     */
    public function removeAsksResolverWithReservationUid(): void
    {
        $reservation = $this->buildFullReservation();

        $this->resolver->expects($this->once())->method('filterDeletable')
            ->with([11, 12, 20, 30], 5)
            ->willReturn([]);

        $this->subject->remove($reservation);
    }
}

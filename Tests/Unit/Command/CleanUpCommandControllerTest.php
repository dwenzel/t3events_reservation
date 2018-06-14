<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Command;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CPSIT\T3eventsReservation\Command\CleanUpCommandController;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ReservationDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use DWenzel\T3events\Domain\Repository\NotificationRepository;

/**
 * Class CleanUpCommandControllerTest

 *
*@package CPSIT\T3eventsReservation\Tests\Unit\Command
 */
class CleanUpCommandControllerTest extends UnitTestCase
{
    /**
     * @var CleanUpCommandController
     */
    protected $subject;

    /**
     * @var ReservationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservationRepository;

    /**
     * @var  PersonRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $personRepository;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            CleanUpCommandController::class, ['dummy', 'outputLine']
        );
        $this->mockReservationRepository();
        $this->personRepository = $this->getMockBuilder(PersonRepository::class)
            ->setMethods(['remove'])->disableOriginalConstructor()
            ->getMock();
        $this->subject->injectPersonRepository($this->personRepository);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationDemandFactory()
    {
        $mockDemandFactory = $this->getMockBuilder(
            ReservationDemandFactory::class)
        ->setMethods(['createFromSettings'])
        ->getMock();
        $this->subject->injectReservationDemandFactory($mockDemandFactory);

        return $mockDemandFactory;
    }

    /**
     * @return ReservationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationRepository()
    {
        /** @var ReservationRepository|\PHPUnit_Framework_MockObject_MockObject $mockReservationRepository */
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->setMethods(['findDemanded', 'remove'])
            ->disableOriginalConstructor()->getMock();

        $this->subject->injectReservationRepository($this->reservationRepository);

        return $this->reservationRepository;
    }

    /**
     * @test
     */
    public function deleteReservationGetsReservationDemandFromFactory()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $emptyQueryResult = [];
        $this->reservationRepository->expects($this->atLeast(1))
            ->method('findDemanded')->will($this->returnValue($emptyQueryResult));
        $settings = [
            'period' => 'pastOnly',
            'storagePages' => '',
            'limit' => 1000
        ];
        $mockDemandFactory = $this->mockReservationDemandFactory();
        $mockDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($mockReservationDemand));

        $this->subject->deleteReservationsCommand();
    }

    /**
     * @test
     */
    public function deleteReservationsCommandPassesArgumentsToDemandFactory()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)
            ->getMock();

        $period = 'specific';
        $date = 'now';
        $storagePageIds = 'foo';
        $limit = 3;

        $settings = [
            'period' => $period,
            'storagePages' => $storagePageIds,
            'periodType' => 'byDate',
            'periodEndDate' => $date,
            'periodStartDate' => '01-01-1970',
            'limit' => $limit
        ];
        $mockDemandFactory = $this->mockReservationDemandFactory();

        $mockDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($mockReservationDemand));
        $emptyQueryResult = [];
        $this->reservationRepository->expects($this->atLeast(1))
            ->method('findDemanded')->will($this->returnValue($emptyQueryResult));

        $this->subject->deleteReservationsCommand(true, $period, $date, $storagePageIds, $limit);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandDemandsReservations()
    {
        $emptyQueryResult = [];

        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)
            ->getMock();
        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $this->reservationRepository->expects($this->atLeast(1))
            ->method('findDemanded')
            ->with($mockReservationDemand)
            ->will($this->returnValue($emptyQueryResult));

        $this->subject->deleteReservationsCommand();
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesParticipants()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getParticipants'])->getMock();
        $mockReservationResult = [
          $mockReservation
        ];
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $participants = [
          $mockParticipant
        ];
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        /** @var PersonRepository|\PHPUnit_Framework_MockObject_MockObject $mockPersonRepository */
        $mockPersonRepository = $this->getMockBuilder(PersonRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subject->injectPersonRepository($mockPersonRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($mockReservationResult));

        $mockReservation->expects($this->once())
            ->method('getParticipants')
            ->will($this->returnValue($participants));
        $mockPersonRepository->expects($this->once())
            ->method('remove')
            ->with($mockParticipant);

        $this->subject->deleteReservationsCommand(false);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesContacts()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getContact'])->getMock();
        $mockReservationResult = [
            $mockReservation
        ];
        $mockContact = $this->getMockBuilder(Contact::class)->getMock();
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        /** @var ContactRepository|\PHPUnit_Framework_MockObject_MockObject $mockContactRepository */
        $mockContactRepository = $this->getMockBuilder(ContactRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()->getMock();
        $this->subject->injectContactRepository($mockContactRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($mockReservationResult));

        $mockReservation->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($mockContact));
        $mockContactRepository->expects($this->once())
            ->method('remove')
            ->with($mockContact);

        $this->subject->deleteReservationsCommand(false);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesBillingAddresses()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getBillingAddress'])->getMock();
        $mockReservationResult = [$mockReservation];
        $mockBillingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        /** @var BillingAddressRepository|\PHPUnit_Framework_MockObject_MockObject $mockBillingAddressRepository */
        $mockBillingAddressRepository = $this->getMockBuilder(BillingAddressRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subject->injectBillingAddressRepository($mockBillingAddressRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($mockReservationResult));

        $mockReservation->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($mockBillingAddress));
        $mockBillingAddressRepository->expects($this->once())
            ->method('remove')
            ->with($mockBillingAddress);

        $this->subject->deleteReservationsCommand(false);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesNotifications()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
        ->setMethods(['getNotifications'])->getMock();
        $mockReservationResult = [
            $mockReservation
        ];
        $mockNotification = $this->getMockBuilder(Notification::class)->getMock();
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        /** @var NotificationRepository|\PHPUnit_Framework_MockObject_MockObject $mockNotificationRepository */
        $mockNotificationRepository = $this->getMockBuilder(NotificationRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()->getMock();
        $this->subject->injectNotificationRepository($mockNotificationRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($mockReservationResult));

        $mockReservation->expects($this->once())
            ->method('getNotifications')
            ->will($this->returnValue([$mockNotification]));
        $mockNotificationRepository->expects($this->once())
            ->method('remove')
            ->with($mockNotification);

        $this->subject->deleteReservationsCommand(false);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesReservations()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)
            ->getMock();
        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();

        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getParticipants'])
            ->getMock();
        $mockReservation->expects($this->any())->method('getParticipants')
            ->will($this->returnValue([$mockParticipant]));
        $reservationsResult = [$mockReservation];

        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($reservationsResult));

        $this->reservationRepository->expects($this->once())
            ->method('remove')
            ->with($mockReservation);

        $this->subject->deleteReservationsCommand(false);
    }
}

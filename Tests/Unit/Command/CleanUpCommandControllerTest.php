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
use DWenzel\T3events\Domain\Repository\NotificationRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class CleanUpCommandControllerTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Command
 */
class CleanUpCommandControllerTest extends UnitTestCase
{
    /**
     * @var CleanUpCommandController
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            CleanUpCommandController::class, ['dummy', 'outputLine']
        );
    }

    /**
     * @test
     */
    public function deleteReservationGetsReservationDemandFromFactory()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();

        $this->mockReservationRepository();
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationRepository()
    {
        $mockReservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->setMethods(['findDemanded', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subject->injectReservationRepository($mockReservationRepository);

        return $mockReservationRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationDemandFactory()
    {
        $mockDemandFactory = $this->getMockBuilder(ReservationDemandFactory::class)
            ->setMethods(['createFromSettings'])->getMock();
        $this->subject->injectReservationDemandFactory($mockDemandFactory);

        return $mockDemandFactory;
    }

    /**
     * @test
     */
    public function deleteReservationsCommandPassesArgumentsToDemandFactory()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();

        $this->mockReservationRepository();
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

        $this->subject->deleteReservationsCommand(true, $period, $date, $storagePageIds, $limit);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandDemandsReservations()
    {
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
            ->method('findDemanded')
            ->with($mockReservationDemand);

        $this->subject->deleteReservationsCommand();
    }

    /**
     * @test
     */
    public function deleteReservationsCommandRemovesParticipants()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getParticipants'])->getMock();
        $mockReservationResult = [$mockReservation];
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $participants = [$mockParticipant];
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $mockPersonRepository = $this->getMockBuilder(PersonRepository::class)
            ->setMethods(['remove'])->disableOriginalConstructor()->getMock();
        $this->subject->injectPersonRepository($mockPersonRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
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
        $mockReservationResult = [$mockReservation];
        $mockContact = $this->getMockBuilder(Contact::class)->getMock();
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $mockContactRepository = $this->getMockBuilder(ContactRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()->getMock();
        $this->subject->injectContactRepository($mockContactRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
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

        $mockBillingAddressRepository = $this->getMockBuilder(BillingAddressRepository::class)
            ->setMethods(['remove'])->disableOriginalConstructor()->getMock();
        $this->subject->injectBillingAddressRepository($mockBillingAddressRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
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
        $mockReservationResult = [$mockReservation];
        $mockNotification = $this->getMockBuilder(Notification::class)->getMock();
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();

        $mockNotificationRepository = $this->getMockBuilder(NotificationRepository::class)
            ->setMethods(['remove'])->disableOriginalConstructor()->getMock();
        $this->subject->injectNotificationRepository($mockNotificationRepository);

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
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
        $mockReservationDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();

        $reservationDemandFactory = $this->mockReservationDemandFactory();
        $reservationDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->will($this->returnValue($mockReservationDemand));

        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $reservationsResult = [$mockReservation];

        $mockReservationRepository = $this->mockReservationRepository();

        $mockReservationRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($reservationsResult));

        $mockReservationRepository->expects($this->once())
            ->method('remove')
            ->with($mockReservation);

        $this->subject->deleteReservationsCommand(false);
    }
}

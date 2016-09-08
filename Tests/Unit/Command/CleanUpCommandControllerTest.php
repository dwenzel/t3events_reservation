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
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            CleanUpCommandController::class, ['dummy']
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationDemandFactory()
    {
        $mockDemandFactory = $this->getMock(
            ReservationDemandFactory::class, ['createFromSettings'], [], '', false
        );
        $this->subject->injectReservationDemandFactory($mockDemandFactory);

        return $mockDemandFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationRepository()
    {
        $mockReservationRepository = $this->getMock(
            ReservationRepository::class, ['findDemanded'], [], '', false
        );
        $this->subject->injectReservationRepository($mockReservationRepository);

        return $mockReservationRepository;
    }

    /**
     * @test
     */
    public function deleteReservationGetsReservationDemandFromFactory()
    {
        $mockReservationDemand = $this->getMock(
        ReservationDemand::class
        );

        $this->mockReservationRepository();
        $settings = [
            'period' => 'pastOnly'
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
    public function deleteReservationCommandPassesArgumentsToDemandFactory()
    {
        $mockReservationDemand = $this->getMock(
            ReservationDemand::class
        );

        $this->mockReservationRepository();
        $period = 'all';
        $lessonDate = 'now';
        $settings = [
            'period' => $period,
            'lessonDate' => $lessonDate
        ];
        $mockDemandFactory = $this->mockReservationDemandFactory();

        $mockDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($mockReservationDemand));

        $this->subject->deleteReservationsCommand(true, $period, $lessonDate);
    }

    /**
     * @test
     */
    public function deleteReservationsCommandDemandsReservations()
    {
        $mockReservationDemand = $this->getMock(
            ReservationDemand::class
        );
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
    public function deleteReservationCommandRemovesParticipants()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getParticipants']
        );
        $mockReservationResult = [
          $mockReservation
        ];
        $mockParticipant = $this->getMock(
            Person::class
        );
        $participants = [
          $mockParticipant
        ];
        $mockReservationDemand = $this->getMock(
            ReservationDemand::class
        );
        $mockPersonRepository = $this->getMock(
            PersonRepository::class, ['remove'], [], '', false
        );
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
    public function deleteReservationCommandRemovesContacts()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getContact']
        );
        $mockReservationResult = [
            $mockReservation
        ];
        $mockContact = $this->getMock(
            Contact::class
        );
        $mockReservationDemand = $this->getMock(
            ReservationDemand::class
        );
        $mockContactRepository = $this->getMock(
            ContactRepository::class, ['remove'], [], '', false
        );
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
    public function deleteReservationCommandRemovesBillingAddresses()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getBillingAddress']
        );
        $mockReservationResult = [
            $mockReservation
        ];
        $mockBillingAddress = $this->getMock(
            BillingAddress::class
        );
        $mockReservationDemand = $this->getMock(
            ReservationDemand::class
        );
        $mockBillingAddressRepository = $this->getMock(
            BillingAddressRepository::class, ['remove'], [], '', false
        );
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
}

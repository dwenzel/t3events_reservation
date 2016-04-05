<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Controller\ParticipantController;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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
class ParticipantControllerTest extends UnitTestCase
{
    /**
     * @var ParticipantController
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ParticipantController::class, ['forward']
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonRepository
     */
    protected function mockParticipantRepository()
    {
        /** @var PersonRepository $mockRepository */
        $mockRepository = $this->getMock(
            PersonRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectParticipantRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * Creates a mock View, injects it and returns it
     *
     * @return mixed
     */
    protected function mockView() {
        $view = $this->getMock(ViewInterface::class);
        $this->inject($this->subject, 'view', $view);

        return $view;
    }

    /**
     * @test
     */
    public function participantRepositoryCanBeInjected() {
        $mockRepository = $this->mockParticipantRepository();

        $this->assertAttributeSame(
            $mockRepository,
            'participantRepository',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function updateActionUpdatesParticipant()
    {
        /** @var Person $participant */
        $participant = $this->getMock(
            Person::class
        );
        $mockRepository = $this->mockParticipantRepository();
        $mockRepository->expects($this->once())
            ->method('update')
            ->with($participant);

        $this->subject->updateAction($participant);
    }

    /**
     * @test
     */
    public function updateActionForwardsToDefaultController()
    {
        $this->mockParticipantRepository();
        $participant = new Person();
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMock(
            Reservation::class
        );
        $participant->setReservation($mockReservation);

        $this->subject->expects($this->once())
            ->method('forward')
            ->with(
                'edit',
                ParticipantController::PARENT_CONTROLLER_NAME,
                null,
                ['reservation' => $mockReservation]
                );

        $this->subject->updateAction($participant);
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1459343264
     */
    public function editActionThrowsExceptionIfReservationDoesNotContainParticipant()
    {
        $participant = $this->mockParticipant();

        $this->subject->editAction($participant);
    }

    /**
     * @test
     */
    public function editActionAssignsVariablesToView()
    {
        $participant = new Person();
        $reservation = new Reservation();
        $reservation->addParticipant($participant);
        $participant->setReservation($reservation);

        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assign')
            ->with('participant', $participant);

        $this->subject->editAction($participant);
    }

    /**
     * @return Person
     */
    protected function mockParticipant()
    {
        $participant = new Person();
        $reservation = new Reservation();
        $participant->setReservation($reservation);

        return $participant;
    }
}

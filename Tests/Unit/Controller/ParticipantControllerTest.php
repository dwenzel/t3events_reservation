<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Controller\AccessControlInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Controller\ParticipantController;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ParticipantControllerTest
 * @package CPSIT\T3eventsReservation\Tests\Unit\Controller
 */
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
            ParticipantController::class, ['dispatch']
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
    protected function mockView()
    {
        $view = $this->getMock(ViewInterface::class);
        $this->inject($this->subject, 'view', $view);

        return $view;
    }

    /**
     * @test
     */
    public function subjectImplementsAccessControlInterface()
    {
        $this->assertInstanceOf(
            AccessControlInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function participantRepositoryCanBeInjected()
    {
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
    public function updateActionCallsDispatch()
    {
        $this->mockParticipantRepository();
        $participant = new Person();
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMock(
            Reservation::class
        );
        $participant->setReservation($mockReservation);

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->updateAction($participant);
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1459343264
     */
    public function editActionThrowsExceptionIfReservationDoesNotContainParticipant()
    {
        $reservation = new Reservation();
        $participant = $this->mockParticipant();

        $this->subject->editAction($participant, $reservation);
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

        $this->subject->editAction($participant, $reservation);
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

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
use CPSIT\T3eventsReservation\Domain\Model\BookableInterface;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Domain\Repository\PerformanceRepository;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Controller\ParticipantController;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

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
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var PersistenceManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceManager;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ParticipantController::class, ['dispatch', 'addFlashMessage', 'translate']
        );
        $this->mockRequest();
        $this->mockView();
        $this->mockPerformanceRepository();
        $this->mockReservationRepository();
        $this->persistenceManager = $this->getMockForAbstractClass(
            PersistenceManagerInterface::class
        );
        $this->subject->injectPersistenceManager($this->persistenceManager);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRequest()
    {
        $this->request = $this->getMock(
            Request::class, ['getOriginalRequest', 'hasArgument', 'getArgument']
        );
        $this->inject(
            $this->subject,
            'request',
            $this->request
        );

        return $this->request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonRepository
     */
    protected function mockPersonRepository()
    {
        /** @var PersonRepository $mockRepository */
        $mockRepository = $this->getMock(
            PersonRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectPersonRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PerformanceRepository
     */
    protected function mockPerformanceRepository()
    {
        /** @var PerformanceRepository $mockRepository */
        $mockRepository = $this->getMock(
            PerformanceRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectPerformanceRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReservationRepository
     */
    protected function mockReservationRepository()
    {
        /** @var ReservationRepository $mockRepository */
        $mockRepository = $this->getMock(
            ReservationRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectReservationRepository($mockRepository);

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
     * @return Person
     */
    protected function mockParticipant()
    {
        $participant = new Person();
        $reservation = new Reservation();
        $participant->setReservation($reservation);

        return $participant;
    }

    /**
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithBookableLesson()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getLesson']
        );
        $bookableItem = $this->getMockForAbstractClass(
            BookableInterface::class
        );
        $bookableItem->expects($this->atLeastOnce())
            ->method('getFreePlaces')
            ->will($this->returnValue(1));
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($bookableItem));
        return $mockReservation;
    }


    /**
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithNonBookableLesson()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getLesson']
        );
        $bookableItem = $this->getMockForAbstractClass(
            DomainObjectInterface::class
        );
        $bookableItem->expects($this->never())
            ->method('getFreePlaces');
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($bookableItem));
        return $mockReservation;
    }

    /**
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithLessonWithoutFreePlaces()
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getLesson']
        );
        $bookableItem = $this->getMockForAbstractClass(
            BookableInterface::class
        );
        $bookableItem->expects($this->atLeastOnce())
            ->method('getFreePlaces')
            ->will($this->returnValue(0));
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($bookableItem));
        return $mockReservation;
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
    public function updateActionUpdatesParticipant()
    {
        /** @var Person $participant */
        $participant = $this->getMock(
            Person::class
        );
        $mockRepository = $this->mockPersonRepository();
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
        $this->mockPersonRepository();
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
     * @test
     */
    public function newActionRestoresParticipantFromRequest()
    {
        $participantFromRequest = $this->getMock(Person::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->request->expects($this->once())
            ->method('getOriginalRequest')
            ->will($this->returnValue($this->request));
        $this->request->expects($this->once())
            ->method('hasArgument')
            ->with('participant')
            ->will($this->returnValue(true));
        $this->request->expects($this->once())
            ->method('getArgument')
            ->with('participant')
            ->will($this->returnValue($participantFromRequest));

        $this->subject->newAction($mockReservation, null);
    }

    /**
     * @test
     */
    public function newActionAssignsVariablesToView()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->getMock(Reservation::class);

        $expectedVariables = [
            'participant' => $mockParticipant,
            'reservation' => $mockReservation
        ];
        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assignMultiple')
            ->with($expectedVariables);

        $this->subject->newAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionCallsDispatch()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->getMock(
            Reservation::class
        );

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionSetsReservation()
    {
        $mockParticipant = $this->getMock(
            Person::class, ['setReservation']
        );
        $mockReservation = $this->mockReservationWithBookableLesson();

        $mockParticipant->expects($this->once())
            ->method('setReservation')
            ->with($mockReservation);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionSetsType()
    {
        $mockParticipant = $this->getMock(
            Person::class, ['setType']
        );
        $mockReservation = $this->mockReservationWithBookableLesson();

        $mockParticipant->expects($this->once())
            ->method('setType')
            ->with(Person::PERSON_TYPE_PARTICIPANT);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionAddsParticipant()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->getMock(
            Reservation::class, ['getLesson', 'addParticipant']
        );
        $bookableItem = $this->getMockForAbstractClass(
            BookableInterface::class
        );
        $bookableItem->expects($this->atLeastOnce())
            ->method('getFreePlaces')
            ->will($this->returnValue(1));
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($bookableItem));
        $mockReservation->expects($this->once())
            ->method('addParticipant')
            ->with($mockParticipant);
        $bookableItem->expects($this->once())
            ->method('addParticipant')
            ->with($mockParticipant);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionUpdatesReservation()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithBookableLesson();
        $mockRepository = $this->mockReservationRepository();
        $this->mockPerformanceRepository();

        $mockRepository->expects($this->once())
            ->method('update')
            ->with($mockReservation);
        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionUpdatesLesson()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithBookableLesson();
        $mockRepository = $this->mockPerformanceRepository();

        $mockRepository->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf(BookableInterface::class));
        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionPersistsAll()
    {
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithBookableLesson();

        $this->persistenceManager->expects($this->once())
            ->method('persistAll');
        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionAddsFlashMessageOnSuccess()
    {
        $translatedMessage = 'foo';
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithBookableLesson();

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.participant.create.success')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionAddsFlashMessageWhenLessonDoesNotHaveFreePlaces()
    {
        $translatedMessage = 'foo';
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithLessonWithoutFreePlaces();

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.participant.create.failure.noFreePlaces')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionAddsFlashMessageWhenLessonIsNotBookable()
    {
        $translatedMessage = 'foo';
        $mockParticipant = $this->getMock(Person::class);
        $mockReservation = $this->mockReservationWithNonBookableLesson();

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.participant.create.failure.notBookable')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

}

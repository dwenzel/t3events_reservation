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
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Domain\Repository\PerformanceRepository;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Controller\ParticipantController;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Class ParticipantControllerTest
 * @package CPSIT\T3eventsReservation\Tests\Unit\Controller
 */
class ParticipantControllerTest extends UnitTestCase
{
    /**
     * @var ParticipantController|\PHPUnit_Framework_MockObject_MockObject
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
        $this->mockPersonRepository();
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
        $this->request = $this->getMockBuilder(Request::class)
            ->setMethods(['getOriginalRequest', 'hasArgument', 'getArgument'])->getMock();
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
        $mockRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'update'])->getMock();
        $this->subject->injectPersonRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PerformanceRepository
     */
    protected function mockPerformanceRepository()
    {
        /** @var PerformanceRepository $mockRepository */
        $mockRepository = $this->getMockBuilder(PerformanceRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'update'])->getMock();
        $this->subject->injectPerformanceRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReservationRepository
     */
    protected function mockReservationRepository()
    {
        /** @var ReservationRepository $mockRepository */
        $mockRepository = $this->getMockBuilder(ReservationRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'update'])->getMock();
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
        $view = $this->getMockBuilder(ViewInterface::class)->getMock();
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
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson'])->getMock();
        $schedule = $this->getMockBuilder(Schedule::class)
            ->setMethods(['getFreePlaces', 'addParticipant'])->getMock();
        $this->inject($mockReservation, 'lesson', $schedule);
        $schedule->expects($this->atLeastOnce())
            ->method('getFreePlaces')
            ->will($this->returnValue(1));
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($schedule));
        return $mockReservation;
    }


    /**
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithNonBookableLesson()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson'])->getMock();
        $nonBookableItem = $this->getMockForAbstractClass(
            DomainObjectInterface::class
        );
        $mockReservation->expects($this->atLeastOnce())
            ->method('getLesson')
            ->will($this->returnValue($nonBookableItem));
        return $mockReservation;
    }

    /**
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithLessonWithoutFreePlaces()
    {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson'])->getMock();

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
     * @return Reservation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithLesson()
    {
        /** @var Reservation|\PHPUnit_Framework_MockObject_MockObject $mockReservation */
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson'])->getMock();
        $mockLesson = $this->getMockBuilder(Schedule::class)->getMock();
        $mockReservation->setLesson($mockLesson);
        $mockReservation->expects($this->any())
            ->method('getLesson')
            ->will($this->returnValue($mockLesson));
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
        $participant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
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
        $participantStorage = new ObjectStorage();
        $participantStorage->attach($participant);

        $reservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getParticipants'])->getMock();
        $reservation->expects($this->atLeastOnce())
            ->method('getParticipants')
            ->will($this->returnValue($participantStorage));
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
        $participantFromRequest = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();

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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();


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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();

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
        $mockParticipant = $this->getMockBuilder(Person::class)
            ->setMethods(['setReservation'])->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)
            ->setMethods(['setType'])->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson', 'addParticipant'])->getMock();
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

        $this->subject->createAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function createActionUpdatesReservation()
    {
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
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

    /**
     * @test
     */
    public function removeActionRemovesParticipantFromReservation()
    {
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['removeParticipant'])->getMock();
        $mockReservation->expects($this->once())
            ->method('removeParticipant')
            ->with($mockParticipant);

        $this->subject->removeAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function removeActionRemovesParticipantFromRepository()
    {
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->mockReservationWithLesson();
        $mockRepository = $this->mockPersonRepository();
        $mockRepository->expects($this->once())
            ->method('remove')
            ->with($mockParticipant);

        $this->subject->removeAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function removeActionUpdatesReservationInRepository()
    {
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->mockReservationWithLesson();
        $mockRepository = $this->mockReservationRepository();
        $mockRepository->expects($this->once())
            ->method('update')
            ->with($mockReservation);

        $this->subject->removeAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function removeActionAddsFlashMessage()
    {
        $translatedMessage = 'foo';
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->mockReservationWithLesson();

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.participant.remove.success')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->removeAction($mockReservation, $mockParticipant);
    }

    /**
     * @test
     */
    public function removeActionCallsDispatch()
    {
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->removeAction($mockReservation, $mockParticipant);
    }

}

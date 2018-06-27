<?php
namespace CPSIT\T3eventsReservations\Tests\Unit\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *  			Boerge Franck <franck@cps-it.de>, CPS IT
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use CPSIT\T3eventsReservation\Controller\ReservationController;
use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\BookableInterface;
use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use DWenzel\T3events\Domain\Model\Performance;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use DWenzel\T3events\Domain\Repository\PerformanceRepository;
use DWenzel\T3events\Service\NotificationService;
use DWenzel\T3events\Session\SessionInterface;
use DWenzel\T3events\Utility\SettingsUtility;

/**
 * Test case for class CPSIT\T3eventsReservations\Controller\ReservationController.
 *
 * @author Dirk Wenzel <wenzel@cps-it.de>
 */
class ReservationControllerTest extends UnitTestCase {

	/**
	 * @var ReservationController
	 */
	protected $subject = NULL;

    /**
     * @var Request |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

	/**
	 * Creates a mock PersistenceManager, injects it to
	 * subject and returns the mock
	 *
	 * @return mixed
	 */
	protected function mockPersistenceManager() {
		$mockPersistenceManager = $this->getMockBuilder(PersistenceManager::class)->getMock();
		$this->inject($this->subject, 'persistenceManager', $mockPersistenceManager);

        return $mockPersistenceManager;
	}

	/**
	 * Creates a mock View, injects it and returns it
	 *
	 * @return mixed
	 */
	protected function mockView() {
		$view = $this->getMockBuilder(ViewInterface::class)->getMock();
		$this->inject($this->subject, 'view', $view);

		return $view;
	}

	/**
	 * @return mixed
	 */
	protected function mockReservationRepository() {
		$reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'update', 'remove'])->getMock();
		$this->inject($this->subject, 'reservationRepository', $reservationRepository);

		return $reservationRepository;
	}

    /**
     * @return mixed
     */
    protected function mockPersonRepository() {
        $personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'update', 'remove'])->getMock();
        $this->inject($this->subject, 'personRepository', $personRepository);

        return $personRepository;
    }

	/**
	 * @return mixed
	 */
	protected function mockBillingAddressRepository() {
		$billingAddressRepository = $this->getMockBuilder(BillingAddressRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'update', 'remove'])->getMock();
		$this->inject($this->subject, 'billingAddressRepository', $billingAddressRepository);

		return $billingAddressRepository;
	}

	protected function mockAllowAccessReturnsTrue() {
		$this->subject->expects($this->once())
			->method('isAccessAllowed')
			->will($this->returnValue(TRUE));
	}

	/**
	 * @return mixed
	 */
	protected function mockRequest() {
		$mockRequest = $this->getMockBuilder(Request::class)
        ->setMethods(['getOriginalRequest', 'getArgument'])->getMock();
		$this->inject($this->subject, 'request', $mockRequest);

		return $mockRequest;
	}

	/**
	 * @return mixed
	 */
	protected function mockSession() {
		$mockSession = $this->getMockBuilder(SessionInterface::class)->getMock();
		$this->inject($this->subject, 'session', $mockSession);

		return $mockSession;
	}

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
	protected function mockSettingsUtility() {
		$mockSettingsUtility = $this->getMockBuilder(SettingsUtility::class)
            ->setMethods(['getValueByKey', 'getFileStorage'])->getMock();
		$this->subject->injectSettingsUtility($mockSettingsUtility);

		return $mockSettingsUtility;
	}

	/**
	 * @return mixed
	 */
	protected function mockLessonRepository() {
		$mockLessonRepository = $this->getMockBuilder(PerformanceRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'update', 'remove'])->getMock();
		$this->inject($this->subject, 'lessonRepository', $mockLessonRepository);

		return $mockLessonRepository;
	}

	protected function assertDenyAccess() {
		$this->subject->expects($this->once())
			->method('denyAccess');
	}

	/**
	 * @return mixed
	 */
	protected function mockObjectManager() {
		$mockObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->setMethods(['get'])->getMock();
		$this->subject->_set('objectManager', $mockObjectManager);

		return $mockObjectManager;
	}

	/**
	 * @return mixed
	 */
	protected function mockNotificationService() {
		$mockNotificationService = $this->getMockBuilder(NotificationService::class)
            ->setMethods(['render', 'send'])->getMock();
		$this->inject($this->subject, 'notificationService', $mockNotificationService);

		return $mockNotificationService;
	}

	protected function setUp() {
		$this->subject = $this->getAccessibleMock(
			ReservationController::class,
			[
                'dispatch',
                'addFlashMessage',
                'translate',
                'isAccessAllowed',
                'clearCacheOnError',
                'denyAccess'
            ],
			[], '', false);
		$mockSession = $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set', 'has', 'clean', 'setNamespace'])->getMock();
		$this->inject($this->subject, 'session', $mockSession);
        $this->request = $this->mockRequest();
    }

	/**
	 * @test
	 */
	public function newParticipantActionDoesNotDenyAccessIfReservationStatusIsDraft() {
		$mockRequest = $this->getMockBuilder(Request::class)->getMock();
		$this->mockView();
		$this->inject($this->subject, 'request', $mockRequest);
		$reservation = new Reservation();
		$reservation->setStatus(Reservation::STATUS_DRAFT);

		$this->subject->expects($this->never())
			->method('denyAccess');
		$this->subject->newParticipantAction($reservation);
	}

	/**
     * @test
     */
    public function confirmActionSetsStatusSubmitted() {
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['setStatus'])->getMock();
        $this->mockReservationRepository();

        $mockReservation->expects($this->once())
            ->method('setStatus')
            ->with(Reservation::STATUS_SUBMITTED);

        $this->subject->confirmAction($mockReservation);
    }

    /**
     * @test
     */
    public function confirmActionAddsFlashMessage() {
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $this->mockReservationRepository();
        $translatedMessage = 'foo';
        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.reservation.confirm.success')
            ->will($this->returnValue($translatedMessage)
        );
        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->confirmAction($mockReservation);
    }

    /**
     * @test
     */
    public function confirmActionSendsNotification() {
        $this->subject = $this->getAccessibleMock(
            ReservationController::class,
            ['sendNotification', 'dispatch', 'addFlashMessage', 'translate', 'isAccessAllowed', 'clearCacheOnError'],
            [], '', false);
        $this->mockRequest();
        $identifier = 'foo';
        $configForIdentifier = ['bar'];

        $settings = [
            'reservation' => [
                'confirm' => [
                    'notification' => [
                        $identifier => $configForIdentifier
                    ]
                ]
            ]
        ];
        $this->subject->_set('settings', $settings);

        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $this->mockReservationRepository();

        $this->subject->expects($this->once())
            ->method('sendNotification')
            ->with(
                $mockReservation,
                $identifier,
                $configForIdentifier
            );

        $this->subject->confirmAction($mockReservation);
    }

    /**
     * @test
     */
    public function confirmActionUpdatesReservation() {
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $mockRepository = $this->mockReservationRepository();
        $mockRepository->expects($this->once())
            ->method('update')
            ->with($mockReservation);

        $this->subject->confirmAction($mockReservation);
    }

    /**
     * @test
     */
    public function confirmActionCallsDispatch() {
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $this->mockReservationRepository();
        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->confirmAction($mockReservation);
    }

    /**
     * @test
     */
    public function checkoutActionAssignsReservationToView() {
        $reservation = new Reservation();
        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assign')
            ->with('reservation', $reservation);

        $this->subject->checkoutAction($reservation);
    }

   	/**
	 * @test
	 */
	public function createActionDeniesAccessIfReservationIsNotNew() {
		$reservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getUid'])->getMock();
		$reservation->expects($this->once())
			->method('getUid')
			->will($this->returnValue(5));
		$this->assertDenyAccess();
		$this->subject->createAction($reservation);
	}

	/**
	 * @test
	 */
	public function createActionDeniesAccessReservationIsInSession() {
		$reservation = $this->getMockBuilder(Reservation::class)->getMock();
		$mockSession = $this->mockSession();
		$mockSession->expects($this->once())
			->method('has')
			->with('reservationUid')
			->will($this->returnValue(true));
		$this->assertDenyAccess();

		$this->subject->createAction($reservation);
	}

	/**
	 * @test
	 */
	public function showActionAssignsReservationToView() {
		$reservation = new Reservation();
		$view = $this->mockView();
		$view->expects($this->once())->method('assign')->with('reservation', $reservation);

		$this->subject->showAction($reservation);
	}

    /**
     * @test
     */
    public function showActionCleansSession() {
        $reservation = new Reservation();
        $this->mockView();
        $session = $this->mockSession();
        $session->expects($this->once())
            ->method('clean');

        $this->subject->showAction($reservation);
    }

	/**
	 * @test
	 */
	public function newActionAssignsVariablesToView() {
		$mockRequest = $this->mockRequest();
		$mockRequest->expects($this->once())
			->method('getOriginalRequest');
		$reservation = new Reservation();
		$mockLesson = $this->getMockBuilder(Performance::class)
            ->setMethods(['getFreePlaces'])->getMock();
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(99));

		$view = $this->getMockBuilder(ViewInterface::class)->getMock();
		$view->expects($this->once())
			->method('assignMultiple')
			->with(
				[
					'newReservation' => $reservation,
					'lesson' => $mockLesson
				]
			);
		$this->inject($this->subject, 'view', $view);

		$this->subject->newAction($mockLesson, $reservation);
	}

	/**
	 * @test
	 */
	public function createActionAddsReservationToReservationRepository() {
		$this->mockPersistenceManager();

		$mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getContact'])->getMock();
		$mockContact = $this->getMockBuilder(Person::class)->getMock();
		$mockReservation->expects($this->once())
			->method('getContact')
			->will($this->returnValue($mockContact));

		$reservationRepository = $this->mockReservationRepository();
		$reservationRepository->expects($this->once())
			->method('add')
			->with($mockReservation);

		$this->subject->createAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function createActionSetsStatusDraft() {
		$this->mockPersistenceManager();
		$this->mockReservationRepository();

		$mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['setStatus'])->getMock();
		$mockReservation->expects($this->once())
			->method('setStatus')
			->with(Reservation::STATUS_DRAFT);

		$this->subject->createAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function editActionAssignsReservationToView() {
		$this->mockPersistenceManager();
		$reservationRepository = $this->mockReservationRepository();
		$view = $this->mockView();

		$reservation = new Reservation();

		$view->expects($this->once())
			->method('assignMultiple')
			->with(
				[
				'reservation' => $reservation
				]
			);

		$reservationRepository->expects($this->once())
			->method('update')
			->with($reservation);

		$this->subject->editAction($reservation);
	}

	/**
	 * @test
	 */
	public function createParticipantUpdatesReservationInReservationRepository() {
		$this->mockPersistenceManager();
		$this->mockLessonRepository();

		$reservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getLesson'])->getMock();
		$newParticipant = new Person();
		$mockLesson = $this->getMockBuilder(Performance::class)
            ->setMethods(['getFreePlaces', 'addParticipant'])->getMock();
		$this->inject($reservation, 'lesson', $mockLesson);
		$reservation->expects($this->any())
			->method('getLesson')
			->will($this->returnValue($mockLesson));
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(99));
		$reservationRepository = $this->mockReservationRepository();
		$reservationRepository->expects($this->once())
			->method('update')
			->with($reservation);

		$this->subject->createParticipantAction($reservation, $newParticipant);
	}

	/**
	 * @test
	 */
	public function deleteActionRemovesReservationFromReservationRepository() {
		$reservation = new Reservation();

		$reservationRepository = $this->mockReservationRepository();
		$reservationRepository->expects($this->once())
			->method('remove')
			->with($reservation);

		$this->subject->deleteAction($reservation);
	}

	/**
	 * @test
	 */
	public function deleteActionClearsSession()
	{
		$reservation = new Reservation();
		$mockSession = $this->mockSession();
		$this->mockReservationRepository();
		$mockSession->expects($this->once())
			->method('clean');

		$this->subject->deleteAction($reservation);
	}

	/**
	 * @test
	 */
	public function deleteActionRemovesContactFromRepository()
	{
		$mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getContact'])->getMock();
		$mockContact = $this->getMockBuilder(Contact::class)->getMock();
        $mockReservation->expects($this->once())
            ->method('getContact')
            ->will($this->returnValue($mockContact));
		$this->mockSession();
		$this->mockReservationRepository();
		$mockContactRepository = $this->getMockBuilder(ContactRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'update'])->getMock();
        $this->inject(
            $this->subject,
            'contactRepository',
            $mockContactRepository
        );

        $mockContactRepository->expects($this->once())
            ->method('remove')
            ->with($mockContact);

        $this->subject->deleteAction($mockReservation);
	}


	/**
	 * @test
	 * @expectedException \TYPO3\CMS\Extbase\Configuration\Exception
	 * @expectedExceptionCode 1454518855
	 */
	public function sendNotificationThrowsExceptionIfFromEmailIsNotSet() {
		$config = [];
		$identifier = 'foo';
		$this->mockSettingsUtility();
		$reservation = new Reservation();
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\CMS\Extbase\Configuration\Exception
	 * @expectedExceptionCode 1454865240
	 */
	public function sendNotificationThrowsExceptionIfRecipientEmailIsNotSet() {
		$config = [
			'fromEmail' => 'foo@bar.com'
		];
		$identifier = 'foo';
		$this->mockSettingsUtility();
		$reservation = new Reservation();
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\CMS\Extbase\Configuration\Exception
	 * @expectedExceptionCode 1454865250
	 */
	public function sendNotificationThrowsExceptionIfSubjectIsNotSet() {
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com'
		];
		$identifier = 'foo';
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$reservation = new Reservation();
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationSendsNotification() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz'
		];
		$this->subject->injectSettingsUtility(new SettingsUtility());

		$identifier = 'foo';
		$reservation = new Reservation();
		$mockNotification = $this->getMockBuilder(Notification::class)
            ->setMethods(['setRecipient', 'setSender', 'setSubject', 'setFormat', 'setBodyText'])->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$mockNotificationService = $this->mockNotificationService();
		$mockNotificationService->expects($this->once())
			->method('render')
			->with(
				ucfirst($identifier),
				'plain',
				'Reservation/Email',
				['reservation' => $reservation, 'settings' => $settings]
			);
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}


	/**
	 * @test
	 */
	public function sendNotificationGetsToEmailByPropertyPath() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => [
				'field' => 'contact.email'
			],
			'subject' => 'baz'
		];
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$identifier = 'foo';
		$email = 'bar@baz.com';
		$mockContact = $this->getAccessibleMock(
			Person::class, ['getEmail']
		);
		$reservation = $this->getAccessibleMock(
			Reservation::class, ['getContact']
		);
		$reservation->expects($this->once())
			->method('getContact')
			->will($this->returnValue($mockContact));
		$mockContact->expects($this->once())
			->method('getEmail')
			->will($this->returnValue($email));
		$mockNotification = $this->getMockBuilder(Notification::class)
            ->setMethods(['setRecipient', 'setSender', 'setSubject', 'setFormat', 'setBodyText'])->getMock();
		$mockNotification->expects($this->once())
			->method('setRecipient')
			->with($email);
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$mockNotificationService = $this->mockNotificationService();
		$mockNotificationService->expects($this->once())
			->method('render')
			->with(
				ucfirst($identifier),
				'plain',
				'Reservation/Email',
				['reservation' => $reservation, 'settings' => $settings]
			);
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationGetsFormatFromSettings() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz',
			'format' => 'html'
		];
		$identifier = 'foo';
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$reservation = new Reservation();
		$mockNotification = $this->getMockBuilder(Notification::class)
            ->setMethods(['setRecipient', 'setSender', 'setSubject', 'setFormat', 'setBodyText'])->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$mockNotificationService = $this->mockNotificationService();
		$mockNotificationService->expects($this->once())
			->method('render')
			->with(
				ucfirst($identifier),
				'html',
				'Reservation/Email',
				['reservation' => $reservation, 'settings' => $settings]
			);
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationGetsSenderNameFromSettings() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz',
			'senderName' => 'fooName'
		];
		$identifier = 'foo';
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$reservation = new Reservation();
		$mockNotification = $this->getMockBuilder(Notification::class)
            ->setMethods(['setRecipient', 'setSenderName', 'setSubject', 'setFormat', 'setBodyText'])->getMock();
        $mockNotification->expects($this->once())
            ->method('setSenderName')
            ->with($config['senderName']);

		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$this->mockNotificationService();
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationGetsTemplateFileNameFromSettings() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz',
			'format' => 'html',
			'template' => [
				'fileName' => 'fooFileName'
			]
		];
		$identifier = 'foo';
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$reservation = new Reservation();
		$mockNotification = $this->getMockBuilder(Notification::class)->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$mockNotificationService = $this->mockNotificationService();
		$mockNotificationService->expects($this->once())
			->method('render')
			->with(
				'fooFileName',
				'html',
				'Reservation/Email',
				['reservation' => $reservation, 'settings' => $settings]
			);
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationAddsAttachments() {
		$reservation = new Reservation();
		$identifier = 'foo';
		$fileUtilityConfig = [
			'foo' => 'bar'
		];
		$config = [
			'attachments' => [
				'files' => $fileUtilityConfig
			],
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz',
		];
		$mockObjectStorage = $this->getMockBuilder(ObjectStorage::class)->getMock();
		$mockSettingsUtility = $this->mockSettingsUtility();
		$mockSettingsUtility->expects($this->once())
			->method('getFileStorage')
			->with($reservation, $fileUtilityConfig)
			->will($this->returnValue($mockObjectStorage));
		$mockSettingsUtility->expects($this->any())
			->method('getValueByKey')
			->will($this->returnValue('barBaz'));
		$mockNotification = $this->getMockBuilder(Notification::class)
            ->setMethods(['setAttachments'])->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$this->mockNotificationService();
		$mockNotification->expects($this->once())
			->method('setAttachments')
			->with($mockObjectStorage);

		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function sendNotificationGetsTemplateFolderFromSettings() {
		$settings = ['foo'];
		$this->subject->_set('settings', $settings);
		$this->subject->injectSettingsUtility(new SettingsUtility());
		$folderName = 'fooFolder';
		$config = [
			'fromEmail' => 'foo@bar.com',
			'toEmail' => 'bar@baz.com',
			'subject' => 'baz',
			'format' => 'html',
			'template' => [
				'folderName' => $folderName
			]
		];
		$identifier = 'foo';
		$reservation = new Reservation();
		$mockNotification = $this->getMockBuilder(Notification::class)->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(Notification::class)
			->will($this->returnValue($mockNotification));
		$mockNotificationService = $this->mockNotificationService();
		$mockNotificationService->expects($this->once())
			->method('render')
			->with(
				ucfirst($identifier),
				'html',
				$folderName,
				['reservation' => $reservation, 'settings' => $settings]
			);
		$this->subject->_callRef('sendNotification', $reservation, $identifier, $config);
	}

	/**
	 * @test
	 */
	public function newParticipantActionAssignsVariablesToView() {
		$mockReservation = $this->getAccessibleMock(
			Reservation::class, ['getStatus', 'getLesson']
		);
		$mockLesson = $this->getMockBuilder(BookableInterface::class)->getMock();
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(1));

		$mockReservation->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(Reservation::STATUS_DRAFT));
		$mockReservation->expects($this->once())
			->method('getLesson')
			->will($this->returnValue($mockLesson));
		$this->mockRequest();
		$mockView = $this->mockView();
		$mockView->expects($this->once())
			->method('assignMultiple')
			->with(
				[
					'newParticipant' => null,
					'reservation' => $mockReservation
				]
			);
		$this->subject->newParticipantAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function newParticipantActionSetsStatusDraft() {
		$mockReservation = $this->getAccessibleMock(
			Reservation::class, ['getStatus', 'setStatus', 'getLesson']
		);
		$mockLesson = $this->getMockBuilder(BookableInterface::class)->getMock();
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(1));

		$mockReservation->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(Reservation::STATUS_NEW));
		$mockReservation->expects($this->any())
			->method('setStatus')
			->with(Reservation::STATUS_DRAFT);
		$mockReservation->expects($this->once())
			->method('getLesson')
			->will($this->returnValue($mockLesson));
		$this->mockRequest();
		$this->mockView();
		$this->subject->newParticipantAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function newParticipantActionAddsFlashMessageIfNoFreePlaces() {
		$mockReservation = $this->getAccessibleMock(
			Reservation::class, ['getStatus', 'setStatus', 'getLesson']
		);

		$mockLesson = $this->getMockBuilder(BookableInterface::class)->getMock();
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(0));
		$mockReservation->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(Reservation::STATUS_NEW));
        $mockReservation->expects($this->once())
			->method('getLesson')
			->will($this->returnValue($mockLesson));
		$this->mockRequest();
		$this->mockView();
		$mockMessage = 'fooMessage';
		$this->subject->expects($this->once())
			->method('translate')
			->with('message.noFreePlacesForThisLesson')
			->will($this->returnValue($mockMessage));
		$this->subject->expects($this->once())
			->method('addFlashMessage')
			->with(
				$mockMessage,
				'',
				AbstractMessage::ERROR,
				true
			);
		$this->subject->newParticipantAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function newParticipantGetsParticipantFromOriginalRequest() {
		$mockReservation = $this->getAccessibleMock(
			Reservation::class, ['getStatus', 'setStatus', 'getLesson']
		);

        $mockLesson = $this->getMockBuilder(BookableInterface::class)->getMock();
		$mockLesson->expects($this->once())
			->method('getFreePlaces')
			->will($this->returnValue(3));

		$mockReservation->expects($this->any())
			->method('getStatus')
			->will($this->returnValue(Reservation::STATUS_NEW));
		$mockReservation->expects($this->once())
			->method('getLesson')
			->will($this->returnValue($mockLesson));
		$this->mockView();
		$mockRequest = $this->mockRequest();
		$mockRequest->expects($this->any())
			->method('getOriginalRequest')
			->will($this->returnValue($mockRequest));
		$mockRequest->expects($this->once())
			->method('getArgument')
			->with('newParticipant');
		$this->subject->newParticipantAction($mockReservation);
	}

	/**
	 * @test
	 */
	public function editBillingAddressActionAssignsReservationToView() {
		$view = $this->mockView();

		$reservation = new Reservation();

		$view->expects($this->once())
			->method('assignMultiple')
			->with(
				[
					'reservation' => $reservation
				]
			);

		$this->subject->editBillingAddressAction($reservation);
	}

	/**
	 * @test
	 */
	public function updateActionUpdatesReservation() {
		$reservationRepository = $this->mockReservationRepository();

		$reservation = new Reservation();

		$reservationRepository->expects($this->once())
			->method('update')
			->with($reservation);

		$this->subject->updateAction($reservation);
	}

	/**
	 * @test
	 */
	public function updateActionAddsFlashMessageOnSuccess() {
		$this->mockReservationRepository();
		$translatedMessage = 'foo';
		$reservation = new Reservation();

		$this->subject->expects($this->once())
			->method('translate')
			->with('message.reservation.update.success')
			->will($this->returnValue($translatedMessage));
		$this->subject->expects($this->once())
			->method('addFlashMessage')
			->with($translatedMessage);

		$this->subject->updateAction($reservation);
	}

	/**
	 * @test
	 */
	public function updateActionCallsDispatch() {
		$this->mockReservationRepository();
		$reservation = new Reservation();

		$this->subject->expects($this->once())
			->method('dispatch')
			->with(['reservation' => $reservation]);

		$this->subject->updateAction($reservation);
	}

    /**
     * @test
     */
    public function removeBillingAddressAddsMessageOnSuccess()
    {
        $this->mockBillingAddressRepository();

        /** @var Reservation $reservation */
        $reservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getBillingAddress'])->getMock();
        /** @var BillingAddress $mockBillingAddress */
        $mockBillingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $reservation->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($mockBillingAddress));
        $expectedKey = 'message.reservation.removeBillingAddress.success';
        $this->subject->expects($this->once())
            ->method('translate')
            ->with($expectedKey)
            ->will($this->returnValue($expectedKey));
        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($expectedKey);

        $this->subject->removeBillingAddressAction($reservation);
    }

    /**
     * @test
     */
    public function removeBillingAddressCallsDispatch()
    {
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['getBillingAddress'])->getMock();
        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->removeBillingAddressAction($mockReservation);
    }

    /**
     * @test
     */
    public function newBillingAddressActionAssignsVariablesToView()
    {
        $mockReservation = $this->getAccessibleMock(
            Reservation::class
        );

        $mockView = $this->mockView();
        $mockView->expects($this->once())
            ->method('assignMultiple')
            ->with(
                [
                    'newBillingAddress' => null,
                    'reservation' => $mockReservation
                ]
            );

        $this->subject->newBillingAddressAction($mockReservation);
    }

    /**
     * @test
     */
    public function createBillingAddressActionSetsBillingAddress()
    {
        $this->mockReservationRepository();
        /** @var Reservation $reservation */
        $reservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['setBillingAddress'])->getMock();
        /** @var BillingAddress $billingAddress */
        $billingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $this->mockPersonRepository();
        $reservation->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress);

        $this->subject->createBillingAddressAction($reservation, $billingAddress);
    }

    /**
     * @test
     */
    public function createBillingAddressActionAddsBillingAddressToRepository()
    {
        /** @var Reservation $reservation */
        $reservation = $this->getMockBuilder(Reservation::class)->getMock();
        /** @var BillingAddress $billingAddress */
        $billingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $this->mockReservationRepository();
        $mockPersonRepository = $this->mockPersonRepository();
        $mockPersonRepository->expects($this->once())
            ->method('add')
            ->with($billingAddress);

        $this->subject->createBillingAddressAction($reservation, $billingAddress);
    }

    /**
     * @test
     */
    public function createBillingAddressUpdatesReservation()
    {
        /** @var Reservation $reservation */
        $reservation = $this->getMockBuilder(Reservation::class)->getMock();
        /** @var BillingAddress $billingAddress */
        $billingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $this->mockPersonRepository();
        $mockReservationRepository = $this->mockReservationRepository();
        $mockReservationRepository->expects($this->once())
            ->method('update')
            ->with($reservation);

        $this->subject->createBillingAddressAction($reservation, $billingAddress);
    }


    /**
     * @test
     */
    public function createBillingAddressAddsMessageOnSuccess()
    {
        $this->mockPersonRepository();
        $this->mockReservationRepository();

        /** @var Reservation $reservation */
        $reservation = $this->getMockBuilder(Reservation::class)->getMock();
        /** @var BillingAddress $mockBillingAddress */
        $mockBillingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $expectedKey = 'message.reservation.createBillingAddress.success';
        $this->subject->expects($this->once())
            ->method('translate')
            ->with($expectedKey)
            ->will($this->returnValue($expectedKey));
        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($expectedKey);

        $this->subject->createBillingAddressAction($reservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createBillingAddressActionCallsDispatch()
    {
        $this->mockReservationRepository();
        $this->mockPersonRepository();

        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        /** @var BillingAddress $mockBillingAddress */
        $mockBillingAddress = $this->getMockBuilder(BillingAddress::class)->getMock();
        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->createBillingAddressAction($mockReservation, $mockBillingAddress);
    }

	/**
	 * @test
	 */
	public function removeParticipantActionRemovesParticipantFromReservation()
	{
		$this->mockPersonRepository();
		$this->mockReservationRepository();
		$mockParticipant = $this->getMockBuilder(Person::class)->getMock();
		$mockReservation = $this->getMockBuilder(Reservation::class)
            ->setMethods(['removeParticipant', 'getLesson'])->getMock();
		$mockLesson = $this->getMockBuilder(Schedule::class)->getMock();
		$mockReservation->setLesson($mockLesson);
		$mockReservation->expects($this->once())
			->method('removeParticipant')
			->with($mockParticipant);

		$this->subject->removeParticipantAction($mockReservation, $mockParticipant);
	}

	/**
	 * @test
	 */
	public function removeParticipantActionRemovesParticipantFromRepository()
	{
		$mockPersonRepository = $this->mockPersonRepository();
		$this->mockReservationRepository();
		$mockParticipant = $this->getMockBuilder(Person::class)->getMock();
		$mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
		$mockPersonRepository->expects($this->once())
			->method('remove')
			->with($mockParticipant);

		$this->subject->removeParticipantAction($mockReservation, $mockParticipant);
	}

	/**
	 * @test
	 */
	public function removeParticipantActionUpdatesReservation()
	{
		$this->mockPersonRepository();
		$mockReservationRepository = $this->mockReservationRepository();
		$mockParticipant = $this->getMockBuilder(Person::class)->getMock();
		$mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
		$mockReservationRepository->expects($this->once())
			->method('update')
			->with($mockReservation);

		$this->subject->removeParticipantAction($mockReservation, $mockParticipant);
	}

	/**
	 * @test
	 */
	public function removeParticipantActionAddsFlashMessage() {
		$mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
		$mockParticipant = $this->getMockBuilder(Person::class)->getMock();
		$this->mockReservationRepository();
		$this->mockPersonRepository();

		$translatedMessage = 'foo';
		$this->subject->expects($this->once())
			->method('translate')
			->with('message.reservation.removeParticipant.success')
			->will($this->returnValue($translatedMessage)
			);
		$this->subject->expects($this->once())
			->method('addFlashMessage')
			->with($translatedMessage);

		$this->subject->removeParticipantAction($mockReservation, $mockParticipant);
	}


	/**
	 * @test
	 */
	public function removeParticipantActionCallsDispatch() {
		$mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
		$mockParticipant = $this->getMockBuilder(Person::class)->getMock();
		$this->mockReservationRepository();
		$this->mockPersonRepository();

		$this->subject->expects($this->once())
			->method('dispatch')
			->with(['reservation' => $mockReservation]);
		$this->subject->removeParticipantAction($mockReservation, $mockParticipant);
	}
}

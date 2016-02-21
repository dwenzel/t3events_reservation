<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

/**
 * ReservationController
 */
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use Webfox\T3events\Controller\AbstractBackendController;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;

/***************************************************************
 *  Copyright notice
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *           Boerge Franck <franck@cps-it.de>, CPS IT
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
class BookingsController extends AbstractBackendController {


	/**
	 * reservationRepository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
	 */
	protected $reservationRepository = null;

	/**
	 * Participant Repository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository
	 */
	protected $personRepository = null;

	/**
	 * injects the person repository
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository $personRepository
	 * @return void
	 */
	public function injectPersonRepository(PersonRepository $personRepository) {
		$this->personRepository = $personRepository;
	}

	/**
	 * injectReservationRepository
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository $reservationRepository
	 * @return void
	 */
	public function injectReservationRepository(ReservationRepository $reservationRepository) {
		$this->reservationRepository = $reservationRepository;
	}

	/**
	 * List action
	 *
	 * @param array $overwriteDemand
	 * @return void
	 */
	public function listAction(array $overwriteDemand = NULL) {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $demand */
		$demand = $this->createDemandFromSettings($this->settings['bookings']['list']);

		if ($overwriteDemand === NULL) {
			$overwriteDemand = $this->moduleData->getOverwriteDemand();
		} else {
			$this->moduleData->setOverwriteDemand($overwriteDemand);
		}

		$this->overwriteDemandObject($demand, $overwriteDemand);
		$this->moduleData->setDemand($demand);

		$reservations = $this->reservationRepository->findDemanded($demand);
		$this->view->assignMultiple(
			[
				'reservations' => $reservations,
				'overwriteDemand' => $overwriteDemand,
				'demand' => $demand,
				'filterOptions' => $this->getFilterOptions(
					$this->settings[$this->settingsUtility->getControllerKey($this)]['list']['filter'])
			]
		);
	}

	/**
	 * action show
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function showAction(Reservation $reservation) {
		$this->view->assign('reservation', $reservation);
	}

	/**
	 * Edit action
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function editAction(Reservation $reservation) {
		$this->view->assign('reservation', $reservation);
	}

	/**
	 * Update action
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function updateAction(Reservation $reservation) {
		$this->reservationRepository->update($reservation);
		$this->addFlashMessage(
			$this->translate('message.reservation.update.success')
		);
		$this->forward('list');
	}

	/**
	 * Cancel action
	 * Cancels a reservation
	 *
	 * @param Reservation $reservation
	 * @param string $reason
	 * @return void
	 */
	public function cancelAction(Reservation $reservation, $reason) {
		switch ($reason) {
			case 'byOrganizer':
				$newStatus = Reservation::STATUS_CANCELED_BY_SUPPLIER;
				break;
			case 'withCosts':
				$newStatus = Reservation::STATUS_CANCELED_WITH_COSTS;
				break;
			case 'noCharge':
				$newStatus = Reservation::STATUS_CANCELED_NO_CHARGE;
				break;
			default:
				break;
		}
		if ($newStatus) {
			$reservation->setStatus($newStatus);
			if ($participants = $reservation->getParticipants()) {
				foreach ($participants as $participant) {
					$reservation->getLesson()->removeParticipant($participant);
					$this->personRepository->remove($participant);
				}
			}
			$this->reservationRepository->update($reservation);
			$this->addFlashMessage(
				$this->translate('message.bookings.cancel.success')
			);
			if ($this->settings['bookings']['cancel'][$reason]['confirm']['sendNotification']) {
				/**@var Notification $notification * */
				$notification = $this->objectManager->get(Notification::class);
				$bodytext = $this->notificationService->render(
					$this->settings['bookings']['cancel'][$reason]['confirm']['templateFileName'],
					'html',
					'Bookings/Email/Cancel',
					[
						'reservation' => $reservation,
						'baseUrl' => $this->getBaseUrlForFrontend()
					]
				);
				$notification->setRecipient($reservation->getContact()->getEmail());
				$notification->setBodytext($bodytext);
				$notification->setSubject($this->settings['bookings']['cancel'][$reason]['confirm']['subject']);
				$notification->setSender($this->settings['bookings']['cancel'][$reason]['confirm']['fromEmail']);
				$notification->setReservation($reservation);
				$notificationSuccess = $this->notificationService->send($notification);
				if ($notificationSuccess) {
					$mailMessageKey = 'message.bookings.cancel.sendNotification.success';
					$mailMessageSeverity = AbstractMessage::OK;
					$notification->setSentAt(new \DateTime());
				} else {
					$mailMessageKey = 'message.bookings.cancel.sendNotification.error';
					$mailMessageSeverity = AbstractMessage::WARNING;
				}
				$this->notificationRepository->add($notification);
				$this->persistenceManager->persistAll();
				$reservation->addNotification($notification);

				$this->addFlashMessage($this->translate($mailMessageKey), null, $mailMessageSeverity);
			}
		}
		$this->forward('list');
	}

	/**
	 * action delete
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function deleteAction(Reservation $reservation) {
		$this->addFlashMessage(
			$this->translate('message.reservation.delete.success')
		);
		if ($participants = $reservation->getParticipants()) {
			foreach ($participants as $participant) {
				$reservation->getLesson()->removeParticipant($participant);
				$this->personRepository->remove($participant);
			}
		}
		if ($company = $reservation->getCompany()) {
			$this->companyRepository->remove($company);
		}
		if ($contact = $reservation->getContact()) {
			$this->personRepository->remove($contact);
		}
		$this->reservationRepository->remove($reservation);
		$this->redirect('list');
	}

	/**
	 * action newParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $newParticipant
	 * @ignorevalidation $newParticipant
	 * @return void
	 */
	public function newParticipantAction(Reservation $reservation, Person $newParticipant = null) {
		if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
			$reservation->setStatus(Reservation::STATUS_DRAFT);
		}
		if ($this->request->getOriginalRequest() instanceof Request) {
			$newParticipant = $this->request->getOriginalRequest()->getArgument('newParticipant');
		}
		$this->view->assignMultiple(
			[
				'newParticipant' => $newParticipant,
				'reservation' => $reservation
			]
		);
	}

	/**
	 * action createParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $newParticipant
	 * @return void
	 */
	public function createParticipantAction(Reservation $reservation, Person $newParticipant) {
		if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
			$reservation->setStatus(Reservation::STATUS_DRAFT);
		}
		$newParticipant->setType(Person::PERSON_TYPE_PARTICIPANT);
		$reservation->getLesson()->addParticipant($newParticipant);
		$reservation->addParticipant($newParticipant);
		$this->reservationRepository->update($reservation);
		$this->persistenceManager->persistAll();
		$this->addFlashMessage(
			$this->translate('message.reservation.createParticipant.success')
		);
		$this->redirect('edit', null, null, ['reservation' => $reservation]);
	}

	/**
	 * action removeParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $participant
	 * @param string $reason Reason for cancellation of reservation for this participant. Allowed:  'byOrganizer', 'withCosts', 'withoutCost'
	 * @return void
	 */
	public function removeParticipantAction(
		Reservation $reservation,
		Person $participant,
		$reason
	) {
		$reservation->removeParticipant($participant);
		$reservation->getLesson()->removeParticipant($participant);
		$this->personRepository->remove($participant);
		$this->addFlashMessage(
			$this->translate('message.reservation.removeParticipant.success')
		);

		if ($this->settings['bookings']['removeParticipant'][$reason]['confirm']['sendNotification']) {
			/**@var Notification $notification * */
			$notification = $this->objectManager->get(Notification::class);
			$bodytext = $this->notificationService->render(
				$this->settings['bookings']['removeParticipant'][$reason]['confirm']['templateFileName'],
				'html',
				'Bookings/Email/RemoveParticipant',
				[
					'reservation' => $reservation,
					'participant' => $participant,
					'reason' => $reason,
					'baseUrl' => $this->getBaseUrlForFrontend()
				]
			);
			$notification->setRecipient($reservation->getContact()->getEmail());
			$notification->setBodytext($bodytext);
			$notification->setSubject($this->settings['bookings']['removeParticipant'][$reason]['confirm']['subject']);
			$notification->setSender($this->settings['bookings']['removeParticipant'][$reason]['confirm']['fromEmail']);
			$notification->setReservation($reservation);
			$notificationSuccess = $this->notificationService->send($notification);
			if ($notificationSuccess) {
				$mailMessageKey = 'message.bookings.cancel.sendNotification.success';
				$mailMessageSeverity = AbstractMessage::OK;
				$notification->setSentAt(new \DateTime());
			} else {
				$mailMessageKey = 'message.bookings.cancel.sendNotification.error';
				$mailMessageSeverity = AbstractMessage::WARNING;
			}
			$this->notificationRepository->add($notification);
			$this->persistenceManager->persistAll();
			$reservation->addNotification($notification);

			$this->addFlashMessage($this->translate($mailMessageKey), null, $mailMessageSeverity);
		}
		$this->redirect('edit', null, null, ['reservation' => $reservation]);
	}

	/**
	 * Returns custom error flash messages, or
	 * display no flash message at all on errors.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
	 */
	protected function getErrorFlashMessage() {
		$key = 'error' . '.' . $this->settingsUtility->getControllerKey($this) . '.'
			. str_replace('Action', '', $this->actionMethodName);
		$message = $this->translate($key);
		if ($message == null) {
			return FALSE;
		} else {
			return $message;
		}
	}

	/**
	 * new notification action
	 *
	 * @param \array $reservations
	 * @param \CPSIT\T3eventsCourse\Domain\Model\Schedule $lesson
	 * @param \Webfox\T3events\Domain\Model\Notification $newNotification
	 * @ignorevalidation $newNotification
	 * @return void
	 */
	public function newNotificationAction($reservations, $lesson, $newNotification = null) {
		$uidList = implode(',', $reservations);
		$reservations = $this->reservationRepository->findMultipleByUid($uidList);
		$this->view->assignMultiple(
			[
				'notification' => $newNotification,
				'lesson' => $lesson,
				'reservations' => $reservations->toArray()
			]
		);
	}

	/**
	 * create notification action
	 * Sends a notification to all contact persons of the given lesson/reservations
	 *
	 * @param \Webfox\T3events\Domain\Model\Notification $newNotification
	 * @param \array $reservations
	 */
	public function createNotificationAction($newNotification, $reservations) {
		$uidList = implode(',', $reservations);
		$reservations = $this->reservationRepository->findMultipleByUid($uidList);
		$succesfullSent = [];
		$sendingFailed = [];

		foreach ($reservations as $reservation) {
			$notification = $this->notificationService->duplicate($newNotification);
			$notification->setRecipient($reservation->getContact()->getEmail());
			$notification->setSender($this->settings['bookings']['notify']['fromEmail']);
			$notification->setFormat('plain');
			$notificationSuccess = $this->notificationService->send($notification);

			if ($notificationSuccess) {
				$succesfullSent[] = $notification->getRecipient();
				$notification->setSentAt(new \DateTime());
			} else {
				$sendingFailed[] = $notification->getRecipient();
			}
			$this->notificationRepository->add($notification);
			$this->persistenceManager->persistAll();
			$reservation->addNotification($notification);
			$this->reservationRepository->update($reservation);
		}
		$this->persistenceManager->persistAll();
		if (count($succesfullSent)) {
			$this->addFlashMessage(
				implode('<br />', $succesfullSent),
				$this->translate('message.bookings.sendNotification.successForRecipients'),
				AbstractMessage::OK);
		}
		if (count($sendingFailed)) {
			$this->addFlashMessage(
				implode('<br />', $sendingFailed),
				$this->translate('message.bookings.sendNotification.errorForRecipients'),
				AbstractMessage::ERROR);
		}
		$this->forward('list');
	}

	/**
	 * Creates a demand from given settings
	 *
	 * @param $settings
	 * @return ReservationDemand
	 */
	protected function createDemandFromSettings($settings) {
		/** @var ReservationDemand $demand */
		$demand = $this->objectManager->get(ReservationDemand::class);

		foreach ($settings as $propertyName => $propertyValue) {
			if (empty($propertyValue)) {
				continue;
			}
			switch ($propertyName) {
				case 'maxItems':
					$demand->setLimit($propertyValue);
					break;
				// all following fall through (see below)
				case 'periodType':
				case 'periodStart':
				case 'periodEndDate':
				case 'periodDuration':
				case 'search':
					break;
				default:
					if (ObjectAccess::isPropertySettable($demand, $propertyName)) {
						ObjectAccess::setProperty($demand, $propertyName, $propertyValue);
					}
			}
		}

		if (isset($settings['period'])) {
			$demand->setPeriod($settings['period']);
			if ($demand->getPeriod() === 'futureOnly'
				OR $demand->getPeriod() == 'pastOnly'
			) {
				$timeZone = new \DateTimeZone(date_default_timezone_get());
				$demand->setStartDate(new \DateTime('midnight', $timeZone));
			}
		}
		if (isset($settings['order'])
			AND is_string($settings['order'])
		) {
			$demand->setOrder($settings['order']);
		}
		if (isset($settings['maxItems'])) {
			$demand->setLimit($settings['maxItems']);
		}

		return $demand;
	}

	/**
	 * Download action
	 *
	 * @param array $reservations
	 * @param string $fileExtension
	 */
	public function downloadAction($reservations = null, $fileExtension='csv') {
		if (is_null($reservations)) {
			$demand = $this->createDemandFromSettings($this->settings);
			$reservationResult = $this->reservationRepository->findDemanded($demand);
		} else {
			$reservationIdList = implode(',', $reservations);
			$reservationResult = $this->reservationRepository->findMultipleByUid($reservationIdList);
		}
		$this->view->assign('reservations', $reservationResult);
		$objectForFileName = $reservationResult->getFirst();

		echo($this->getContentForDownload($fileExtension, $objectForFileName));
		return;
	}

}

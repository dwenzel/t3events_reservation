<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

/**
 * ReservationController
 */
use CPSIT\T3eventsCourse\Controller\AbstractController;
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
class BookingsController extends AbstractController {

	/**
	 * reservationRepository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
	 * @inject
	 */
	protected $reservationRepository = NULL;

	/**
	 * Company Repository
	 *
	 * @var \Webfox\T3events\Domain\Repository\CompanyRepository
	 * @inject
	 */
	protected $companyRepository = NULL;

	/**
	 * Participant Repository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository
	 * @inject
	 */
	protected $personRepository = NULL;

	/**
	 * Notification Repository
	 *
	 * @var \Webfox\T3events\Domain\Repository\NotificationRepository
	 * @inject
	 */
	protected $notificationRepository = NULL;

	/**
	 * List action
	 *
	 * @return void
	 */
	public function listAction() {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $demand */
		$demand = $this->createDemandFromSettings($this->settings['bookings']['list']);
		$reservations = $this->reservationRepository->findDemanded($demand);
		$this->view->assign('reservations', $reservations);
	}

	/**
	 * action show
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @return void
	 */
	public function showAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation) {
		$this->view->assign('reservation', $reservation);
	}

	/**
	 * Edit action
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @return void
	 */
	public function editAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation) {
		$this->view->assign('reservation', $reservation);
	}

	/**
	 * Update action
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @return void
	 */
	public function updateAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation) {
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
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @param \string $reason
	 * @return void
	 */
	public function cancelAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation, $reason) {
		switch ($reason) {
			case 'byDakosy':
				$newStatus = \CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_CANCELED_BY_SUPPLIER;
				break;
			case 'withCosts':
				$newStatus = \CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_CANCELED_WITH_COSTS;
				break;
			case 'noCharge':
				$newStatus = \CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_CANCELED_NO_CHARGE;
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
				/**@var \Webfox\T3events\Domain\Model\Notification $notification * */
				$notification = $this->objectManager->get('\\Webfox\\T3events\\Domain\\Model\\Notification');
				$bodytext = $this->notificationService->render(
					$this->settings['bookings']['cancel'][$reason]['confirm']['templateFileName'],
					'html',
					'Bookings/Email/Cancel',
					array('reservation' => $reservation,
						'baseUrl' => $this->getBaseUrlForFrontend())
				);
				$notification->setRecipient($reservation->getContact()->getEmail());
				$notification->setBodytext($bodytext);
				$notification->setSubject($this->settings['bookings']['cancel'][$reason]['confirm']['subject']);
				$notification->setSender($this->settings['bookings']['cancel'][$reason]['confirm']['fromEmail']);
				$notification->setReservation($reservation);
				$notificationSuccess = $this->notificationService->send($notification);
				if ($notificationSuccess) {
					$mailMessageKey = 'message.bookings.cancel.sendNotification.success';
					$mailMessageSeverity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK;
					$notification->setSentAt(new \DateTime());
				} else {
					$mailMessageKey = 'message.bookings.cancel.sendNotification.error';
					$mailMessageSeverity = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
				}
				$this->notificationRepository->add($notification);
				$this->persistenceManager->persistAll();
				$reservation->addNotification($notification);

				$this->addFlashMessage($this->translate($mailMessageKey), NULL, $mailMessageSeverity);
			}
		}
		$this->forward('list');
	}

	/**
	 * action delete
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @return void
	 */
	public function deleteAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation) {
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
*@param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $newParticipant
	 * @ignorevalidation $newParticipant
	 * @return void
	 */
	public function newParticipantAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation,
		\CPSIT\T3eventsReservation\Domain\Model\Person $newParticipant = NULL) {
		if (!$reservation->getStatus() == \CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_DRAFT) {
			$reservation->setStatus(\CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_DRAFT);
		}
		if ($this->request->getOriginalRequest() instanceof \TYPO3\CMS\Extbase\Mvc\Request) {
			$newParticipant = $this->request->getOriginalRequest()->getArgument('newParticipant');
		}
		$this->view->assignMultiple(
			array(
				'newParticipant' => $newParticipant,
				'reservation' => $reservation
			)
		);
	}

	/**
	 * action createParticipant


*
*@param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $newParticipant
	 * @return void
	 */
	public function createParticipantAction(\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation, \CPSIT\T3eventsReservation\Domain\Model\Person $newParticipant) {
		if (!$reservation->getStatus() == \CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_DRAFT) {
			$reservation->setStatus(\CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_DRAFT);
		}
		$newParticipant->setType(\CPSIT\T3eventsReservation\Domain\Model\Person::PERSON_TYPE_PARTICIPANT);
		$reservation->getLesson()->addParticipant($newParticipant);
		$reservation->addParticipant($newParticipant);
		$this->reservationRepository->update($reservation);
		$this->persistenceManager->persistAll();
		$this->addFlashMessage(
			$this->translate('message.reservation.createParticipant.success')
		);
		$this->redirect('edit', NULL, NULL, array('reservation' => $reservation));
	}

	/**
	 * action removeParticipant


*
*@param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participant
	 * @param \string $reason Reason for cancellation of reservation for this participant. Allowed:  'byDakosy', 'withCosts', 'withoutCost'
	 * @return void
	 */
	public function removeParticipantAction(
		\CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation,
		\CPSIT\T3eventsReservation\Domain\Model\Person $participant,
		$reason
	) {
		$reservation->removeParticipant($participant);
		$reservation->getLesson()->removeParticipant($participant);
		$this->personRepository->remove($participant);
		$this->addFlashMessage(
			$this->translate('message.reservation.removeParticipant.success')
		);

		if ($this->settings['bookings']['removeParticipant'][$reason]['confirm']['sendNotification']) {
			/**@var \Webfox\T3events\Domain\Model\Notification $notification * */
			$notification = $this->objectManager->get('\\Webfox\\T3events\\Domain\\Model\\Notification');
			$bodytext = $this->notificationService->render(
				$this->settings['bookings']['removeParticipant'][$reason]['confirm']['templateFileName'],
				'html',
				'Bookings/Email/RemoveParticipant',
				array(
					'reservation' => $reservation,
					'participant' => $participant,
					'reason' => $reason,
					'baseUrl' => $this->getBaseUrlForFrontend()
				)
			);
			$notification->setRecipient($reservation->getContact()->getEmail());
			$notification->setBodytext($bodytext);
			$notification->setSubject($this->settings['bookings']['removeParticipant'][$reason]['confirm']['subject']);
			$notification->setSender($this->settings['bookings']['removeParticipant'][$reason]['confirm']['fromEmail']);
			$notification->setReservation($reservation);
			$notificationSuccess = $this->notificationService->send($notification);
			if ($notificationSuccess) {
				$mailMessageKey = 'message.bookings.cancel.sendNotification.success';
				$mailMessageSeverity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK;
				$notification->setSentAt(new \DateTime());
			} else {
				$mailMessageKey = 'message.bookings.cancel.sendNotification.error';
				$mailMessageSeverity = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
			}
			$this->notificationRepository->add($notification);
			$this->persistenceManager->persistAll();
			$reservation->addNotification($notification);

			$this->addFlashMessage($this->translate($mailMessageKey), NULL, $mailMessageSeverity);
		}
		$this->redirect('edit', NULL, NULL, array('reservation' => $reservation));
	}

	/**
	 * Returns custom error flash messages, or
	 * display no flash message at all on errors.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
	 */
	protected function getErrorFlashMessage() {
		$key = 'error' . '.administration.' . str_replace('Action', '', $this->actionMethodName) . '.' . $this->errorMessage;
		$message = $this->translate($key);
		if ($message == NULL) {
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
	public function newNotificationAction($reservations, $lesson, $newNotification = NULL) {
		$uidList = implode(',', $reservations);
		$reservations = $this->reservationRepository->findMultipleByUid($uidList);
		$this->view->assignMultiple(
			array(
				'notification' => $newNotification,
				'lesson' => $lesson,
				'reservations' => $reservations->toArray()
			)
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
		$succesfullSent = array();
		$sendingFailed = array();

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
				\TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
		}
		if (count($sendingFailed)) {
			$this->addFlashMessage(
				implode('<br />', $sendingFailed),
				$this->translate('message.bookings.sendNotification.errorForRecipients'),
				\TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
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
		$demand = $this->objectManager->get('CPSIT\\T3eventsReservation\\Domain\\Model\\Dto\\ReservationDemand');
		if (isset($settings['period'])) {
			$demand->setPeriod($settings['period']);
			if ($demand->getPeriod() === 'futureOnly'
				OR $demand->getPeriod() == 'pastOnly'
			) {
				$demand->setLessonDate(new \DateTime('midnight'));
			}
		}
		if (isset($settings['order'])
			AND is_string($settings['order'])
		) {
			$demand->setOrder($settings['order']);
		}

		return $demand;
	}

	/**
	 * Get frontend base url as configured in TypoScript
	 * Pass this as a variable when rendering fluid templates in Backend context for instance
	 * if you want to render images in emails.
	 *
	 * @return \string
	 */
	protected function getBaseUrlForFrontend() {
		$typoScriptConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

		return $typoScriptConfiguration['config.']['baseURL'];
	}
}

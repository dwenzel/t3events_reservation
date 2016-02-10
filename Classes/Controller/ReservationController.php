<?php
namespace CPSIT\T3eventsReservation\Controller;

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
use CPSIT\T3eventsReservation\Domain\Model\BookableInterface;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\Exception;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use Webfox\T3events\Controller\AbstractController;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use Webfox\T3events\Domain\Model\Performance;
use Webfox\T3events\Session\Typo3Session;

/**
 * ReservationController
 */
class ReservationController extends AbstractController {
	const SESSION_NAME_SPACE = 'tx_t3eventsreservation';

	/**
	 * Persistence Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * Notification Service
	 *
	 * @var \Webfox\T3events\Service\NotificationService
	 * @inject
	 */
	protected $notificationService;

	/**
	 * reservationRepository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
	 * @inject
	 */
	protected $reservationRepository = NULL;

	/**
	 * Lesson Repository
	 *
	 * @var \Webfox\T3events\Domain\Repository\PerformanceRepository
	 * @inject
	 */
	protected $lessonRepository = NULL;

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
	 * @var \Webfox\T3events\Session\SessionInterface
	 */
	protected $session;

	/**
	 * Initialize Action
	 */
	public function initializeAction() {
		parent::initializeAction();
		$this->session = $this->objectManager->get(Typo3Session::class, self::SESSION_NAME_SPACE);
	}

	/**
	 * action show
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function showAction(Reservation $reservation) {
		if ($this->isAccessAllowed($reservation)) {
			$this->view->assign('reservation', $reservation);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * action new
	 *
	 * @param BookableInterface|Performance $lesson
	 * @param Reservation $newReservation
	 * @ignorevalidation $newReservation
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	public function newAction(Performance $lesson = NULL, Reservation $newReservation = NULL) {
		//@todo: check for existing session key and prevent creating new reservation
		if (is_null($lesson)) {
			$error = 'message.selectLesson';
		} elseif (!$lesson->getFreePlaces()) {
			$error = 'message.noFreePlacesForThisLesson';
		}
		if (isset($error)) {
			$this->addFlashMessage(
				$this->translate($error), '', AbstractMessage::ERROR, TRUE
			);
			// todo make redirect target configurable or use AbstractController->handleEntityNotFoundError
			$this->redirect('list', 'Performance', 't3events', [], $this->settings['schedule']['listPid']);
		}
		if ($this->request->getOriginalRequest() instanceof Request) {
			$newReservation = $this->request->getOriginalRequest()->getArgument('newReservation');
		}
		$this->view->assignMultiple(
			[
				'newReservation' => $newReservation,
				'lesson' => $lesson
			]
		);
	}

	/**
	 * action create
	 *
	 * @param Reservation $newReservation
	 * @return void
	 */
	public function createAction(Reservation $newReservation) {
		//@todo: check for existing session key and prevent creating new reservation
		if (is_null($newReservation->getUid())) {
			if ($contact = $newReservation->getContact()) {
				$contact->setReservation($newReservation);
			}
			$newReservation->setStatus(Reservation::STATUS_NEW);
			if ($newReservation->getContactIsParticipant() && is_object($contact)) {
				$participant = new Person();
				foreach (ObjectAccess::getGettableProperties($contact) as $propertyName=>$propertyValue) {
					if (ObjectAccess::isPropertySettable($participant, $propertyName)) {
						ObjectAccess::setProperty($participant, $propertyName, $propertyValue);
					}
				}
				$participant->setType(Person::PERSON_TYPE_PARTICIPANT);
				$newReservation->addParticipant($participant);
				$newReservation->getLesson()->addParticipant($participant);
			}
			$this->addFlashMessage(
				$this->translate('message.reservation.create.success')
			);
			$this->reservationRepository->add($newReservation);
			$this->persistenceManager->persistAll();
			$this->session->set('reservationUid', $newReservation->getUid());
			$this->forward('edit', NULL, NULL, ['reservation' => $newReservation]);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * action edit
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function editAction(Reservation $reservation) {
		if (!$this->isAccessAllowed($reservation)) {
			$this->denyAccess();
		} else {
			$this->reservationRepository->update($reservation);
			$this->persistenceManager->persistAll();

			$this->view->assignMultiple(
				[
					'reservation' => $reservation
				]
			);
		}
	}

	/**
	 * action delete
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function deleteAction(Reservation $reservation) {
		if ($this->isAccessAllowed($reservation)) {
			$this->addFlashMessage(
				$this->translate('message.reservation.delete.success')
			);
			if ($participants = $reservation->getParticipants()) {
				foreach ($participants as $participant) {
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
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * action newParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $newParticipant
	 * @ignorevalidation $newParticipant
	 * @return void
	 */
	public function newParticipantAction(Reservation $reservation, Person $newParticipant = NULL) {
		if ($this->isAccessAllowed($reservation)
			&& ($reservation->getStatus() == Reservation::STATUS_DRAFT
			|| $reservation->getStatus() == Reservation::STATUS_NEW)
		) {
			if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
				$reservation->setStatus(Reservation::STATUS_DRAFT);
			}
			if (!$reservation->getLesson()->getFreePlaces()) {
				$this->addFlashMessage(
					$this->translate('message.noFreePlacesForThisLesson'), '', AbstractMessage::ERROR, TRUE
				);
			} elseif (!count($reservation->getParticipants())) {
				$this->addFlashMessage(
					$this->translate('message.reservation.newParticipant.addAtLeastOneParticipant'), '', AbstractMessage::NOTICE
				);
			}
			if ($this->request->getOriginalRequest() instanceof \TYPO3\CMS\Extbase\Mvc\Request) {
				$newParticipant = $this->request->getOriginalRequest()->getArgument('newParticipant');
			}
			$this->view->assignMultiple(
				[
					'newParticipant' => $newParticipant,
					'reservation' => $reservation
				]
			);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * action createParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $newParticipant
	 * @return void
	 */
	public function createParticipantAction(Reservation $reservation, Person $newParticipant) {
		if (!$this->isAccessAllowed($reservation)) {
			$this->denyAccess();
		} else {
			if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
				$reservation->setStatus(Reservation::STATUS_DRAFT);
			}
			if ($reservation->getLesson()->getFreePlaces()) {
				$newParticipant->setReservation($reservation);
				$newParticipant->setType(Person::PERSON_TYPE_PARTICIPANT);
				$reservation->addParticipant($newParticipant);
				$reservation->getLesson()->addParticipant($newParticipant);
				$this->reservationRepository->update($reservation);
				$this->lessonRepository->update($reservation->getLesson());
				$this->persistenceManager->persistAll();
				$this->addFlashMessage(
					$this->translate('message.reservation.createParticipant.success')
				);
			}

			$this->redirect(
				'edit',
				NULL,
				NULL,
				['reservation' => $reservation]
			);
		}
	}

	/**
	 * Checkout Action
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function checkoutAction(Reservation $reservation) {
		if ($this->isAccessAllowed($reservation)) {
			$this->view->assign('reservation', $reservation);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * Confirm Action
	 *
	 * @param Reservation $reservation
	 * @return void
	 */
	public function confirmAction(Reservation $reservation) {
		if ($this->isAccessAllowed($reservation)) {
			// @todo optionally read reservation status from settings
			$reservation->setStatus(Reservation::STATUS_SUBMITTED);
			$this->addFlashMessage(
				$this->translate('message.reservation.confirm.success')
			);
			foreach ($this->settings['reservation']['confirm']['notification'] as $identifier => $config) {
				$this->sendNotification($reservation, $identifier, $config);
			}
			$this->reservationRepository->update($reservation);
			$this->forward('show', NULL, NULL, ['reservation' => $reservation]);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * action removeParticipant
	 *
	 * @param Reservation $reservation
	 * @param Person $participant
	 * @return void
	 */
	public function removeParticipantAction(
		Reservation $reservation,
		Person $participant
	) {
		if ($this->isAccessAllowed($reservation)) {
			$reservation->removeParticipant($participant);
			$reservation->getLesson()->removeParticipant($participant);
			$this->personRepository->remove($participant);
			$this->addFlashMessage(
				$this->translate('message.reservation.removeParticipant.success')
			);
			$this->redirect('edit', NULL, NULL, ['reservation' => $reservation]);
		} else {
			$this->denyAccess();
		}
	}

	/**
	 * Deny access
	 * Issues an error message and redirects
	 *
	 * @return void
	 */
	protected function denyAccess() {
		$this->clearCacheOnError();
		$this->addFlashMessage(
			$this->translate(
				'error.reservation.' . str_replace('Action', '', $this->actionMethodName) . '.accessDenied'),
				'',
				AbstractMessage::ERROR,
				true
		);
		// todo make redirect target configurable or use AbstractController->handleEntityNotFoundError
		$this->redirect('list', 'Performance', 't3events', [], $this->settings['schedule']['listPid']);
	}

	/**
	 * Checks if access is allowed
	 *
	 * @param \object $object Object which should be accessed
	 * @return \boolean
	 */
	public function isAccessAllowed($object) {
		$isAllowed = FALSE;
		if ($object instanceof Reservation) {
			$isAllowed = ($this->session->has('reservationUid')
				&& method_exists($object, 'getUid')
				&& ((int) $this->session->get('reservationUid') === $object->getUid())
			);
		}

		return $isAllowed;
	}

	/**
	 * @param Reservation $reservation
	 * @param string $identifier
	 * @param array $config
	 * @return bool
	 * @throws Exception
	 */
	protected function sendNotification(Reservation $reservation, $identifier, $config) {
		if (isset($config['fromEmail'])) {
			$sender = $config['fromEmail'];
		} else {
			throw new Exception('Missing sender for email notification', 1454518855);
		}
		if (isset($config['toEmail'])) {
			if (isset($config['toEmail']['field']) && is_string($config['toEmail']['field'])) {
				$recipientEmail = ObjectAccess::getPropertyPath($reservation, $config['toEmail']['field']);
			} elseif (is_string($config['toEmail'])) {
				$recipientEmail = $config['toEmail'];
			}
		}

		if (!isset($recipientEmail)) {
			throw new Exception('Missing recipient for email notification ' . $identifier, 1454865240);
		}

		if (isset($config['subject'])) {
			$subject = $config['subject'];
		} else {
			throw new Exception('Missing subject for email notification ' . $identifier, 1454865250);
		}

		$format = 'plain';
		if (isset($config['format']) && is_string($config['format'])) {
			$format = $config['format'];
		}
		$fileName = ucfirst($identifier);
		if (isset($config['template']['fileName'])) {
			$fileName = $config['template']['fileName'];
		}
		$folderName = 'Reservation/Email';
		if (isset($config['template']['folderName'])) {
			$folderName = $config['template']['folderName'];
		}
		/** @var Notification $notification */
		$notification = $this->objectManager->get(Notification::class);
		$notification->setRecipient($recipientEmail);
		$notification->setSender($sender);
		$notification->setSubject($subject);
		$notification->setFormat($format);
		$bodyText = $this->notificationService->render(
			$fileName,
			$format,
			$folderName,
			['reservation' => $reservation, 'settings' => $this->settings]
		);
		$notification->setBodytext($bodyText);
		$reservation->addNotification($notification);

		return $this->notificationService->send($notification);
	}


	/**
	 * Translate a given key
	 *
	 * @param \string $key
	 * @param \string $extension
	 * @param \array $arguments
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function translate($key, $extension = 't3events_reservation', $arguments = NULL) {
		return parent::translate($key, $extension, $arguments);
	}
}

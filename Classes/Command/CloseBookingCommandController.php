<?php
namespace CPSIT\T3eventsReservation\Command;

/**
 * @package t3events
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

use CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand;
use CPSIT\T3eventsCourse\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;

/***************************************************************
 * <?php
 *  Copyright notice
 *  (c) 2013 Dirk Wenzel <wenzel@webfox01.de>, Agentur Webfox
 *  Michael Kasten <kasten@webfox01.de>, Agentur Webfox
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
use TYPO3\CMS\Core\Exception;

/**
 * Class CloseBookingCommandController
 *
 * @package CPSIT\T3eventsReservation\Command
 */
class CloseBookingCommandController extends CommandController {

	/**
	 * Persistence Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * View
	 *
	 * @var \TYPO3\CMS\Fluid\View\StandaloneView
	 * @inject
	 */
	protected $view;

	/**
	 * Configuration Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Notification Service
	 *
	 * @var \DWenzel\T3events\Service\NotificationService
	 * @inject
	 */
	protected $notificationService;

	/**
	 * Schedule Repository
	 *
	 * @var \CPSIT\T3eventsCourse\Domain\Repository\ScheduleRepository
	 * @inject
	 */
	protected $lessonRepository;

	/**
	 * Reservation Repository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
	 * @inject
	 */
	protected $reservationRepository;

	/**
	 * Person Repository


*
*@var \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository
	 * @inject
	 */
	protected $personRepository;

	/**
	 * Close Bookings
	 * Searches for lessons with expired date and hides them.
	 * Matching reservations are set to 'closed' state and hidden too.
	 * A list of all participants is being generated and send via email attachment .
	 *
	 * @param string $email E-Mail
	 * @param boolean $dryRun Does not persist changes but sends the generated email with attachement.
	 * @throws \TYPO3\CMS\Core\Exception
	 * @return void
	 */
	public function closeBookingCommand($email, $dryRun = NULL) {
		$lessons = $this->hideExpiredLessons($dryRun);
		$reservations = $this->closeReservations($dryRun);

		// only send an email if at least one lesson or one reservation has been closed (and email address is given)
		if ((count($lessons) OR count($reservations)) AND !empty($email)) {
			try {
				$this->notificationService->notify(
					$email, 't3events@cps-it.de', 'close booking', 'Email', NULL, 'CloseBooking', array(
					'dryRun' => $dryRun,
					'lessons' => $lessons,
					'reservations' => $reservations
				), array(
						array(
							'variables' => array(
								'lessons' => $lessons,
								'reservations' => $reservations
							),
							'templateName' => 'Download',
							'folderName' => 'CloseBooking',
							'fileName' => 'anhang.xls',
							'mimeType' => 'application/vnd.ms-excel'
						)
					)
				);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}

	/**
	 * Sends an email about expired reservations and lessons.
	 * Expired are lessons where the reservation deadline is
	 * before yesterday 0:00:00 h.
	 * A MS Excel file with the results will be attached to the email
	 *
	 * @param \string $email Recipients email address
	 * @param null $dryRun
	 * @throws Exception
	 */
	public function reportExpiredCommand($email) {
		$lessons = $this->getLessonsWithExpiredDeadline();
		$reservationDemand = $this->createReservationDemandByLessonDeadline('yesterday');
		$reservations = $this->reservationRepository->findDemanded($reservationDemand);

		if (
			!empty($email)
			AND (count($lessons) OR count($reservations))
		) {
			try {
				$this->notificationService->notify(
					$email, 'no-reply@example.com', 'Abgelaufene Termine und Reservierungen in ihrem Veranstaltungssystem', 'Email', NULL, 'ReportExpired', array(
					'lessons' => $lessons,
					'reservations' => $reservations
				), array(
						array(
							'variables' => array(
								'lessons' => $lessons,
								'reservations' => $reservations
							),
							'templateName' => 'Download',
							'folderName' => 'CloseBooking',
							'fileName' => 'anhang.xls',
							'mimeType' => 'application/vnd.ms-excel'
						)
					)
				);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}

	/**
	 * Cleanup incomplete reservations.
	 * Removes all reservations which are older then 'age' seconds and
	 * of status 'new' or 'draft'
	 *
	 * @param int $age Minimum age of reservations
	 * @param string $email Email address for notification
	 * @param boolean $dryRun Dry run
	 * @return \boolean Return TRUE
	 * @throws Exception Throws an exception if send email fails
	 */
	public function cleanupIncompleteReservationsCommand($age, $email, $dryRun = NULL) {
		$deletedCount = $this->deleteInvalidReservations($dryRun, $age);

		if (!empty($email)) {
			try {
				return $this->notificationService->notify(
					$email,
					'no-reply@example.com',
					'cleanup incomplete reservations',
					'Email',
					NULL,
					'CleanupIncomplete',
					array(
						'dryRun' => $dryRun,
						'age' => $age,
						'deletedCount' => $deletedCount
					)
				);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}

		return TRUE;
	}

	/**
	 * Hide expired lessons
	 * Hides all lesson which meet the given constraints. Returns a query result with matching lessons.
	 *
	 * @param boolean $dryRun
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult | NULL
	 */
	protected function hideExpiredLessons($dryRun) {
		$demand = $this->createDemandForExpiredLessons();
		$lessons = $this->lessonRepository->findDemanded($demand);

		if (!$dryRun) {
			foreach ($lessons as $lesson) {
				$lesson->setHidden(1);
				$this->lessonRepository->update($lesson);
			}
		}

		return $lessons;
	}

	/**
	 * Closes expired reservations
	 *
	 * @param boolean $dryRun
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult | NULL
	 */
	protected function closeReservations($dryRun) {
		$reservationDemand = $this->createReservationDemandByExpiredLessonDate();
		$reservations = $this->reservationRepository->findDemanded($reservationDemand);

		if (!$dryRun) {
			foreach ($reservations as $reservation) {
				$reservation->setHidden(1);
				$reservation->setStatus(\CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_CLOSED);
				$this->reservationRepository->update($reservation);
			}
		}

		return $reservations;
	}

	/**
	 * Gets all expired lessons
	 *
	 * @param \string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */
	protected function getLessonsWithExpiredDeadline($date = NULL) {
		/** @var ScheduleDemand $lessonDemand */
		$lessonDemand = $this->createDemandForLessonsWithExpiredDeadline($date);

		return $this->lessonRepository->findDemanded($lessonDemand);
	}

	/**
	 * Delete expired reservations
	 * I.e. reservations of status new which are older then a given minAge
	 *
	 * @param \boolean $dryRun
	 * @param \int $age Age in seconds
	 * @return \int
	 */
	protected function deleteInvalidReservations($dryRun, $age) {
		$reservations = $this->getInvalidReservations($age);
		$deletedCount = 0;

		if (!$dryRun) {
			/** @var Reservation $reservation */
			foreach ($reservations as $reservation) {
				/** @var Schedule $lesson */
				if ($lesson = $reservation->getLesson()) {
					/** @var Person $participants */
					if ($participants = $reservation->getParticipants()) {
						foreach ($participants as $participant) {
							$lesson->removeParticipant($participant);
							$this->personRepository->remove($participant);
						}
					}
				}
				$this->reservationRepository->remove($reservation);
				$deletedCount++;
			}
		}

		return $dryRun ? count($reservations) : $deletedCount;
	}

	/**
	 * @param \int $age Age in seconds
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected function getInvalidReservations($age) {
		$reservationDemand = $this->createInvalidReservationsDemand($age);

		return $this->reservationRepository->findDemanded($reservationDemand);
	}

	/**
	 * Returns a lesson demand object for lessons with expired registration deadline.
	 * A lesson will considered expired when its registration deadline is older than the given date.
	 * Default is 'now'
	 *
	 * @param \string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
	 * @return \CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand $lessonDemand
	 */
	protected function createDemandForLessonsWithExpiredDeadline($date = 'now') {
		/** @var \CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand $lessonDemand */
		$lessonDemand = $this->objectManager->get(ScheduleDemand::class);
		$lessonDemand->setDeadlineBefore(new \DateTime($date));

		return $lessonDemand;
	}

	/**
	 * Returns a lesson demand object for expired lessons.
	 * A lesson will considered expired when its date is older than the given date.
	 * Default is 'now'
	 *
	 * @param \string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
	 * @return \CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand $lessonDemand
	 */
	protected function createDemandForExpiredLessons($date = 'now') {
		/** @var \CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand $lessonDemand */
		$lessonDemand = $this->objectManager->get('CPSIT\\T3eventsCourse\\Domain\\Model\\Dto\\ScheduleDemand');
		$lessonDemand->setPeriod('pastOnly');
		$lessonDemand->setDate(new \DateTime($date));

		return $lessonDemand;
	}

	/**
	 * Create a demand for invalid reservations. I.e. unfinished reservations of a certain minAge
	 *
	 * @param \int $age Age in seconds
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand
	 */
	protected function createInvalidReservationsDemand($age) {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand */
		$reservationDemand = $this->objectManager->get('CPSIT\\T3eventsReservation\\Domain\\Model\\Dto\\ReservationDemand');
		$expiredStatus = array(Reservation::STATUS_NEW, Reservation::STATUS_DRAFT);
		$reservationDemand->setStatus(implode(',', $expiredStatus));
		$reservationDemand->setMinAge($age);

		return $reservationDemand;
	}

	/**
	 * Returns a reservation demand object for reservations
	 * The demand includes all reservations with status 'submitted'
	 * where the registration deadline of its lessons is beyond
	 * a given date and time (default 'now')
	 *
	 * @param \string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand
	 */
	protected function createReservationDemandByLessonDeadline($date = 'now') {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand */
		$reservationDemand = $this->objectManager->get('CPSIT\\T3eventsReservation\\Domain\\Model\\Dto\\ReservationDemand');
		$reservationDemand->setStatus(\CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_SUBMITTED);
		$reservationDemand->setLessonDeadline(new \DateTime($date));

		return $reservationDemand;
	}


	/**
	 * Returns a reservation demand object for reservations
	 * The demand includes all reservations with status 'submitted'
	 * where the date of its lessons is beyond
	 * a given date and time (default 'now')
	 *
	 * @param \string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand
	 */
	protected function createReservationDemandByExpiredLessonDate($date = 'now') {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $reservationDemand */
		$reservationDemand = $this->objectManager->get('CPSIT\\T3eventsReservation\\Domain\\Model\\Dto\\ReservationDemand');
		$reservationDemand->setStatus(\CPSIT\T3eventsReservation\Domain\Model\Reservation::STATUS_SUBMITTED);
		$reservationDemand->setLessonDate(new \DateTime($date));
		$reservationDemand->setPeriod('pastOnly');

		return $reservationDemand;
	}
}


<?php
namespace CPSIT\T3eventsReservation\Command;

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
use CPSIT\T3eventsCourse\Domain\Model\Dto\ScheduleDemand;
use CPSIT\T3eventsCourse\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ScheduleRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Configuration\ConfigurationManagerTrait;
use DWenzel\T3events\Controller\NotificationServiceTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CloseBookingCommandController
 *
 * FIXME This class requires the base extension t3events and t3events_course
 * (which is not and should not be a requirement of this package)
 */
class CloseBookingCommandController extends CommandController
{
    use ConfigurationManagerTrait, NotificationServiceTrait, PersistenceManagerTrait,
        PersonRepositoryTrait, ReservationRepositoryTrait, ScheduleRepositoryTrait;

    const TEMPLATE_EMAIL = 'Email';
    const TEMPLATE_DOWNLOAD = 'Download';
    const FOLDER_CLOSE_BOOKING = 'CloseBooking';
    const FOLDER_CLEANUP_INCOMPLETE = 'CleanupIncomplete';
    const FOLDER_REPORT_EXPIRED = 'ReportExpired';

    /**
     * View
     *
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     * @inject
     */
    protected $view;

    /**
     * Cleanup incomplete reservations.
     * Removes all reservations which are older then 'age' seconds and
     * of status 'new' or 'draft'
     *
     * @param int $age Minimum age of reservations
     * @param string $email Email address for notification
     * @param boolean $dryRun Dry run
     * @return boolean Return TRUE
     * @throws Exception Throws an exception if send email fails
     */
    public function cleanupIncompleteReservationsCommand($age, $email, $dryRun = NULL)
    {
        $deletedCount = $this->deleteInvalidReservations($dryRun, $age);

        if (!empty($email)) {
            try {
                return $this->notificationService->notify(
                    $email,
                    'no-reply@example.com',
                    'cleanup incomplete reservations',
                    static::TEMPLATE_EMAIL,
                    NULL,
                    static::FOLDER_CLEANUP_INCOMPLETE,
                    [
                        'dryRun' => $dryRun,
                        'age' => $age,
                        'deletedCount' => $deletedCount
                    ]
                );
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return TRUE;
    }

    /**
     * Delete expired reservations
     * I.e. reservations of status new which are older then a given minAge
     *
     * @param boolean $dryRun
     * @param int $age Age in seconds
     * @return int
     * @throws IllegalObjectTypeException
     */
    protected function deleteInvalidReservations($dryRun, $age)
    {
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
     * @param int $age Age in seconds
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getInvalidReservations($age)
    {
        $reservationDemand = $this->createInvalidReservationsDemand($age);

        return $this->reservationRepository->findDemanded($reservationDemand);
    }

    /**
     * Create a demand for invalid reservations.
     * I.e. unfinished reservations of a certain age
     *
     * @param int $age Age in seconds
     * @return ReservationDemand $reservationDemand
     */
    protected function createInvalidReservationsDemand($age)
    {
        /** @var ReservationDemand $reservationDemand */
        $reservationDemand = $this->objectManager->get(ReservationDemand::class);
        $expiredStatus = [Reservation::STATUS_NEW, Reservation::STATUS_DRAFT];
        $reservationDemand->setStatus(implode(',', $expiredStatus));
        $reservationDemand->setMinAge($age);

        return $reservationDemand;
    }

    /**
     * Close Bookings
     * Searches for lessons with expired date and hides them.
     * Matching reservations are set to 'closed' state and hidden too.
     * A list of all participants is being generated and send via email attachment .
     *
     * @param string $email E-Mail
     * @param boolean $dryRun Does not persist changes but sends the generated email with attachment.
     * @throws \TYPO3\CMS\Core\Exception
     * @return void
     */
    public function closeBookingCommand($email, $dryRun = NULL)
    {
        $lessons = $this->hideExpiredLessons($dryRun);
        $reservations = $this->closeReservations($dryRun);

        // only send an email if at least one lesson or one reservation has been closed (and email address is given)
        if (!empty($email) && (count($lessons) || count($reservations))) {
            try {
                // FIXME remove hard coded argument and use template for rendering (render method)
                $this->notificationService->notify(
                    $email,
                    't3events@cps-it.de',
                    'close booking',
                    static::TEMPLATE_EMAIL,
                    NULL,
                    static::FOLDER_CLOSE_BOOKING,
                    [
                        'dryRun' => $dryRun,
                        SettingsInterface::LESSONS => $lessons,
                        SettingsInterface::RESERVATIONS => $reservations
                    ],
                    [
                        [
                            'variables' => [
                                SettingsInterface::LESSONS => $lessons,
                                SettingsInterface::RESERVATIONS => $reservations
                            ],
                            'templateName' => static::TEMPLATE_DOWNLOAD,
                            'folderName' => static::FOLDER_CLOSE_BOOKING,
                            'fileName' => 'anhang.xls',
                            'mimeType' => 'application/vnd.ms-excel'
                        ]
                    ]
                );
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Hide expired lessons
     * Hides all lesson which meet the given constraints. Returns a query result with matching lessons.
     *
     * @param boolean $dryRun
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function hideExpiredLessons($dryRun)
    {
        $demand = $this->createDemandForExpiredLessons();
        $lessons = $this->scheduleRepository->findDemanded($demand);

        if (!$dryRun) {
            foreach ($lessons as $lesson) {
                $lesson->setHidden(1);
                $this->scheduleRepository->update($lesson);
            }
        }

        return $lessons;
    }

    /**
     * Returns a lesson demand object for expired lessons.
     * A lesson will considered expired when its date is older than the given date.
     * Default is 'now'
     *
     * @param string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
     * @return ScheduleDemand $lessonDemand
     */
    protected function createDemandForExpiredLessons($date = 'now')
    {
        /** @var ScheduleDemand $lessonDemand */
        $lessonDemand = $this->objectManager->get(ScheduleDemand::class);
        $lessonDemand->setPeriod(PeriodConstraintRepositoryInterface::PERIOD_PAST);
        $lessonDemand->setDate(new \DateTime($date));

        return $lessonDemand;
    }

    /**
     * Closes expired reservations
     *
     * @param boolean $dryRun
     * @return QueryResult|array
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function closeReservations($dryRun)
    {
        $reservationDemand = $this->createReservationDemandByExpiredLessonDate();
        $reservations = $this->reservationRepository->findDemanded($reservationDemand);

        if (!$dryRun) {
            foreach ($reservations as $reservation) {
                $reservation->setHidden(1);
                $reservation->setStatus(Reservation::STATUS_CLOSED);
                $this->reservationRepository->update($reservation);
            }
        }

        return $reservations;
    }

    /**
     * Returns a reservation demand object for reservations
     * The demand includes all reservations with status 'submitted'
     * where the date of its lessons is beyond
     * a given date and time (default 'now')
     *
     * @param string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
     * @return ReservationDemand $reservationDemand
     */
    protected function createReservationDemandByExpiredLessonDate($date = 'now')
    {
        /** @var ReservationDemand $reservationDemand */
        $reservationDemand = $this->objectManager->get(ReservationDemand::class);
        $reservationDemand->setStatus(Reservation::STATUS_SUBMITTED);
        $reservationDemand->setLessonDate(new \DateTime($date));
        $reservationDemand->setPeriod(PeriodConstraintRepositoryInterface::PERIOD_PAST);

        return $reservationDemand;
    }

    /**
     * Sends an email about expired reservations and lessons.
     * Expired are lessons where the reservation deadline is
     * before yesterday 0:00:00 h.
     * A MS Excel file with the results will be attached to the email
     *
     * @param string $email Recipients email address
     * @throws Exception
     */
    public function reportExpiredCommand($email)
    {
        $lessons = $this->getLessonsWithExpiredDeadline();
        $reservationDemand = $this->createReservationDemandByLessonDeadline('yesterday');
        $reservations = $this->reservationRepository->findDemanded($reservationDemand);

        if (!empty($email) && ($lessons->count() || $reservations->count())) {
            try {
                $this->notificationService->notify(
                    $email,
                    'no-reply@example.com',
                    'Abgelaufene Termine und Reservierungen in ihrem Veranstaltungssystem',
                    static::TEMPLATE_EMAIL,
                    NULL,
                    'ReportExpired', [
                    SettingsInterface::LESSONS => $lessons,
                    SettingsInterface::RESERVATIONS => $reservations
                ], [
                        [
                            'variables' => [
                                SettingsInterface::LESSONS => $lessons,
                                SettingsInterface::RESERVATIONS => $reservations
                            ],
                            'templateName' => static::TEMPLATE_DOWNLOAD,
                            'folderName' => static::FOLDER_CLOSE_BOOKING,
                            'fileName' => 'anhang.xls',
                            'mimeType' => 'application/vnd.ms-excel'
                        ]
                    ]
                );
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Gets all expired lessons
     *
     * @param string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
     * @return QueryResultInterface|array
     */
    protected function getLessonsWithExpiredDeadline($date = NULL)
    {
        /** @var ScheduleDemand $lessonDemand */
        $lessonDemand = $this->createDemandForLessonsWithExpiredDeadline($date);

        return $this->scheduleRepository->findDemanded($lessonDemand);
    }

    /**
     * Returns a lesson demand object for lessons with expired registration deadline.
     * A lesson will considered expired when its registration deadline is older than the given date.
     * Default is 'now'
     *
     * @param string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
     * @return ScheduleDemand $lessonDemand
     */
    protected function createDemandForLessonsWithExpiredDeadline($date = 'now')
    {
        /** @var ScheduleDemand $lessonDemand */
        $lessonDemand = $this->objectManager->get(ScheduleDemand::class);
        $lessonDemand->setDeadlineBefore(new \DateTime($date));

        return $lessonDemand;
    }

    /**
     * Returns a reservation demand object for reservations
     * The demand includes all reservations with status 'submitted'
     * where the registration deadline of its lessons is beyond
     * a given date and time (default 'now')
     *
     * @param string $date A string that the strtotime(), DateTime and date_create() parser understands. Default: 'now'
     * @return ReservationDemand $reservationDemand
     */
    protected function createReservationDemandByLessonDeadline($date = 'now')
    {
        /** @var ReservationDemand $reservationDemand */
        $reservationDemand = $this->objectManager->get(ReservationDemand::class);
        $reservationDemand->setStatus(Reservation::STATUS_SUBMITTED);
        $reservationDemand->setLessonDeadline(new \DateTime($date));

        return $reservationDemand;
    }
}


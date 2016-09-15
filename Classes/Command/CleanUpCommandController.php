<?php
namespace CPSIT\T3eventsReservation\Command;

use CPSIT\T3eventsReservation\Controller\BillingAddressRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ContactRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use Webfox\T3events\Controller\NotificationRepositoryTrait;

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

/**
 * Class CleanUpCommandController
 * Provides cleanup commands for scheduler and command line interface
 *
 * @package CPSIT\T3eventsReservation\Command
 */
class CleanUpCommandController extends CommandController
{
    use ReservationDemandFactoryTrait, ReservationRepositoryTrait,
        PersonRepositoryTrait, ContactRepositoryTrait,
        BillingAddressRepositoryTrait, NotificationRepositoryTrait;

    /**
     * Deletes reservations by date and all their related records.
     *
     * @param boolean $dryRun If true nothing will be deleted.
     * @param string $period A period name. Allowed: pastOnly, futureOnly, specific, all
     * @param string $date A string understood by \DateTime constructor.
     * @param string $storagePageIds Comma separated list of storage page ids. (Required)
     * @param int $limit Maximum number of reservations to remove.
     */
    public function deleteReservationsCommand(
        $dryRun = true,
        $period = 'pastOnly',
        $date = '',
        $storagePageIds = '',
        $limit = 1000
    ) {
        $settings = [
            'period' => $period,
            'storagePages' => $storagePageIds,
            'limit' => $limit
        ];

        if (!empty($date) && $period === 'specific') {
            $settings['periodType'] = 'byDate';
            $settings['periodEndDate'] = $date;
            $settings['periodStartDate'] = '01-01-1970';
        }

        $reservationDemand = $this->reservationDemandFactory->createFromSettings($settings);
        $reservations = $this->reservationRepository->findDemanded($reservationDemand);
        $deletedReservations = count($reservations);

        $this->outputLine('Found ' . $deletedReservations . ' matching reservations.');

        if (count($reservations)) {
            $participantsToRemove = $this->getParticipantsToRemove($reservations);
            $contactsToRemove = $this->getContactsToRemove($reservations);
            $billingAddressesToRemove = $this->getBillingAddressesToRemove($reservations);
            $notificationsToRemove = $this->getNotificationsToRemove($reservations);
            $this->outputLine('Reservations contain:');
            $this->outputLine(' ' . count($participantsToRemove) . ' participants');
            $this->outputLine(' ' . count($contactsToRemove) . ' contacts');
            $this->outputLine(' ' . count($billingAddressesToRemove) . ' billing addresses');
            $this->outputLine(' ' . count($notificationsToRemove) . ' notifications');

            if (!$dryRun) {
                $this->outputLine('Removing:');
                $this->outputLine(' ' . count($participantsToRemove) . ' participants');
                foreach ($participantsToRemove as $participantToRemove) {
                    $this->personRepository->remove($participantToRemove);
                }

                $this->outputLine(' ' . count($contactsToRemove) . ' contacts');
                foreach ($contactsToRemove as $contactToRemove) {
                    $this->contactRepository->remove($contactToRemove);
                }

                $this->outputLine(' ' . count($billingAddressesToRemove) . ' billing addresses');
                foreach ($billingAddressesToRemove as $billingAddress) {
                    $this->billingAddressRepository->remove($billingAddress);
                }

                $this->outputLine(' ' . count($notificationsToRemove) . ' notifications');
                foreach ($notificationsToRemove as $notificationToRemove) {
                    $this->notificationRepository->remove($notificationToRemove);
                }

                $this->outputLine(' ' . count($reservations) . ' reservations');
                foreach ($reservations as $reservation) {
                    $this->reservationRepository->remove($reservation);
                }
            }
        }
    }

    /**
     * Gets all participants from all reservations
     *
     * @param QueryResultInterface|array $reservations
     * @return array
     */
    protected function getParticipantsToRemove($reservations)
    {
        $participantsToRemove = [];
        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            $participants = $reservation->getParticipants();
            foreach ($participants as $participant) {
                $participantsToRemove[] = $participant;
            }
        }

        return $participantsToRemove;
    }

    /**
     * Gets all contacts from all reservations
     *
     * @param QueryResultInterface|array $reservations
     * @return array
     */
    protected function getContactsToRemove($reservations)
    {
        $contactsToRemove = [];
        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            $contact = $reservation->getContact();
            if ($contact !== null) {
                $contactsToRemove[] = $contact;
            }
        }

        return $contactsToRemove;
    }

    /**
     * Gets all billing adresses from all reservations
     *
     * @param QueryResultInterface|array $reservations
     * @return array
     */
    protected function getBillingAddressesToRemove($reservations)
    {
        $billingAddressesToRemove = [];
        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            $billingAddress = $reservation->getBillingAddress();
            if ($billingAddress !== null) {
                $billingAddressesToRemove[] = $billingAddress;
            }
        }

        return $billingAddressesToRemove;
    }

    /**
     * Gets all notifications from all reservations
     *
     * @param QueryResultInterface|array $reservations
     * @return array
     */
    protected function getNotificationsToRemove($reservations)
    {
        $notificationsToRemove = [];
        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            $notifications = $reservation->getNotifications();
            foreach ($notifications as $notification) {
                $notificationsToRemove[] = $notification;
            }
        }

        return $notificationsToRemove;
    }
}

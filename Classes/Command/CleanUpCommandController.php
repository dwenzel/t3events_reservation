<?php

namespace CPSIT\T3eventsReservation\Command;

use CPSIT\T3eventsReservation\Controller\BillingAddressRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ContactRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Utility\SettingsInterface as SI;
use DWenzel\T3events\Controller\NotificationRepositoryTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface as PCI;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
        BillingAddressRepositoryTrait, NotificationRepositoryTrait, PersistenceManagerTrait;

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
        $period = PCI::PERIOD_PAST,
        $date = '',
        $storagePageIds = '',
        $limit = 1000
    )
    {
        $settings = [
            'period' => $period,
            'storagePages' => $storagePageIds,
            'limit' => $limit
        ];

        if (!empty($date) && $period === PCI::PERIOD_SPECIFIC) {
            $settings[PCI::PERIOD_TYPE] = PCI::PERIOD_TYPE_DATE;
            $settings[PCI::PERIOD_END_DATE] = $date;
            $settings[PCI::PERIOD_START_DATE] = '01-01-1970';
        }

        $reservationDemand = $this->reservationDemandFactory->createFromSettings($settings);
        $reservations = $this->reservationRepository->findDemanded($reservationDemand);
        $deletedReservations = count($reservations);

        $this->outputLine('Found ' . $deletedReservations . ' matching reservations.');

        if (!count($reservations)) {
            return;
        }

        $objectsToRemove = [
            SI::PARTICIPANTS => [
                SI::OBJECTS => $this->getParticipantsToRemove($reservations),
                SI::REPOSITORY => $this->personRepository
            ],
            SI::CONTACTS => [
                SI::OBJECTS => $this->getContactsToRemove($reservations),
                SI::REPOSITORY => $this->contactRepository
            ],
            'billing addresses' => [
                SI::OBJECTS => $this->getBillingAddressesToRemove($reservations),
                SI::REPOSITORY => $this->billingAddressRepository
            ],
            SI::NOTIFICATIONS => [
                SI::OBJECTS => $this->getNotificationsToRemove($reservations),
                SI::REPOSITORY => $this->notificationRepository
            ],
            SI::RESERVATIONS => [
                SI::OBJECTS => $reservations,
                SI::REPOSITORY => $this->reservationRepository
            ]
        ];

        $this->outputLine('Reservations contain:');
        foreach ($objectsToRemove as $key => $entry) {
            $this->outputLine(' ' . count($entry[SI::OBJECTS]) . ' ' . $key);
        }

        if ($dryRun) {
            return;
        }

        $this->outputLine('Removing:');
        foreach ($objectsToRemove as $key => $entry) {
            $objects = $entry[SI::OBJECTS];
            $this->outputLine(' ' . count($objects) . ' ' . $key);
            foreach ($objects as $object) {
                $entry[SI::REPOSITORY]->remove($object);
            }
        }
        $this->persistenceManager->persistAll();
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
            if (!count($participants)) {
                continue;
            }
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
            if (!count($notifications)) {
                continue;
            }
            foreach ($notifications as $notification) {
                $notificationsToRemove[] = $notification;
            }
        }

        return $notificationsToRemove;
    }
}

<?php

namespace CPSIT\T3eventsReservation\Service;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Domain\Repository\NotificationRepository;

class ReservationRemover
{
    public function __construct(
        private PersonRepository $personRepository,
        private ContactRepository $contactRepository,
        private BillingAddressRepository $billingAddressRepository,
        private NotificationRepository $notificationRepository,
        private ReservationRepository $reservationRepository,
        private ReservationPersonResolver $personResolver
    ) {
    }

    /**
     * Callers own persistence (no persistAll here).
     */
    public function remove(Reservation $reservation): void
    {
        $participants = $reservation->getParticipants() ? $reservation->getParticipants()->toArray() : [];
        $contact = $reservation->getContact();
        $billingAddress = $reservation->getBillingAddress();

        $persons = [];
        foreach ($participants as $participant) {
            $persons[] = [$participant, $this->personRepository];
        }
        if ($contact !== null) {
            $persons[] = [$contact, $this->contactRepository];
        }
        if ($billingAddress !== null) {
            $persons[] = [$billingAddress, $this->billingAddressRepository];
        }

        $uids = [];
        foreach ($persons as [$person]) {
            if ($person->getUid() !== null) {
                $uids[] = $person->getUid();
            }
        }
        $deletableUids = $this->personResolver->filterDeletable($uids, (int)$reservation->getUid());

        foreach ($persons as [$person, $repository]) {
            $uid = $person->getUid();
            if ($uid === null || in_array($uid, $deletableUids, true)) {
                $repository->remove($person);
            }
        }

        foreach ($reservation->getNotifications() as $notification) {
            $this->notificationRepository->remove($notification);
        }
        $this->reservationRepository->remove($reservation);
    }
}

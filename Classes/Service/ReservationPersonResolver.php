<?php

namespace CPSIT\T3eventsReservation\Service;

class ReservationPersonResolver
{
    public function filterDeletable(array $personUids, int $reservationUid): array
    {
        // Filtering implemented in Task 4 (#83594)
        return $personUids;
    }
}

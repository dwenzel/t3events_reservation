<?php

namespace CPSIT\T3eventsReservation\Hooks;

use CPSIT\T3eventsReservation\Service\ReservationPersonResolver;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Deletes the persons of a reservation when the reservation is deleted in the backend
 */
class ReservationDeleteHook
{
    private const RESERVATION_TABLE = 'tx_t3eventsreservation_domain_model_reservation';
    private const PERSON_TABLE = 'tx_t3events_domain_model_person';

    /** @var ReservationPersonResolver */
    private $personResolver;

    // DataHandler creates hooks via makeInstance without DI
    public function __construct(?ReservationPersonResolver $personResolver = null)
    {
        $this->personResolver = $personResolver ?? new ReservationPersonResolver();
    }

    /**
     * @param string $table
     * @param int|string $id
     * @param array $recordToDelete
     * @param bool $recordWasDeleted
     * @param DataHandler $dataHandler
     */
    public function processCmdmap_deleteAction(
        string $table,
        $id,
        array $recordToDelete,
        &$recordWasDeleted,
        DataHandler $dataHandler
    ): void {
        if ($table !== self::RESERVATION_TABLE) {
            return;
        }
        $id = (int)$id;

        if (!$recordWasDeleted) {
            $dataHandler->deleteEl($table, $id);
            $recordWasDeleted = true;
        }

        // The delete may have been refused (missing permission): keep the persons then
        if ($this->personResolver->isReservationLive($id)) {
            return;
        }

        $personUids = $this->personResolver->collectPersonUids($id, $recordToDelete);
        foreach ($this->personResolver->filterDeletable($personUids, $id) as $personUid) {
            $dataHandler->deleteAction(self::PERSON_TABLE, $personUid);
        }
    }
}

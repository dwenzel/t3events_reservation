<?php

namespace CPSIT\T3eventsReservation\Controller;


/**
 * ReservationController
 */
interface AccessControlInterface
{
    /**
     * Deny access
     * Issues an error message and redirects
     *
     * @return void
     */
    public function denyAccess();

    /**
     * Checks if access is allowed
     *
     * @param object $object Object which should be accessed
     * @return boolean
     */
    public function isAccessAllowed($object);
}

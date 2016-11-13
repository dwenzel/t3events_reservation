<?php

namespace CPSIT\T3eventsReservation\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\PerformanceRepositoryTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class BillingAddressController
 * Creates, updates and deletes billing addresses for reservations.
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class BillingAddressController
    extends ActionController
    implements AccessControlInterface
{
    use BillingAddressRepositoryTrait, DemandTrait,
        EntityNotFoundHandlerTrait, PerformanceRepositoryTrait,
        ReservationAccessTrait, ReservationRepositoryTrait,
        RoutingTrait, SettingsUtilityTrait,
        SearchTrait, TranslateTrait;
    const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * New billing address action
     *
     * @param Reservation $reservation
     * @param BillingAddress|null $billingAddress
     * @ignorevalidation $billingAddress
     */
    public function newAction(Reservation $reservation, BillingAddress $billingAddress = null)
    {
        $this->view->assignMultiple(
            [
                'billingAddress' => $billingAddress,
                'reservation' => $reservation
            ]
        );
    }

    /**
     * Create billing address action
     * Creates a billing addres for a reservation
     * @param Reservation $reservation
     * @param BillingAddress $billingAddress
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $reservation->setBillingAddress($billingAddress);
        $this->billingAddressRepository->add($billingAddress);

        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.billingAddress.create.success')
        );

        $this->dispatch(['reservation' => $reservation]);
    }


    /**
     * Edit a billing address
     *
     * @param Reservation $reservation
     * @param BillingAddress $billingAddress
     */
    public function editAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $this->view->assignMultiple(
            [
                'reservation' => $reservation,
                'billingAddress' => $billingAddress
            ]
        );
    }

    /**
     * Removes a billing address from reservation
     *
     * @param Reservation $reservation
     * @param BillingAddress $billingAddress
     */
    public function removeAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        if ($billingAddress === $reservation->getBillingAddress()) {
            $reservation->removeBillingAddress();
            $this->billingAddressRepository->remove($billingAddress);
            $this->addFlashMessage(
                $this->translate('message.billingAddress.remove.success')
            );
        }

        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * Updates a billing address
     *
     * @param Reservation $reservation
     * @param BillingAddress $billingAddress
     */
    public function updateAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $this->billingAddressRepository->update($billingAddress);
        $this->dispatch(['reservation' => $reservation]);
    }
}

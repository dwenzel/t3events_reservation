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
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\PerformanceRepositoryTrait;
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

    final public const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * @const Extension key
     */
    final public const EXTENSION_KEY = 't3events_reservation';

    /**
     * New billing address action
     *
     * @param BillingAddress|null $billingAddress
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("billingAddress")
     */
    public function newAction(Reservation $reservation, BillingAddress $billingAddress = null)
    {
        $this->view->assignMultiple(
            [
                'billingAddress' => $billingAddress,
                SettingsInterface::RESERVATION => $reservation
            ]
        );
    }

    /**
     * Create billing address action
     * Creates a billing addres for a reservation
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $reservation->setBillingAddress($billingAddress);
        $this->billingAddressRepository->add($billingAddress);

        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.billingAddress.create.success')
        );

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * Edit a billing address
     */
    public function editAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $this->view->assignMultiple(
            [
                SettingsInterface::RESERVATION => $reservation,
                'billingAddress' => $billingAddress
            ]
        );
    }

    /**
     * Removes a billing address from reservation
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
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

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * Updates a billing address
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(Reservation $reservation, BillingAddress $billingAddress)
    {
        $this->billingAddressRepository->update($billingAddress);
        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }
}

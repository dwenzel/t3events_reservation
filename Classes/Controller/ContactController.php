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

use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use DWenzel\T3events\Controller\AbstractController;
use DWenzel\T3events\Controller\RoutingTrait;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;

/**
 * Class ContactController
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class ContactController
    extends AbstractController
    implements AccessControlInterface
{
    use ReservationAccessTrait, RoutingTrait;

    /**
     * @const parent controller
     */
    const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * Injects the contact repository
     *
     * @param ContactRepository $contactRepository
     */
    public function injectContactRepository(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * New contact
     *
     * @param Contact|null $contact
     * @param Reservation $reservation
     * @ignorevalidation $contact
     */
    public function newAction(Contact $contact = null, Reservation $reservation)
    {
        $originalRequest = $this->request->getOriginalRequest();
        if (
            $originalRequest instanceof Request
            && $originalRequest->hasArgument('contact')
        ) {
            $contact = $originalRequest->getArgument('contact');
        }

        $templateVariables = [
            'contact' => $contact,
            'reservation' => $reservation
        ];
        $this->view->assignMultiple($templateVariables);
    }

    /**
     * Create a contact
     *
     * @param Contact $contact
     */
    public function createAction(Contact $contact)
    {
        $this->contactRepository->add($contact);

        if ($reservation = $contact->getReservation())
        {
            $reservation->setContact($contact);
        }
        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * Edit contact
     *
     * @param Contact $contact
     * @param Reservation $reservation
     * @throws InvalidSourceException
     * @ignorevalidation $contact
     * @ignorevalidation $reservation
     */
    public function editAction(Contact $contact, Reservation $reservation)
    {
        if ($reservation->getContact() !== $contact) {
            throw new InvalidSourceException(
                'Can not edit contact uid ' . $contact->getUid()
                . '. Contact not found in Reservation uid: ' . $reservation->getUid() . '.',
                1460039887
            );
        }

        $this->view->assign('contact', $contact);
    }

    /**
     * Updates a contact
     *
     * @param Contact $contact
     * @validate $contact \CPSIT\T3eventsReservation\Domain\Validator\ContactValidator
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function updateAction(Contact $contact)
    {
        $this->contactRepository->update($contact);
        $this->dispatch(['reservation' => $contact->getReservation()]);
    }
}

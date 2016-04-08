<?php
namespace CPSIT\T3eventsReservation\Controller;

use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Validator\ContactValidator;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use Webfox\T3events\Controller\AbstractController;

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
 * Class ContactController
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class ContactController
    extends AbstractController
    implements AccessControlInterface
{
    use ReservationAccessTrait;

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
     * Edit contact
     *
     * @param Contact $contact
     * @param Reservation $reservation
     * @throws InvalidSourceException
     */
    public function editAction(Contact $contact, Reservation $reservation)
    {
        if ($reservation->getContact() !== $contact)
        {
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
        $this->forward(
            'edit',
            self::PARENT_CONTROLLER_NAME,
            null,
            ['reservation' => $contact->getReservation()]
        );
    }
}

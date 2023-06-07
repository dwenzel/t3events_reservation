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
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\SignalInterface;
use DWenzel\T3events\Controller\TranslateTrait;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;

/**
 * Class ContactController
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class ContactController
    extends ActionController
    implements AccessControlInterface, SignalInterface
{
    use ContactRepositoryTrait, DemandTrait,
        EntityNotFoundHandlerTrait,
        RoutingTrait, SearchTrait, SettingsUtilityTrait,
        TranslateTrait;

    use ReservationAccessTrait {
        isAccessAllowed as traitIsAccessAllowed;
    }

    /**
     * @const parent controller
     */
    final public const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * @const Extension key
     */
    final public const EXTENSION_KEY = 't3events_reservation';

    public function isAccessAllowed()
    {
        if ($this->request->hasArgument('contact')) {
            $contact = $this->request->getArgument('contact');

            if (!empty($contact['reservation'])) {
                $this->request->setArgument('reservation', $contact['reservation']);
            }
        }

        return $this->traitIsAccessAllowed();
    }

    /**
     * New contact
     *
     * @param Contact|null $contact
     * @param Reservation $reservation
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("contact")
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function newAction(Reservation $reservation, Contact $contact = null)
    {
        $originalRequest = $this->request->getOriginalRequest();
        if (
            $originalRequest instanceof Request
            && $originalRequest->hasArgument(SettingsInterface::CONTACT)
        ) {
            $contact = $originalRequest->getArgument(SettingsInterface::CONTACT);
        }

        $templateVariables = [
            SettingsInterface::CONTACT => $contact,
            SettingsInterface::RESERVATION => $reservation
        ];
        $this->view->assignMultiple($templateVariables);
    }

    /**
     * Create a contact
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAction(Contact $contact)
    {
        $this->contactRepository->add($contact);

        if ($reservation = $contact->getReservation()) {
            $reservation->setContact($contact);
        }
        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * Edit contact
     *
     * @throws InvalidSourceException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("contact")
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("reservation")
     */
    public function editAction(Contact $contact, Reservation $reservation)
    {
        if (!$contact->equals($reservation->getContact())) {
            throw new InvalidSourceException(
                'Can not edit contact uid ' . $contact->getUid()
                . '. Contact not found in Reservation uid: ' . $reservation->getUid() . '.',
                1_460_039_887
            );
        }

        $this->view->assign(SettingsInterface::CONTACT, $contact);
    }

    /**
     * Updates a contact
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="contact", validator="CPSIT\T3eventsReservation\Domain\Validator\ContactValidator")
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(Contact $contact)
    {
        $this->contactRepository->update($contact);
        $this->dispatch([SettingsInterface::RESERVATION => $contact->getReservation()]);
    }

    /**
     * Clear cache of current page on error. Needed because we want a re-evaluation of the data.
     */
    public function clearCacheOnError(): void
    {
        $extbaseSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (isset($extbaseSettings['persistence']['enableAutomaticCacheClearing']) && $extbaseSettings['persistence']['enableAutomaticCacheClearing'] === '1') {
            if (isset($GLOBALS['TSFE'])) {
                $this->cacheService->clearPageCache([$GLOBALS['TSFE']->id]);
            }
        }
    }
}

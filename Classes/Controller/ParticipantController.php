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

use CPSIT\T3eventsReservation\Domain\Model\BookableInterface;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\PerformanceRepositoryTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;

/**
 * Class ParticipantController
 * Creates, updates and deletes participants for reservations.
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class ParticipantController
    extends ActionController
    implements AccessControlInterface
{
    use DemandTrait, EntityNotFoundHandlerTrait,
        PerformanceRepositoryTrait, PersonRepositoryTrait,
        PersistenceManagerTrait, ReservationAccessTrait,
        ReservationRepositoryTrait, RoutingTrait,
        SettingsUtilityTrait, TranslateTrait, SearchTrait;

    const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * @const Extension key
     */
    const EXTENSION_KEY = 't3events_reservation';

    /**
     * New participant action
     *
     * @param Reservation $reservation
     * @param Person|null $participant
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("participant")
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function newAction(Reservation $reservation, Person $participant = null)
    {
        $originalRequest = $this->request->getOriginalRequest();
        if (
            $originalRequest instanceof Request
            && $originalRequest->hasArgument(SettingsInterface::PARTICIPANT)
        ) {
            $participant = $originalRequest->getArgument(SettingsInterface::PARTICIPANT);
        }

        $templateVariables = [
            SettingsInterface::PARTICIPANT => $participant,
            SettingsInterface::RESERVATION => $reservation
        ];
        $this->view->assignMultiple($templateVariables);
    }

    /**
     * Create participant action
     *
     * @param Reservation $reservation
     * @param Person $participant
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function createAction(Reservation $reservation, Person $participant)
    {
        $lesson = $reservation->getLesson();
        $messageKey = 'message.participant.create.failure.notBookable';
        if ($lesson instanceof BookableInterface) {
            $messageKey = 'message.participant.create.failure.noFreePlaces';
            if ($lesson->getFreePlaces()) {
                $participant->setReservation($reservation);
                $participant->setType(Person::PERSON_TYPE_PARTICIPANT);
                $reservation->addParticipant($participant);
                $this->reservationRepository->update($reservation);
                $this->performanceRepository->update($lesson);
                $this->persistenceManager->persistAll();
                $messageKey = 'message.participant.create.success';
            }
        }
        $this->addFlashMessage($this->translate($messageKey));

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * Edit participant
     *
     * @param Person $participant
     * @param Reservation $reservation
     * @throws InvalidSourceException
     */
    public function editAction(Person $participant, Reservation $reservation)
    {
        if (!$reservation->equals($participant->getReservation())) {
            throw new InvalidSourceException(
                'Can not edit participant uid ' . $participant->getUid()
                . '. Participant not found in Reservation uid: ' . $reservation->getUid() . '.',
                1459343264
            );
        }

        $this->view->assign(SettingsInterface::PARTICIPANT, $participant);
    }

    /**
     * Updates a participant
     *
     * @param Person $participant
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="participant", validator="CPSIT\T3eventsReservation\Domain\Validator\ParticipantValidator")
     */
    public function updateAction(Person $participant)
    {
        $this->personRepository->update($participant);
        $this->dispatch([SettingsInterface::RESERVATION => $participant->getReservation()]);
    }


    /**
     * Removes a participant from the reservation its
     * lesson and deletes it.
     *
     * @param Reservation $reservation
     * @param Person $participant
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function removeAction(Reservation $reservation, Person $participant)
    {
        $reservation->removeParticipant($participant);
        $this->personRepository->remove($participant);
        $this->reservationRepository->update($reservation);
        $this->addFlashMessage($this->translate('message.participant.remove.success'));
        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }
}

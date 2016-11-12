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
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\PerformanceRepositoryTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

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
     * New participant action
     *
     * @param Reservation $reservation
     * @param Person|null $participant
     * @ignorevalidation $participant
     */
    public function newAction(Reservation $reservation, Person $participant = null)
    {
        $originalRequest = $this->request->getOriginalRequest();
        if (
            $originalRequest instanceof Request
            && $originalRequest->hasArgument('participant')
        ) {
            $participant = $originalRequest->getArgument('participant');
        }

        $templateVariables = [
            'participant' => $participant,
            'reservation' => $reservation
        ];
        $this->view->assignMultiple($templateVariables);
    }

    /**
     * Create participant action
     *
     * @param Reservation $reservation
     * @param Person $participant
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
                $lesson->addParticipant($participant);
                $this->reservationRepository->update($reservation);
                $this->performanceRepository->update($lesson);
                $this->persistenceManager->persistAll();
                $messageKey = 'message.participant.create.success';
            }
        }
        $this->addFlashMessage($this->translate($messageKey));

        $this->dispatch(['reservation' => $reservation]);
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
        if (!$reservation->getParticipants()->contains($participant)) {
            throw new InvalidSourceException(
                'Can not edit participant uid ' . $participant->getUid()
                . '. Participant not found in Reservation uid: ' . $participant->getReservation()->getUid() . '.',
                1459343264
            );
        }

        $this->view->assign('participant', $participant);
    }

    /**
     * Updates a participant
     *
     * @param Person $participant
     * @validate $participant \CPSIT\T3eventsReservation\Domain\Validator\ParticipantValidator
     */
    public function updateAction(Person $participant)
    {
        $this->personRepository->update($participant);
        $this->dispatch(['reservation' => $participant->getReservation()]);
    }

}

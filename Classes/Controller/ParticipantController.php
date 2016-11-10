<?php
namespace CPSIT\T3eventsReservation\Controller;
/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use DWenzel\T3events\Controller\RoutingTrait;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use DWenzel\T3events\Controller\AbstractController;


/**
 * Class ParticipantController
 * This should be used as child controller of ReservationController only
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
class ParticipantController
    extends AbstractController
    implements AccessControlInterface
{
    use ReservationAccessTrait, RoutingTrait;
    const PARENT_CONTROLLER_NAME = 'Reservation';

    /**
     * @var PersonRepository
     */
    protected $participantRepository;

    /**
     * Injects the participant repository
     *
     * @param PersonRepository $participantRepository
     */
    public function injectParticipantRepository(PersonRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
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
        if (!$reservation->getParticipants()->contains($participant))
        {
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
        $this->participantRepository->update($participant);
        $this->dispatch(['reservation' => $participant->getReservation()]);
    }

}

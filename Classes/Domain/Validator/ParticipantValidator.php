<?php
namespace CPSIT\T3eventsReservation\Domain\Validator;

use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

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
 * Class ParticipantValidator
 *
 * @package CPSIT\T3eventsReservation\Domain\Validator
 */
class ParticipantValidator extends AbstractValidator
{
    /**
     * Tells if a given object is a valid participant
     *
     * @param mixed $participant
     * @return bool
     */
    protected function isValid($participant)
    {
        if (!$participant instanceof Person) {
            $this->addError('Participant must be a Person.', 1_465_382_176);

            return false;
        }

        if ($participant->getType() !== Person::PERSON_TYPE_PARTICIPANT) {
            $this->addError(
                'Wrong person type: ' . $participant->getType() . '. '
                . ' Participant must be of type '
                . Person::class . '::PERSON_TYPE_PARTICIPANT.',
                1_465_382_335
            );

            return false;
        }

        if (!$participant->getReservation() instanceof Reservation) {
            $this->addError('Missing reservation.', 1_465_389_725);

            return false;
        }

        return true;
    }
}

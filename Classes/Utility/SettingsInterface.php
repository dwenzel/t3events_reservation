<?php

namespace CPSIT\T3eventsReservation\Utility;

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

interface SettingsInterface extends \DWenzel\T3events\Utility\SettingsInterface
{
    const CONTACT = 'contact';
    const CONTACTS = 'contacts';
    const CONFIRM = 'confirm';
    const LESSON = 'lesson';
    const LESSONS = 'lessons';
    const NEW_BILLING_ADDRESS = 'newBillingAddress';
    const NEW_RESERVATION = 'newReservation';
    const NEW_PARTICIPANT = 'newParticipant';
    const NOTIFICATION = 'notification';
    const PARTICIPANT = 'participant';
    const PARTICIPANTS = 'participants';
    const RESERVATION = 'reservation';
    const RESERVATIONS = 'reservations';
    const LESSON_DEADLINE = 'lessonDeadline';
}

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
    public const CONTACT = 'contact';
    public const CONTACTS = 'contacts';
    public const CONFIRM = 'confirm';
    public const LESSON = 'lesson';
    public const LESSONS = 'lessons';
    public const NEW_BILLING_ADDRESS = 'newBillingAddress';
    public const NEW_RESERVATION = 'newReservation';
    public const NEW_PARTICIPANT = 'newParticipant';
    public const NOTIFICATION = 'notification';
    public const PARTICIPANT = 'participant';
    public const PARTICIPANTS = 'participants';
    public const RESERVATION = 'reservation';
    public const RESERVATIONS = 'reservations';
    public const LESSON_DEADLINE = 'lessonDeadline';
}

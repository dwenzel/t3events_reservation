<?php
namespace CPSIT\T3eventsReservation\Domain\Model;

/***************************************************************
 *  Copyright notice
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *           Boerge Franck <franck@cps-it.de>, CPS IT
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
use CPSIT\T3eventsReservation\PriceableInterface;
use DWenzel\T3events\Domain\Model\Company;
use DWenzel\T3events\Domain\Model\EqualsTrait;
use DWenzel\T3events\Domain\Model\Performance;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Reservation
 */
class Reservation extends AbstractEntity
{
    use EqualsTrait;

    const ERROR_ACCESS_UNKNOWN = 'unknownAccessError';

    const ERROR_INCOMPLETE_RESERVATION_IN_SESSION = 'incompleteReservationInSession';

    const ERROR_MISMATCH_SESSION_KEY_REQUEST_ARGUMENT = 'mismatchOfReservationKeyInSessionAndRequestArgument';

    const ERROR_MISSING_RESERVATION_KEY_IN_SESSION = 'missingReservationKeyInSession';

    const ERROR_MISSING_SESSION_KEY_AND_REQUEST_ARGUMENT = 'requestArgumentAndSessionKeyMissing';

    const STATUS_CANCELED_BY_SUPPLIER = 6;

    const STATUS_CANCELED_NO_CHARGE = 3;

    const STATUS_CANCELED_WITH_COSTS = 4;

    const STATUS_CLOSED = 5;

    const STATUS_DRAFT = 1;

    const STATUS_NEW = 0;

    const STATUS_SUBMITTED = 2;

    /**
     * billing address
     *
     * @var \CPSIT\T3eventsReservation\Domain\Model\BillingAddress
     */
    protected $billingAddress = null;

    /**
     * company
     *
     * @var \DWenzel\T3events\Domain\Model\Company
     */
    protected $company = null;

    /**
     * Responsible contact person for reservation.
     *
     * @var \CPSIT\T3eventsReservation\Domain\Model\Contact
     * @TYPO3\CMS\Extbase\Annotation\Validate("\CPSIT\T3eventsReservation\Domain\Validator\ContactValidator")
     */
    protected $contact = null;

    /**
     * Contact is participant
     *
     * @var boolean
     */
    protected $contactIsParticipant;

    /**
     * Disclaimer of revocation statement
     *
     * @var boolean
     */
    protected $disclaimRevocation = false;

    /**
     * Hidden
     *
     * @var int
     */
    protected $hidden;

    /**
     * lesson
     *
     * @var \DWenzel\T3events\Domain\Model\Performance|Schedule
     */
    protected $lesson = null;

    /**
     * note
     *
     * @var string
     */
    protected $note;

    /**
     * Notifications
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Notification>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $notifications;

    /**
     * participants
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $participants = null;

    /**
     * Privacy statement
     *
     * @var boolean
     * @TYPO3\CMS\Extbase\Annotation\Validate("Boolean", options={"is": true})
     */
    protected $privacyStatementAccepted = false;

    /**
     * status
     *
     * @var integer
     */
    protected $status = 0;

    /**
     * total price
     *
     * @var float
     */
    protected $totalPrice = 0.0;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->participants = new ObjectStorage();
        $this->notifications = new ObjectStorage();
    }

    /**
     * Adds a Notification
     *
     * @param Notification $notification
     * @return void
     */
    public function addNotification(Notification $notification)
    {
        $this->notifications->attach($notification);
    }

    /**
     * Adds a Person
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participant
     * @return void
     */
    public function addParticipant(Person $participant)
    {
        $this->participants->attach($participant);
        $this->lesson->addParticipant($participant);
        $this->updateTotalPrice();
    }

    /**
     * updates the total price
     */
    protected function updateTotalPrice()
    {
        if ($this->lesson instanceof PriceableInterface) {
            $totalPrice = $this->lesson->getPrice() * $this->participants->count();
            $this->setTotalPrice($totalPrice);
        }

    }

    /**
     * Returns the lesson
     *
     * @return Performance $lesson
     */
    public function getLesson()
    {
        return $this->lesson;
    }

    /**
     * Sets the lesson
     *
     * @param \DWenzel\T3events\Domain\Model\Performance $lesson
     * @return void
     */
    public function setLesson(Performance $lesson)
    {
        $this->lesson = $lesson;
    }

    /**
     * @return \CPSIT\T3eventsReservation\Domain\Model\BillingAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param \CPSIT\T3eventsReservation\Domain\Model\BillingAddress $billingAddress
     */
    public function setBillingAddress(BillingAddress $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * Returns the company
     *
     * @return Company $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Sets the company
     *
     * @param \DWenzel\T3events\Domain\Model\Company $company
     * @return void
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Returns the contact
     *
     * @return Contact $contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Sets the contact
     *
     * @param Contact $contact
     * @return void
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Get contact is participant
     *
     * @return boolean
     */
    public function getContactIsParticipant()
    {
        return $this->contactIsParticipant;
    }

    /**
     * Set contact is participant
     *
     * @var boolean $contactIsParticipant
     * @return void
     */
    public function setContactIsParticipant($contactIsParticipant)
    {
        $this->contactIsParticipant = $contactIsParticipant;
    }

    /**
     * Get the disclaim of revocation
     *
     * @return boolean
     */
    public function getDisclaimRevocation()
    {
        return $this->disclaimRevocation;
    }

    /**
     * Set the disclaim of revocation
     *
     * @param boolean $disclaimRevocation
     */
    public function setDisclaimRevocation($disclaimRevocation)
    {
        $this->disclaimRevocation = $disclaimRevocation;
    }

    /**
     * Returns hidden
     *
     * @return int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets hidden
     *
     * @param int $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * Returns the notifications
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Notification> $notifications
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Sets the notifications
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Notification> $notifications
     * @return void
     */
    public function setNotifications(ObjectStorage $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Returns the participants
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Sets the participants
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
     * @return void
     */
    public function setParticipants(ObjectStorage $participants)
    {
        /** @var Person $oldParticipant */
        foreach ($this->participants as $oldParticipant) {
            $this->lesson->removeParticipant($oldParticipant);
        }
        /** @var Person $newParticipant */
        foreach ($participants as $newParticipant) {
            $this->lesson->addParticipant($newParticipant);
        }
        $this->participants = $participants;
        $this->updateTotalPrice();
    }

    /**
     * Get the privacy statement accepted
     *
     * @return boolean
     */
    public function getPrivacyStatementAccepted()
    {
        return $this->privacyStatementAccepted;
    }

    /**
     * Sets the privacy statement accepted
     *
     * @param boolean $accepted
     * @return void
     */
    public function setPrivacyStatementAccepted($accepted)
    {
        $this->privacyStatementAccepted = $accepted;
    }

    /**
     * Returns the status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param integer $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param float $totalPrice
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * Removes the billing address.
     * I.e. sets it to null
     */
    public function removeBillingAddress()
    {
        $this->billingAddress = null;
    }

    /**
     * Removes a Notification
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Notification $notificationToRemove The Notification to be removed
     * @return void
     */
    public function removeNotification(Notification $notificationToRemove)
    {
        $this->notifications->detach($notificationToRemove);
    }

    /**
     * Removes a Person
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove The Person to be removed
     * @return void
     */
    public function removeParticipant(Person $participantToRemove)
    {
        $this->participants->detach($participantToRemove);
        $this->lesson->removeParticipant($participantToRemove);
        $this->updateTotalPrice();
    }
}

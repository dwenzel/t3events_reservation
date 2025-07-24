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

    final public const ERROR_ACCESS_UNKNOWN = 'unknownAccessError';

    final public const ERROR_INCOMPLETE_RESERVATION_IN_SESSION = 'incompleteReservationInSession';

    final public const ERROR_MISMATCH_SESSION_KEY_REQUEST_ARGUMENT = 'mismatchOfReservationKeyInSessionAndRequestArgument';

    final public const ERROR_MISSING_RESERVATION_KEY_IN_SESSION = 'missingReservationKeyInSession';

    final public const ERROR_MISSING_SESSION_KEY_AND_REQUEST_ARGUMENT = 'requestArgumentAndSessionKeyMissing';

    final public const STATUS_CANCELED_BY_SUPPLIER = 6;

    final public const STATUS_CANCELED_NO_CHARGE = 3;

    final public const STATUS_CANCELED_WITH_COSTS = 4;

    final public const STATUS_CLOSED = 5;

    final public const STATUS_DRAFT = 1;

    final public const STATUS_NEW = 0;

    final public const STATUS_SUBMITTED = 2;

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
     * @var \DWenzel\T3events\Domain\Model\Performance|\CPSIT\T3eventsReservation\Domain\Model\BookableInterface
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
    protected function initStorageObjects(): void
    {
        $this->participants = new ObjectStorage();
        $this->notifications = new ObjectStorage();
    }

    /**
     * Adds a Notification
     *
     * @return void
     */
    public function addNotification(Notification $notification): void
    {
        $this->notifications->attach($notification);
    }

    /**
     * Adds a Person
     *
     * @return void
     */
    public function addParticipant(Person $participant): void
    {
        $this->participants->attach($participant);
        $this->lesson->addParticipant($participant);
        $this->updateTotalPrice();
    }

    /**
     * updates the total price
     */
    protected function updateTotalPrice(): void
    {
        if ($this->lesson instanceof PriceableInterface) {
            $totalPrice = $this->lesson->getPrice() * $this->participants->count();
            $this->setTotalPrice($totalPrice);
        }

    }

    /**
     * Returns the lesson
     *
     * @return Performance|BookableInterface|null $lesson
     */
    public function getLesson(): Performance|BookableInterface|null
    {
        return $this->lesson;
    }

    /**
     * Sets the lesson
     *
     * @return void
     */
    public function setLesson(Performance $lesson): void
    {
        $this->lesson = $lesson;
    }

    /**
     * @return ?BillingAddress
     */
    public function getBillingAddress(): ?BillingAddress
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * Returns the company
     *
     * @return ?Company $company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * Sets the company
     *
     * @return void
     */
    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    /**
     * Returns the contact
     *
     * @return ?Contact $contact
     */
    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    /**
     * Sets the contact
     *
     * @param Contact $contact
     * @return void
     */
    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * Get contact is participant
     *
     * @return boolean
     */
    public function getContactIsParticipant(): bool
    {
        return $this->contactIsParticipant;
    }

    /**
     * Set contact is participant flag
     *
     * @return void
     *@var boolean $contactIsParticipant
     */
    public function setContactIsParticipant(bool $contactIsParticipant): void
    {
        $this->contactIsParticipant = $contactIsParticipant;
    }

    /**
     * Get the disclaim of revocation
     *
     * @return boolean
     */
    public function getDisclaimRevocation(): bool
    {
        return $this->disclaimRevocation;
    }

    /**
     * Set the disclaim of revocation
     *
     * @param boolean $disclaimRevocation
     */
    public function setDisclaimRevocation(bool $disclaimRevocation): void
    {
        $this->disclaimRevocation = $disclaimRevocation;
    }

    /**
     * Returns hidden
     *
     * @return int
     */
    public function getHidden(): int
    {
        return $this->hidden;
    }

    /**
     * Sets hidden
     *
     * @param int $hidden
     */
    public function setHidden($hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note): void
    {
        $this->note = $note;
    }

    /**
     * Returns the notifications
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Notification> $notifications
     */
    public function getNotifications(): ObjectStorage
    {
        return $this->notifications;
    }

    /**
     * Sets the notifications
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Notification> $notifications
     * @return void
     */
    public function setNotifications(ObjectStorage $notifications): void
    {
        $this->notifications = $notifications;
    }

    /**
     * Returns the participants
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
     */
    public function getParticipants(): ?ObjectStorage
    {
        return $this->participants;
    }

    /**
     * Sets the participants
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
     * @return void
     */
    public function setParticipants(ObjectStorage $participants): void
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
    public function getPrivacyStatementAccepted(): bool
    {
        return $this->privacyStatementAccepted;
    }

    /**
     * Sets the privacy statement accepted
     *
     * @param boolean $accepted
     * @return void
     */
    public function setPrivacyStatementAccepted($accepted): void
    {
        $this->privacyStatementAccepted = $accepted;
    }

    /**
     * Returns the status
     *
     * @return integer $status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param integer $status
     * @return void
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * @param float $totalPrice
     */
    public function setTotalPrice($totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * Removes the billing address.
     * I.e. sets it to null
     */
    public function removeBillingAddress(): void
    {
        $this->billingAddress = null;
    }

    /**
     * Removes a Notification
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Notification $notificationToRemove The Notification to be removed
     * @return void
     */
    public function removeNotification(Notification $notificationToRemove): void
    {
        $this->notifications->detach($notificationToRemove);
    }

    /**
     * Removes a Person
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove The Person to be removed
     * @return void
     */
    public function removeParticipant(Person $participantToRemove): void
    {
        $this->participants->detach($participantToRemove);
        $this->lesson->removeParticipant($participantToRemove);
        $this->updateTotalPrice();
    }
}

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Webfox\T3events\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;

/**
 * Reservation
 */
class Reservation extends AbstractEntity {

	const STATUS_NEW = 0;
	const STATUS_DRAFT = 1;
	const STATUS_SUBMITTED = 2;
	const STATUS_CANCELED_NO_CHARGE = 3;
	const STATUS_CANCELED_WITH_COSTS = 4;
	const STATUS_CLOSED = 5;
	const STATUS_CANCELED_BY_SUPPLIER = 6;

	/**
	 * Hidden
	 *
	 * @var \int
	 */
	protected $hidden;


	/**
	 * status
	 *
	 * @var integer
	 */
	protected $status = 0;

	/**
	 * company
	 *
	 * @var \Webfox\T3events\Domain\Model\Company
	 */
	protected $company = NULL;

	/**
	 * billing address
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Model\Person
	 */
	protected $billingAddress = NULL;

	/**
	 * Responsible contact person for reservation.
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Model\Person
	 * @validate \CPSIT\T3eventsReservation\Domain\Validator\ContactValidator
	 */
	protected $contact = NULL;

	/**
	 * participants
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person>
	 */
	protected $participants = NULL;

	/**
	 * lesson
	 *
	 * @var BookableInterface|\Webfox\T3events\Domain\Model\Performance
	 */
	protected $lesson = NULL;

	/**
	 * Privacy statement
	 *
	 * @var \boolean
	 * @validate Boolean(is=true)
	 */
	protected $privacyStatementAccepted = FALSE;

	/**
	 * Disclaimer of revocation statement
	 *
	 * @var \boolean
	 */
	protected $disclaimRevocation = FALSE;

	/**
	 * Contact is participant
	 *
	 * @var \boolean
	 */
	protected $contactIsParticipant;

	/**
	 * Notifications
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Webfox\T3events\Domain\Model\Notification>
	 */
	protected $notifications;

	/**
	 * total price
	 *
	 * @var \float
	 */
	protected $totalPrice = 0.0;

	/**
	 * note
	 *
	 * @var \string
	 */
	protected $note;

	/**
	 * Returns hidden
	 *
	 * @return \int
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * Sets hidden
	 *
	 * @param \int $hidden
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	/**
	 * Returns the status
	 *
	 * @return integer $status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Sets the status
	 *
	 * @param integer $status
	 * @return void
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Returns the company
	 *
	 * @return \Webfox\T3events\Domain\Model\Company $company
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * Sets the company
	 *
	 * @param \Webfox\T3events\Domain\Model\Company $company
	 * @return void
	 */
	public function setCompany(\Webfox\T3events\Domain\Model\Company $company) {
		$this->company = $company;
	}

	/**
	 * Returns the contact
	 *
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Person $contact
	 */
	public function getContact() {
		return $this->contact;
	}

	/**
	 * Sets the contact
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $contact
	 * @return void
	 */
	public function setContact(\CPSIT\T3eventsReservation\Domain\Model\Person $contact) {
		$this->contact = $contact;
	}

	/**
	 * __construct
	 */
	public function __construct() {
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
	protected function initStorageObjects() {
		$this->participants = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->notifications = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Adds a Person
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participant
	 * @return void
	 */
	public function addParticipant(Person $participant) {
		$this->participants->attach($participant);
		$this->updateTotalPrice();
	}

	/**
	 * Removes a Person
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove The Person to be removed
	 * @return void
	 */
	public function removeParticipant(\CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove) {
		$this->participants->detach($participantToRemove);
		$this->updateTotalPrice();
	}

	/**
	 * Returns the participants
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
	 */
	public function getParticipants() {
		return $this->participants;
	}

	/**
	 * Sets the participants
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
	 * @return void
	 */
	public function setParticipants(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $participants) {
		$this->participants = $participants;
		$this->updateTotalPrice();
	}

	/**
	 * Returns the lesson
	 *
	 * @return BookableInterface|\Webfox\T3events\Domain\Model\Performance $lesson
	 */
	public function getLesson() {
		return $this->lesson;
	}

	/**
	 * Sets the lesson
	 *
	 * @param BookableInterface|\Webfox\T3events\Domain\Model\Performance $lesson
	 * @return void
	 */
	public function setLesson(\Webfox\T3events\Domain\Model\Performance $lesson) {
		$this->lesson = $lesson;
	}

	/**
	 * Get the privacy statement accepted
	 *
	 * @return \boolean
	 */
	public function getPrivacyStatementAccepted() {
		return $this->privacyStatementAccepted;
	}

	/**
	 * Sets the privacy statement accepted
	 *
	 * @param \boolean $accepted
	 * @return void
	 */
	public function setPrivacyStatementAccepted($accepted) {
		$this->privacyStatementAccepted = $accepted;
	}

	/**
	 * Get the disclaim of revocation
	 *
	 * @return boolean
	 */
	public function getDisclaimRevocation() {
		return $this->disclaimRevocation;
	}

	/**
	 * Set the disclaim of revocation
	 *
	 * @param boolean $disclaimRevocation
	 */
	public function setDisclaimRevocation($disclaimRevocation) {
		$this->disclaimRevocation = $disclaimRevocation;
	}

	/**
	 * Get contact is participant
	 *
	 * @return \boolean
	 */
	public function getContactIsParticipant() {
		return $this->contactIsParticipant;
	}

	/**
	 * Set contact is participant
	 *
	 * @var \boolean $contactIsParticipant
	 * @return void
	 */
	public function setContactIsParticipant($contactIsParticipant) {
		$this->contactIsParticipant = $contactIsParticipant;
	}

	/**
	 * Adds a Notification
	 *
	 * @param \Webfox\T3events\Domain\Model\Notification $notification
	 * @return void
	 */
	public function addNotification(Notification $notification) {
		$this->notifications->attach($notification);
	}

	/**
	 * Removes a Notification
	 *
	 * @param \Webfox\T3events\Domain\Model\Notification $notificationToRemove The Notification to be removed
	 * @return void
	 */
	public function removeNotification(\Webfox\T3events\Domain\Model\Notification $notificationToRemove) {
		$this->notifications->detach($notificationToRemove);
	}

	/**
	 * Returns the notifications
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Webfox\T3events\Domain\Model\Notification> $notifications
	 */
	public function getNotifications() {
		return $this->notifications;
	}

	/**
	 * Sets the notifications
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Webfox\T3events\Domain\Model\Notification> $notifications
	 * @return void
	 */
	public function setNotifications(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $notifications) {
		$this->notifications = $notifications;
	}

	/**
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Person
	 */
	public function getBillingAddress() {
		return $this->billingAddress;
	}

	/**
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $billingAddress
	 */
	public function setBillingAddress($billingAddress) {
		$this->billingAddress = $billingAddress;
	}

	/**
	 * @return float
	 */
	public function getTotalPrice() {
		return $this->totalPrice;
	}

	/**
	 * @param float $totalPrice
	 */
	public function setTotalPrice($totalPrice) {
		$this->totalPrice = $totalPrice;
	}

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @param string $note
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	/**
	 * updates the total price
	 */
	protected function updateTotalPrice() {
		if ($lesson = $this->getLesson()) {
			if ($lesson instanceof PriceableInterface) {
				$totalPrice = $lesson->getPrice() * $this->participants->count();
				$this->setTotalPrice($totalPrice);
			}
		}
	}
}

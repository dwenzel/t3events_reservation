<?php
namespace CPSIT\T3eventsReservation\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class BookableScheduleTrait
 *
 * @package CPSIT\T3eventsReservation\Domain\Model
 */
trait BookableScheduleTrait {
	/**
	 * Participants of this course.
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person>
	 */
	protected $participants = NULL;

	/**
	 * Registration deadline
	 *
	 * @var \DateTime
	 */
	protected $deadline = NULL;

	/**
	 * Price per participant
	 *
	 * @var float
	 */
	protected $price = 0.0;

	/**
	 * Available places (How many participants are allowed to register)
	 *
	 * @var integer
	 */
	protected $places = 0;

	/**
	 * @var \DateTime
	 */
	protected $registrationBegin;

	/**
	 * @var bool
	 */
	protected $freeOfCharge;

	/**
	 * @var string
	 */
	protected $registrationRemarks;

	/**
	 * @var bool
	 */
	protected $documentBasedRegistration;

	/**
	 * @var bool
	 */
	protected $externalRegistration;

	/**
	 * @var string
	 */
	protected $externalRegistrationLink;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
	 * @lazy
	 */
	protected $registrationDocuments;

	/**
	 * Initializes object
	 */
	public function initializeObject() {
		$this->participants = new ObjectStorage();
		$this->registrationDocuments = new ObjectStorage();
	}

	/**
	 * Returns the deadline
	 *
	 * @return \DateTime $deadline
	 */
	public function getDeadline() {
		return $this->deadline;
	}

	/**
	 * Sets the deadline
	 *
	 * @param \DateTime $deadline
	 * @return void
	 */
	public function setDeadline(\DateTime $deadline) {
		$this->deadline = $deadline;
	}

	/**
	 * Adds a Participant
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participant
	 * @return void
	 */
	public function addParticipant(Person $participant) {
		$this->participants->attach($participant);
	}

	/**
	 * Returns the price
	 *
	 * @return float $price
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * Sets the price
	 *
	 * @param float $price
	 * @return void
	 */
	public function setPrice($price) {
		$this->price = $price;
	}

	/**
	 * Returns the places
	 *
	 * @return integer $places
	 */
	public function getPlaces() {
		return $this->places;
	}

	/**
	 * Sets the places
	 *
	 * @param integer $places
	 * @return void
	 */
	public function setPlaces($places) {
		$this->places = $places;
	}

	/**
	 * Removes a Participant
	 *
	 *@param \CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove The Participant to be removed
	 * @return void
	 */
	public function removeParticipant(Person $participantToRemove) {
		$this->participants->detach($participantToRemove);
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
	public function setParticipants($participants) {
		$this->participants = $participants;
	}

	/**
	 * Returns the number of free places
	 *
	 * @return int
	 */
	public function getFreePlaces() {
		return $this->getPlaces() - $this->getParticipants()->count();
	}

	/**
	 * Gets the registration documents
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
	 */
	public function getRegistrationDocuments() {
		return $this->registrationDocuments;
	}

	/**
	 * Sets the registration documents
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $registrationDocuments
	 */
	public function setRegistrationDocuments($registrationDocuments) {
		$this->registrationDocuments = $registrationDocuments;
	}

	/**
	 * Adds an registration document
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference
	 */
	public function addRegistrationDocument(FileReference $fileReference) {
		$this->registrationDocuments->attach($fileReference);
	}

	/**
	 * Removes an registration document
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference
	 */
	public function removeRegistrationDocument(FileReference $fileReference) {
		$this->registrationDocuments->detach($fileReference);
	}

	/**
	 * @return \DateTime
	 */
	public function getRegistrationBegin() {
		return $this->registrationBegin;
	}

	/**
	 * @param \DateTime $registrationBegin
	 */
	public function setRegistrationBegin($registrationBegin) {
		$this->registrationBegin = $registrationBegin;
	}

	/**
	 * @return boolean
	 */
	public function isFreeOfCharge() {
		return $this->freeOfCharge;
	}

	/**
	 * @param boolean $freeOfCharge
	 */
	public function setFreeOfCharge($freeOfCharge) {
		$this->freeOfCharge = $freeOfCharge;
	}

	/**
	 * @return string
	 */
	public function getRegistrationRemarks() {
		return $this->registrationRemarks;
	}

	/**
	 * @param string $registrationRemarks
	 */
	public function setRegistrationRemarks($registrationRemarks) {
		$this->registrationRemarks = $registrationRemarks;
	}

	/**
	 * @return boolean
	 */
	public function isDocumentBasedRegistration() {
		return $this->documentBasedRegistration;
	}

	/**
	 * @param boolean $documentBasedRegistration
	 */
	public function setDocumentBasedRegistration($documentBasedRegistration) {
		$this->documentBasedRegistration = $documentBasedRegistration;
	}

	/**
	 * @return boolean
	 */
	public function isExternalRegistration() {
		return $this->externalRegistration;
	}

	/**
	 * @param boolean $externalRegistration
	 */
	public function setExternalRegistration($externalRegistration) {
		$this->externalRegistration = $externalRegistration;
	}

	/**
	 * @return string
	 */
	public function getExternalRegistrationLink() {
		return $this->externalRegistrationLink;
	}

	/**
	 * @param string $externalRegistrationLink
	 */
	public function setExternalRegistrationLink($externalRegistrationLink) {
		$this->externalRegistrationLink = $externalRegistrationLink;
	}

}
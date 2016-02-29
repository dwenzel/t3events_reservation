<?php

namespace CPSIT\T3eventsReservation\Domain\Model;


/***************************************************************
 *  Copyright notice
 *  (c) 2015 Dirk Wenzel <dirk.wenzel@cps-it.de>
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
interface BookableInterface {
	/**
	 * Returns the deadline
	 *
	 * @return \DateTime $deadline
	 */
	public function getDeadline();

	/**
	 * Adds a Participant
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participant
	 * @return void
	 */
	public function addParticipant(Person $participant);

	/**
	 * Returns the places
	 *
	 * @return integer $places
	 */
	public function getPlaces();

	/**
	 * Removes a Participant
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Person $participantToRemove The Participant to be removed
	 * @return void
	 */
	public function removeParticipant(Person $participantToRemove);

	/**
	 * Returns the participants
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
	 */
	public function getParticipants();

	/**
	 * Sets the participants
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CPSIT\T3eventsReservation\Domain\Model\Person> $participants
	 * @return void
	 */
	public function setParticipants($participants);

	/**
	 * Returns the number of free places
	 *
	 * @return int
	 */
	public function getFreePlaces();

	/**
	 * @return boolean
	 */
	public function isFreeOfCharge();

	/**
	 * @param boolean $freeOfCharge
	 */
	public function setFreeOfCharge($freeOfCharge);

	/**
	 * Returns the noHandlingFee
	 *
	 * @return boolean $noHandlingFee
	 */
	public function getNoHandlingFee();

	/**
	 * Returns the boolean state of noHandlingFee
	 *
	 * @return boolean
	 */
	public function isNoHandlingFee();
}

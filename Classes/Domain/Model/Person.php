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

/**
 * Person
 */
class Person extends \Webfox\T3events\Domain\Model\Person {
	const PERSON_TYPE_UNKNOWN = 0;
	const PERSON_TYPE_CONTACT = 1;
	const PERSON_TYPE_PARTICIPANT = 2;

	/**
	 * type
	 *
	 * @var integer
	 */
	protected $type = 0;


	/**
	 * reservation
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Model\Reservation
	 */
	protected $reservation;

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * Returns the type
	 *
	 * @return integer $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type
	 *
	 * @param integer $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Returns the reservation
	 *
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 */
	public function getReservation() {
		return $this->reservation;
	}

	/**
	 * Sets the reservation
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 * @return void
	 */
	public function setReservation($reservation) {
		$this->reservation = $reservation;
	}

}
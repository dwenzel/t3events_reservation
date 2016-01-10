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
trait ReservationPersonTrait {
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
	 * Returns the type
	 *
	 * @return integer $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @var string
	 */
	protected $birthplace;

	/**
	 * @var string
	 */
	protected $companyName;

	/**
	 * @var string
	 */
	protected $role;

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

	/**
	 * @return string
	 */
	public function getBirthplace() {
		return $this->birthplace;
	}

	/**
	 * @param string $birthplace
	 */
	public function setBirthplace($birthplace) {
		$this->birthplace = $birthplace;
	}

	/**
	 * @return string
	 */
	public function getCompanyName() {
		return $this->companyName;
	}

	/**
	 * @param string $companyName
	 */
	public function setCompanyName($companyName) {
		$this->companyName = $companyName;
	}

	/**
	 * @return string
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * @param string $role
	 */
	public function setRole($role) {
		$this->role = $role;
	}
}
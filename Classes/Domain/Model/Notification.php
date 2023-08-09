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
 * Notification for reservations
 */
class Notification extends \DWenzel\T3events\Domain\Model\Notification {
	/**
	 * Reservation
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Model\Reservation
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $reservation;

	/**
	 * Returns the reservation
	 *
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Reservation
	 */
	public function getReservation() {
		return $this->reservation;
	}

	/**
	 * Sets the reservation
	 *
	 * @param \CPSIT\T3eventsReservation\Domain\Model\Reservation $reservation
	 */
	public function setReservation($reservation) {
		$this->reservation = $reservation;
	}
}

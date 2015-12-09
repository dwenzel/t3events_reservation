<?php
namespace CPSIT\T3eventsReservation\Domain\Model\Dto;

use Webfox\T3events\Domain\Model\Dto\AbstractDemand;
use Webfox\T3events\Domain\Model\Dto\DemandInterface;

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
 * Class ReservationDemand
 *
 * @package CPSIT\T3eventsReservation\Domain\Model\Dto
 */
class ReservationDemand extends AbstractDemand
	implements DemandInterface {

	/**
	 * Status
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * lesson deadline
	 *
	 * @var \DateTime Deadline of lesson (in reservation)
	 */
	protected $lessonDeadline;

	/**
	 * Date of lesson
	 *
	 * @var \DateTime
	 */
	protected $lessonDate;

	/**
	 * Minimum Age (in seconds)
	 *
	 * @var \int
	 */
	protected $minAge;

	/**
	 * get the status
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * sets the status
	 *
	 * @param string $status
	 * @return void
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Get the lesson deadline
	 *
	 * @return \DateTime
	 */
	public function getLessonDeadline() {
		return $this->lessonDeadline;
	}

	/**
	 * Sets the lesson deadline
	 *
	 * @var \DateTime $deadline
	 */
	public function setLessonDeadline($deadline) {
		$this->lessonDeadline = $deadline;
	}

	/**
	 * Gets the minimal age in seconds
	 *
	 * @return int
	 */
	public function getMinAge() {
		return $this->minAge;
	}

	/**
	 * Sets the minimal age in seconds
	 *
	 * @param int $minAge
	 */
	public function setMinAge($minAge) {
		$this->minAge = $minAge;
	}

	/**
	 * Gets the lesson date
	 *
	 * @return \DateTime
	 */
	public function getLessonDate() {
		return $this->lessonDate;
	}

	/**
	 * Sets the lesson date
	 *
	 * @param $date
	 */
	public function setLessonDate($date) {
		$this->lessonDate = $date;
	}
}
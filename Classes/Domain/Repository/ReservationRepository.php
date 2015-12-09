<?php
namespace CPSIT\T3eventsReservation\Domain\Repository;

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
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Webfox\T3events\Domain\Repository\AbstractDemandedRepository;

/**
 * The repository for Reservations
 */
class ReservationRepository extends AbstractDemandedRepository {
	public function createConstraintsFromDemand(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query, \Webfox\T3events\Domain\Model\Dto\DemandInterface $demand) {
		/** @var  \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $demand */
		$constraints = array();
		if ($demand->getLessonDeadline()) {
			$constraints[] = $query->logicalAnd(
				$query->lessThan('lesson.deadline', $demand->getLessonDeadline())
			);
		}
		if ($demand->getStatus()) {
			$statusArr = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $demand->getStatus());
			$statusConstraints = array();
			foreach ($statusArr as $status) {
				$statusConstraints[] = $query->equals('status', $status);
			}
			$constraints[] = $query->logicalOr(
				$statusConstraints
			);
		}
		if ($demand->getMinAge()) {
			$constraints[] = $query->logicalAnd(
				$query->lessThan('tstamp', time() - $demand->getMinAge())
			);
		}
		if ($demand->getLessonDate()) {
			if ($demand->getPeriod() === 'futureOnly') {
				$constraints[] = $query->greaterThanOrEqual('lesson.date', $demand->getLessonDate());
			} elseif ($demand->getPeriod() === 'pastOnly') {
				$constraints[] = $query->lessThanOrEqual('lesson.date', $demand->getLessonDate());
			}
		}

		return $constraints;
	}
}
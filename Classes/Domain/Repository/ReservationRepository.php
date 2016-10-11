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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use DWenzel\T3events\Domain\Repository\AbstractDemandedRepository;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryTrait;

/**
 * The repository for Reservations
 */
class ReservationRepository
	extends AbstractDemandedRepository
	implements GenreConstraintRepositoryInterface, AudienceConstraintRepositoryInterface,
	EventTypeConstraintRepositoryInterface, PeriodConstraintRepositoryInterface {
	use GenreConstraintRepositoryTrait, EventTypeConstraintRepositoryTrait,
		PeriodConstraintRepositoryTrait, AudienceConstraintRepositoryTrait;

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @param \DWenzel\T3events\Domain\Model\Dto\DemandInterface $demand
	 * @return array
	 */
	public function createConstraintsFromDemand(QueryInterface $query, DemandInterface $demand) {
		/** @var ReservationDemand $demand */
		$constraints = [];
		if ($demand->getLessonDeadline()) {
			$constraints[] = $query->logicalAnd(
				$query->lessThan('lesson.deadline', $demand->getLessonDeadline())
			);
		}
		if ($demand->getStatus()) {
			$statusArr = GeneralUtility::intExplode(',', $demand->getStatus());
			$statusConstraints = [];
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
		if ((bool) $genreConstraints = $this->createGenreConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $genreConstraints, $demand->getCategoryConjunction());
		}
		if ((bool) $searchConstraints = $this->createSearchConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $searchConstraints, 'OR');
		}
		if ((bool) $eventTypeConstraints = $this->createEventTypeConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $eventTypeConstraints, $demand->getCategoryConjunction());
		}
		if ((bool) $periodConstraints = $this->createPeriodConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $periodConstraints);
		}
		if ((bool) $audienceConstraints = $this->createAudienceConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $audienceConstraints);
		}

		return $constraints;
	}
}

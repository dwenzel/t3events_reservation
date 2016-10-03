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
use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Repository\AbstractDemandedRepository;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\CategoryConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\CategoryConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryTrait;

/**
 * The repository for Persons
 */
class PersonRepository
	extends AbstractDemandedRepository
	implements GenreConstraintRepositoryInterface, CategoryConstraintRepositoryInterface,
	EventTypeConstraintRepositoryInterface, PeriodConstraintRepositoryInterface,
	AudienceConstraintRepositoryInterface {
	use GenreConstraintRepositoryTrait, EventTypeConstraintRepositoryTrait,
		CategoryConstraintRepositoryTrait, PeriodConstraintRepositoryTrait,
		AudienceConstraintRepositoryTrait;
	/**
	 * Returns an array of constraints created from a given demand object.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @param \DWenzel\T3events\Domain\Model\Dto\DemandInterface $demand
	 * @return array<\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Constraint>
	 */
	public function createConstraintsFromDemand(QueryInterface $query, DemandInterface $demand) {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand $demand */
		$constraints = [];
		if ($demand->getTypes()) {
			$personTypes = explode(',', $demand->getTypes());
			$personConstraints = [];
			foreach ($personTypes as $personType) {
				$personConstraints[] = $query->equals('type', $personType);
			}
			if (count($personConstraints)) {
				$constraints[] = $query->logicalAnd($personConstraints);
			}
		}
		if ($demand->getLessonDeadline()) {
			$constraints[] = $query->logicalAnd(
				$query->lessThan('lesson.deadline', $demand->getLessonDeadline())
			);
		}
		if ($demand->getLessonDate()) {
			if ($demand->getLessonPeriod() === 'futureOnly') {
				$constraints[] = $query->greaterThanOrEqual('reservation.lesson.date', $demand->getLessonDate());
			} elseif ($demand->getLessonPeriod() === 'pastOnly') {
				$constraints[] = $query->lessThanOrEqual('reservation.lesson.date', $demand->getLessonDate());
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
		if ((bool) $categoryConstraints = $this->createCategoryConstraints($query, $demand)) {
			$this->combineConstraints($query, $constraints, $categoryConstraints, $demand->getCategoryConjunction());
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

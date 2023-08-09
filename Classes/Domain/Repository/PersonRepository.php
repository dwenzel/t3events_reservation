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
use DWenzel\T3events\Domain\Repository\DemandedRepositoryInterface;
use DWenzel\T3events\Domain\Repository\DemandedRepositoryTrait;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
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
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Persons
 */
class PersonRepository
	extends Repository
	implements DemandedRepositoryInterface, GenreConstraintRepositoryInterface, CategoryConstraintRepositoryInterface,
	EventTypeConstraintRepositoryInterface, PeriodConstraintRepositoryInterface,
	AudienceConstraintRepositoryInterface {
	use DemandedRepositoryTrait, GenreConstraintRepositoryTrait, EventTypeConstraintRepositoryTrait,
		CategoryConstraintRepositoryTrait, PeriodConstraintRepositoryTrait,
		AudienceConstraintRepositoryTrait;
	/**
  * Returns an array of constraints created from a given demand object.
  *
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
        $deadline = $demand->getLessonDeadline();
		if (!empty($deadline)) {
			$constraints[] = $query->logicalAnd(
				$query->lessThan(
				    'reservation.lesson.deadline',
                    $deadline->getTimestamp()
                )
			);
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

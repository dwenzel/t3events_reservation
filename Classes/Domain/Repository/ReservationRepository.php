<?php
namespace CPSIT\T3eventsReservation\Domain\Repository;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Repository\AbstractDemandedRepository;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\AudienceConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\DemandedRepositoryInterface;
use DWenzel\T3events\Domain\Repository\DemandedRepositoryTrait;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\EventTypeConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\GenreConstraintRepositoryTrait;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Reservations
 */
class ReservationRepository
    extends Repository
    implements DemandedRepositoryInterface , GenreConstraintRepositoryInterface, AudienceConstraintRepositoryInterface,
    EventTypeConstraintRepositoryInterface, PeriodConstraintRepositoryInterface
{
    use DemandedRepositoryTrait, GenreConstraintRepositoryTrait, EventTypeConstraintRepositoryTrait,
        PeriodConstraintRepositoryTrait, AudienceConstraintRepositoryTrait;

    /**
     * @return array
     */
    public function createConstraintsFromDemand(QueryInterface $query, DemandInterface $demand)
    {
        /** @var ReservationDemand $demand */
        $constraints = [];
        if ($demand->getLessonDeadline()) {
            $constraints[] = $query->logicalAnd(
                $query->lessThan(
                    'lesson.deadline',
                    $demand->getLessonDeadline()->getTimestamp()
                )
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
        if ((bool)$genreConstraints = $this->createGenreConstraints($query, $demand)) {
            $this->combineConstraints($query, $constraints, $genreConstraints, $demand->getCategoryConjunction());
        }
        if ((bool)$searchConstraints = $this->createSearchConstraints($query, $demand)) {
            $this->combineConstraints($query, $constraints, $searchConstraints, 'OR');
        }
        if ((bool)$eventTypeConstraints = $this->createEventTypeConstraints($query, $demand)) {
            $this->combineConstraints($query, $constraints, $eventTypeConstraints, $demand->getCategoryConjunction());
        }
        if ((bool)$periodConstraints = $this->createPeriodConstraints($query, $demand)) {
            $this->combineConstraints($query, $constraints, $periodConstraints);
        }
        if ((bool)$audienceConstraints = $this->createAudienceConstraints($query, $demand)) {
            $this->combineConstraints($query, $constraints, $audienceConstraints);
        }

        return $constraints;
    }
}

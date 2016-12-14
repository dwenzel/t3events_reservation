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

use CPSIT\T3eventsReservation\Domain\Model\Dto\DeadlineAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Repository\PerformanceRepository;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class ScheduleRepository
 *
 * @package CPSIT\T3eventsReservation\Domain\Repository
 */
class ScheduleRepository extends PerformanceRepository
{
    /**
     * Returns an array of constraints created from a given demand object.
     *
     * @param QueryInterface $query
     * @param DemandInterface $demand
     * @return array<\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Constraint>
     */
    public function createConstraintsFromDemand(
        QueryInterface $query,
        DemandInterface $demand
    ) {
        $constraints = parent::createConstraintsFromDemand($query, $demand);

        if ($demand instanceof DeadlineAwareDemandInterface) {
            $deadlinePeriod = $demand->getDeadlinePeriod();
            if (!empty($deadlinePeriod)) {
                $timeZone = new \DateTimeZone(date_default_timezone_get());
                $dateTime = new \DateTime('now', $timeZone);
                $timeStamp = $dateTime->getTimestamp();
                if ($deadlinePeriod === PeriodConstraintRepositoryInterface::PERIOD_FUTURE) {
                    $constraints[] = $query->logicalOr(
                        $query->greaterThanOrEqual('deadline', $timeStamp)
                    );
                }
                if ($deadlinePeriod === PeriodConstraintRepositoryInterface::PERIOD_PAST) {
                    $constraints[] = $query->logicalOr(
                        $query->lessThan('deadline', $timeStamp)
                    );
                }
            }
        }

        return $constraints;
    }


}

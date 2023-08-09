<?php
namespace CPSIT\T3eventsReservation\Domain\Factory\Dto;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Dto\ParticipantDemand;
use DWenzel\T3events\Domain\Factory\Dto\PeriodAwareDemandFactoryTrait;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;

/**
 * Class PersonDemandFactory
 * Creates PersonDemand objects
 *
 * @package CPSIT\T3eventsReservation\Domain\Factory\Dto
 */
class ParticipantDemandFactory extends PersonDemandFactory
{
    use PeriodAwareDemandFactoryTrait;

    /**
     * Class name of the object created by this factory.
     */
    final public const DEMAND_CLASS = ParticipantDemand::class;

    /**
     * Composite properties which can not set directly
     * but have to be composed from various settings or
     * require any special logic before setting
     * Do not remove 'types'!
     *
     * @var array
     */
    static protected $compositeProperties = [
        'search',
        'types',
        'periodStart',
        'periodType',
        'periodDuration',
        'startDate',
        'endDate'
    ];

}

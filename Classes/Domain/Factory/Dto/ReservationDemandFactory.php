<?php
namespace CPSIT\T3eventsReservation\Domain\Factory\Dto;

use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use DWenzel\T3events\Domain\Factory\Dto\AbstractDemandFactory;
use DWenzel\T3events\Domain\Factory\Dto\DemandFactoryInterface;
use DWenzel\T3events\Domain\Factory\Dto\PeriodAwareDemandFactoryTrait;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Model\Dto\PeriodAwareDemandInterface;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
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
class ReservationDemandFactory
extends AbstractDemandFactory
implements DemandFactoryInterface
{
use PeriodAwareDemandFactoryTrait;

    /**
     * Class name of the object created by this factory
     */
    final public const DEMAND_CLASS = ReservationDemand::class;

    /**
     * Composite properties which can not set directly
     * but have to be composed from various settings or
     * require any special logic before setting
     *
     * @var array
     */
    static protected $compositeProperties = [
        'search'
    ];

    /**
     * Creates a demand object from settings
     *
     * @return DemandInterface
     */
    public function createFromSettings(array $settings)
    {
        /** @var ReservationDemand $demand */
        $demand = $this->objectManager->get(static::DEMAND_CLASS);

        if ($demand instanceof PeriodAwareDemandInterface) {
            $this->setPeriodConstraints($demand, $settings);
        }

        $this->applySettings($demand, $settings);
        return $demand;
    }
}

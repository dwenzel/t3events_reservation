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

use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use DWenzel\T3events\Domain\Factory\Dto\AbstractDemandFactory;
use DWenzel\T3events\Domain\Factory\Dto\DemandFactoryInterface;
use DWenzel\T3events\Domain\Factory\Dto\PeriodAwareDemandFactoryTrait;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Model\Dto\PeriodAwareDemandInterface;

/**
 * Class PersonDemandFactory
 * Creates PersonDemand objects
 *
 * @package CPSIT\T3eventsReservation\Domain\Factory\Dto
 */
class PersonDemandFactory
    extends AbstractDemandFactory
    implements DemandFactoryInterface
{
    use PeriodAwareDemandFactoryTrait;

    /**
     * Class name of the object created by this factory.
     */
    const DEMAND_CLASS = PersonDemand::class;

    /**
     * Properties which should be mapped when settings
     * are applied to demand object
     *
     * @var array
     */
    static protected $mappedProperties = [
        'maxItems' => 'limit',
        'category' => 'categories'
    ];

    /**
     * Composite properties which can not set directly
     * but have to be composed from various settings or
     * require any special logic before setting
     *
     * @var array
     */
    static protected $compositeProperties = [
        'search', 'types', 'lessonDeadline'
    ];

    /**
     * Creates a demand object from settings
     *
     * @param array $settings
     * @return DemandInterface
     */
    public function createFromSettings(array $settings)
    {
        /** @var PersonDemand $demand */
        $demand = $this->objectManager->get(static::DEMAND_CLASS);

        if ($demand instanceof PeriodAwareDemandInterface) {
            $this->setPeriodConstraints($demand, $settings);
        }

        $this->applySettings($demand, $settings);

        if (!empty($settings['lessonDeadline'])){
            $timeZone = new \DateTimeZone(date_default_timezone_get());
            $demand->setLessonDeadline(
                new \DateTime($settings['lessonDeadline'], $timeZone)
            );
        }

        if ($demand->getLessonPeriod() === 'futureOnly'
            OR $demand->getLessonPeriod() === 'pastOnly'
        ) {
            $timeZone = new \DateTimeZone(date_default_timezone_get());
            $demand->setLessonDate(new \DateTime('midnight', $timeZone));
        }

        return $demand;
    }
}

<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Repository;

use CPSIT\T3eventsReservation\Domain\Model\Dto\ScheduleDemand;
use CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository;
use DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class ScheduleRepositoryTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Repository
 */
class ScheduleRepositoryTest extends UnitTestCase
{
    /**
     * @var ScheduleRepository |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = $this->getMockBuilder(ScheduleRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['dummy'])->getMock();
    }

    /**
     * data provider for deadline tests
     */
    public function deadlineDataProvider()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $dateTime = new \DateTime('now', $timeZone);
        $timeStamp = $dateTime->getTimestamp();

        return [
            [PeriodConstraintRepositoryInterface::PERIOD_FUTURE, 'greaterThanOrEqual', $timeStamp],
            [PeriodConstraintRepositoryInterface::PERIOD_PAST, 'lessThan', $timeStamp],
        ];

    }

    /**
     * @dataProvider deadlineDataProvider
     * @test
     */
    public function createConstraintsFromDemandAddsDeadlineConstraints($deadlinePeriod, $expectedMethod, $expectedValue)
    {
        $query = $this->getMockForAbstractClass(QueryInterface::class);
        $demand = $this->getMockBuilder(ScheduleDemand::class)
            ->setMethods(['getDeadlinePeriod'])->getMock();
        $demand->expects($this->once())
            ->method('getDeadlinePeriod')
            ->will($this->returnValue($deadlinePeriod));
        $query->expects($this->once())
            ->method($expectedMethod)
            ->with('deadline', $this->equalTo($expectedValue, 20));
        $this->subject->createConstraintsFromDemand($query, $demand);
    }
}

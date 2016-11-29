<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Command;

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

use CPSIT\T3eventsReservation\Command\TaskCommandController;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ScheduleDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ScheduleDemand;
use CPSIT\T3eventsReservation\Domain\Model\Task;
use CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository;
use DWenzel\T3events\Domain\Repository\TaskRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class TaskCommandControllerTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Command
 */
class TaskCommandControllerTest extends UnitTestCase
{
    /**
     * @var TaskCommandController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var ScheduleDemandFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduleDemandFactory;

    /**
     * @var ScheduleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduleRepository;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            TaskCommandController::class, ['dummy', 'getSettingsForDemand']
        );
        $this->mockScheduleDemandFactory();
        $this->mockScheduleRepository();
    }

    /**
     * @return ScheduleDemandFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockScheduleDemandFactory()
    {
        $mockDemandFactory = $this->getMock(
            ScheduleDemandFactory::class, ['createFromSettings'], [], '', false
        );
        $this->subject->injectScheduleDemandFactory($mockDemandFactory);
        $this->scheduleDemandFactory = $mockDemandFactory;

        return $mockDemandFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockScheduleRepository()
    {
        $mockScheduleRepository = $this->getMock(
            ScheduleRepository::class, ['findDemanded'], [], '', false
        );
        $this->subject->injectScheduleRepository($mockScheduleRepository);
        $this->scheduleRepository = $mockScheduleRepository;

        return $mockScheduleRepository;
    }

    /**
     * @test
     */
    public function getPerformancesForTaskGetsScheduleDemandFromFactory()
    {
        $settings = [];
        $mockTask = $this->getMock(Task::class);
        $mockDemand = $this->getMock(
            ScheduleDemand::class
        );
        $this->subject->expects($this->once())
            ->method('getSettingsForDemand')
            ->with($mockTask)
            ->will($this->returnValue($settings));
        $this->scheduleDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($mockDemand));
        $this->subject->getPerformancesForTask($mockTask);
    }


    /**
     * @test
     */
    public function getPerformancesForTaskRequiresDemandWithDeadlinePeriod()
    {
        $deadlinePeriod = 'foo';
        $settings = [
            'deadlinePeriod' => $deadlinePeriod
        ];
        $mockTask = $this->getMock(Task::class, ['getDeadlinePeriod']);
        $mockDemand = $this->getMock(
            ScheduleDemand::class
        );

        $mockTask->expects($this->atLeastOnce())
            ->method('getDeadlinePeriod')
            ->will($this->returnValue($deadlinePeriod));

        $this->scheduleDemandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($mockDemand));

        $this->subject->getPerformancesForTask($mockTask);
    }

}

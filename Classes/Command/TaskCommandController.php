<?php
namespace CPSIT\T3eventsReservation\Command;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Controller\ScheduleDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\ScheduleRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\TaskRepositoryTrait;
use DWenzel\T3events\Command\TaskCommandController as BaseController;
use CPSIT\T3eventsReservation\Domain\Model\Task;

/**
 * Class TaskCommandController
 *
 * @package CPSIT\T3eventsReservation\Command
 */
class TaskCommandController extends BaseController
{
    use ScheduleDemandFactoryTrait, ScheduleRepositoryTrait,
        TaskRepositoryTrait;

    /**
     * Get the performances matching a tasks constraints
     *
     * @param Task $task
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getPerformancesForTask($task)
    {
        $settings = $this->getSettingsForDemand($task);
        $deadlinePeriod = $task->getDeadlinePeriod();
        if (!empty($deadlinePeriod)) {
            $settings['deadlinePeriod'] = $deadlinePeriod;
        }
        $demand = $this->scheduleDemandFactory->createFromSettings($settings);
        return $this->scheduleRepository->findDemanded($demand);
    }

}

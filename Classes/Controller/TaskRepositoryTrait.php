<?php
namespace CPSIT\T3eventsReservation\Controller;

use CPSIT\T3eventsReservation\Domain\Repository\TaskRepository;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */
trait TaskRepositoryTrait
{
    /**
     * @var \CPSIT\T3eventsReservation\Domain\Repository\TaskRepository
     */
    protected $taskRepository;

    /**
     * Injects the task repository
     *
     * @param TaskRepository $taskRepository
     */
    public function injectTaskRepository(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }
}

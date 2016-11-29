<?php
namespace CPSIT\T3eventsReservation\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */
use CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository;

/**
 * Class ScheduleRepositoryTrait
 * Provides a ScheduleRepository
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
trait ScheduleRepositoryTrait
{
    /**
     * Reservation repository
     *
     * @var \CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository
     */
    protected $scheduleRepository;

    /**
     * Injects the reservation repository
     *
     * @param \CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository $scheduleRepository
     */
    public function injectScheduleRepository(ScheduleRepository $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

}

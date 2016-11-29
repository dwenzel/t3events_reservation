<?php
namespace CPSIT\T3eventsReservation\Domain\Model;

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
 * Class Task
 *
 * @package CPSIT\T3eventsReservation\Domain\Model
 */
class Task extends \DWenzel\T3events\Domain\Model\Task
{
    /**
     * Deadline period
     *
     * @var string
     */
    protected $deadlinePeriod;

    /**
     * Get the deadline period
     *
     * @return string
     */
    public function getDeadlinePeriod()
    {
        return $this->deadlinePeriod;
    }

    /**
     * Sets the deadline period
     *
     * @param string $deadlinePeriod
     */
    public function setDeadlinePeriod($deadlinePeriod)
    {
        $this->deadlinePeriod = $deadlinePeriod;
    }

}

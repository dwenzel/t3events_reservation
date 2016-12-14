<?php
namespace CPSIT\T3eventsReservation\Domain\Model\Dto;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */
interface DeadlineAwareDemandInterface
{
    /**
     * Gets the deadline period
     *
     * @return string A string
     */
    public function getDeadlinePeriod();

    /**
     * Sets the deadline period
     * @param string $period
     */
    public function setDeadlinePeriod($period);
}

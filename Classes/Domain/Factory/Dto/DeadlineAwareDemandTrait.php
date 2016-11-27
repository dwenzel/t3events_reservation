<?php
namespace CPSIT\T3eventsReservation\Domain\Factory\Dto;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */
trait DeadlineAwareDemandTrait
{
    /**
     * @var \DateTime
     */
    protected $deadlineBefore;

    /**
     * @var \DateTime
     */
    protected $deadlineAfter;

    /**
     * Gets the deadline before date.
     * Tells the repository to fetch records
     * where the deadline is before the given date
     * @return \DateTime
     */
    public function getDeadlineBefore()
    {
        return $this->deadlineBefore;
    }

    /**
     * Sets the deadline before date
     *
     * @var \DateTime $deadline
     */
    public function setDeadlineBefore($deadline)
    {
        $this->deadlineBefore = $deadline;
    }

    /**
     * Gets the deadline after date.
     * Tells the repository to fetch records
     * where the deadline is later then the given date
     * @return \DateTime
     */
    public function getDeadlineAfter()
    {
        return $this->deadlineAfter;
    }

    /**
     * @var \DateTime $deadline
     */
    public function setDeadlineAfter($deadline)
    {
        $this->deadlineAfter = $deadline;
    }
}

<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Dto\DeadlineAwareDemandTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class DeadlineAwareDemandTraitTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto
 */
class DeadlineAwareDemandTraitTest
extends UnitTestCase
{
    /**
     * @var DeadlineAwareDemandTrait
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            \CPSIT\T3eventsReservation\Domain\Model\Dto\DeadlineAwareDemandTrait::class
        );
    }

    /**
     * @test
     */
    public function getDeadlinePeriodInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getDeadlinePeriod()
        );
    }

    /**
     * @test
     */
    public function deadlinePeriodCanBeSet()
    {
        $period = 'foo';
        $this->subject->setDeadlinePeriod($period);

        $this->assertSame(
            $period,
            $this->subject->getDeadlinePeriod()
        );
    }
}

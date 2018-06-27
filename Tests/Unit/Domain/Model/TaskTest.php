<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Task;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class TaskTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model
 */
class TaskTest extends UnitTestCase
{
    /**
     * @var Task|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     *  set up subject
     */
    public function setUp()
    {
        $this->subject = new Task();
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

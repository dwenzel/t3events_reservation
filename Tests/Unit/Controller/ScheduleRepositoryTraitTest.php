<?php

namespace CPSIT\T3eventsReservation\Tests\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Controller\ScheduleRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Repository\ScheduleRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class ScheduleRepositoryTraitTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Controller
 */
class ScheduleRepositoryTraitTest extends UnitTestCase
{
    /**
     * @var ScheduleRepositoryTrait
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ScheduleRepositoryTrait::class
        );
    }

    /**
     * @test
     */
    public function ScheduleRepositoryCanBeInjected()
    {
        $scheduleRepository = $this->getMockBuilder(ScheduleRepository::class)
            ->disableOriginalConstructor()->getMock();

        $this->subject->injectScheduleRepository($scheduleRepository);

        $this->assertAttributeSame(
            $scheduleRepository,
            'scheduleRepository',
            $this->subject
        );
    }
}

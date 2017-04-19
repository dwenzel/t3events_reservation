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

use CPSIT\T3eventsReservation\Domain\Model\Dto\DeadlineAwareDemandInterface;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ScheduleDemand;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class ScheduleDemandTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto
 */
class ScheduleDemandTest extends UnitTestCase
{
    /**
     * @var ScheduleDemand
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ScheduleDemand::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function classImplementsDemandInterface()
    {
        $this->assertInstanceOf(
            DemandInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function classImplementsDeadlineAwareDemandInterface()
    {
        $this->assertInstanceOf(
            DeadlineAwareDemandInterface::class,
            $this->subject
        );
    }
}

<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class NotificationTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model
 */
class NotificationTest extends UnitTestCase
{
    /**
     * @var Notification
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            Notification::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function getReservationInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getReservation()
        );
    }

    /**
     * @test
     */
    public function reservationCanBeSet()
    {
        $reservation = $this->getMock(Reservation::class);
        $this->subject->setReservation($reservation);

        $this->assertSame(
            $reservation,
            $this->subject->getReservation()
        );
    }
}

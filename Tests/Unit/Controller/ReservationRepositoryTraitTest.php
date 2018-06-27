<?php

namespace CPSIT\T3eventsReservation\Tests\Controller;

use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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
class ReservationRepositoryTraitTest extends UnitTestCase
{
    /**
     * @var ReservationRepositoryTrait
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ReservationRepositoryTrait::class
        );
    }

    /**
     * @test
     */
    public function reservationRepositoryCanBeInjected()
    {
        $reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->disableOriginalConstructor()->getMock();

        $this->subject->injectReservationRepository($reservationRepository);

        $this->assertAttributeSame(
            $reservationRepository,
            'reservationRepository',
            $this->subject
        );
    }
}

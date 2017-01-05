<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use CPSIT\T3eventsReservation\Controller\ReservationDemandFactoryTrait;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ReservationDemandFactory;
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
class ReservationDemandFactoryTraitTest extends UnitTestCase
{
    /**
     * @var ReservationDemandFactoryTrait
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ReservationDemandFactoryTrait::class
        );
    }

    /**
     * @test
     */
    public function reservationDemandFactoryCanBeInjected()
    {
        $reservationDemandFactory = $this->getMock(
            ReservationDemandFactory::class, [], [], '', false
        );

        $this->subject->injectReservationDemandFactory($reservationDemandFactory);

        $this->assertAttributeSame(
            $reservationDemandFactory,
            'reservationDemandFactory',
            $this->subject
        );
    }
}